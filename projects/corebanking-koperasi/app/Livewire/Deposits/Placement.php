<?php

namespace App\Livewire\Deposits;

use App\Models\Cif;
use App\Models\Coa;
use App\Models\DepositProduct;
use App\Models\DepositBilyet;
use App\Models\ApprovalRequest;
use App\Models\MarketingMaster;
use App\Models\SavingAccount;
use App\Services\DepositOperationService;
use App\Services\SettlementEngine;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Carbon\Carbon;

class Placement extends Component
{
    use ApprovesActions, LogsActivity;


    // Step 1: Member
    public $searchCif = '';
    public $cif_id;
    public $selectedCif = null;

    // Step 2: Investment
    public $deposit_product_id;
    public $amount = 10000000;
    public $tenor = 1;
    public $interest_rate;
    public $placement_date;
    public $interest_calculation_type = 'MONTHLY'; // MONTHLY or DAILY

    // Step 3: Admin & Payout
    public $deposit_bilyet_id;
    public $rollover_type = 'NONE';
    public $deposit_channel = 'CASH';
    public $bank_coa_id   = null;  // Sub-akun ABA spesifik (misal Giro BRI)
    public $cash_coa_id   = null;  // Sub-akun Kas spesifik (misal Kas Teller)
    public $source_saving_account_id;
    public $saving_account_id;
    public $marketing_id;
    public $reason;

    protected $listeners = ['selectCif'];

    public function mount()
    {
        $this->placement_date = now()->format('Y-m-d');
        $this->logActivity('NAVIGATE', 'Penempatan Baru');
    }

    public function updatedDepositProductId($id)
    {
        if ($id) {
            $product = DepositProduct::find($id);
            if ($product) {
                $this->interest_rate = $product->max_interest_rate;
                $this->tenor = $product->min_term;
                $this->interest_calculation_type = $product->interest_calculation_type === 'DAILY' ? 'DAILY' : 'MONTHLY';
                if ($this->amount < $product->min_amount) {
                    $this->amount = (float) $product->min_amount;
                }
                if ($product->max_amount && $this->amount > $product->max_amount) {
                    $this->amount = (float) $product->max_amount;
                }
            }
        }
    }

    public function updatedDepositChannel($channel)
    {
        // Reset pilihan sub-akun saat channel berganti
        $this->bank_coa_id = null;
        $this->cash_coa_id = null;

        if ($channel === 'INTERNAL') {
            if (!$this->source_saving_account_id && $this->cif_id) {
                $this->source_saving_account_id = SavingAccount::where('cif_id', $this->cif_id)
                    ->where('status', 'ACTIVE')
                    ->orderBy('id')
                    ->value('id');
            }
            return;
        }

        // Auto-select jika hanya ada 1 pilihan
        $options = SettlementEngine::getSelectableCoas($channel);
        if ($options->count() === 1) {
            if ($channel === 'ABA') {
                $this->bank_coa_id = $options->first()->id;
            } else {
                $this->cash_coa_id = $options->first()->id;
            }
        }
    }

    public function selectCif($id)
    {
        $this->selectedCif = Cif::find($id);
        $this->cif_id = $id;
        $this->searchCif = $this->selectedCif->name . ' (' . $this->selectedCif->cif_no . ')';
        $firstSavingAccountId = SavingAccount::where('cif_id', $id)
            ->where('status', 'ACTIVE')
            ->orderBy('id')
            ->value('id');
        $this->source_saving_account_id = $firstSavingAccountId;
        $this->saving_account_id = $firstSavingAccountId;
    }



    public function getProjectionProperty()
    {
        if (!$this->amount || !$this->tenor || !$this->interest_rate || !$this->deposit_product_id) return null;

        return app(DepositOperationService::class)->calculateSimulation(
            $this->amount,
            $this->deposit_product_id,
            $this->tenor,
            $this->interest_rate,
            $this->interest_calculation_type,
            $this->placement_date
        );
    }

    public function save(DepositOperationService $service)
    {
        $this->validate([
            'cif_id'                    => 'required|exists:cifs,id',
            'deposit_product_id'        => 'required|exists:deposit_products,id',
            'amount'                    => 'required|numeric|min:0',
            'tenor'                     => 'required|integer|min:1',
            'interest_rate'             => 'required|numeric|min:0',
            'interest_calculation_type' => 'required|in:MONTHLY,DAILY',
            'placement_date'            => 'required|date',
            'deposit_channel'           => 'required|in:CASH,ABA,INTERNAL',
            'bank_coa_id'               => 'nullable|exists:coas,id',
            'cash_coa_id'               => 'nullable|exists:coas,id',
            'source_saving_account_id'  => 'nullable|exists:saving_accounts,id',
            'deposit_bilyet_id'         => 'required|exists:deposit_bilyets,id',
            'rollover_type'             => 'required|in:NONE,PRINCIPAL,PRINCIPAL_INTEREST',
            'saving_account_id'         => 'required|exists:saving_accounts,id',
            'reason'                    => 'nullable|string|max:500',
        ]);

        if ($this->deposit_channel === 'INTERNAL') {
            $this->validate([
                'source_saving_account_id' => 'required|exists:saving_accounts,id',
            ]);
        }

        $product = DepositProduct::find($this->deposit_product_id);
        if (!$product || !$product->is_active) {
            $this->addError('deposit_product_id', 'Produk simpanan berjangka tidak aktif atau tidak tersedia.');
            return;
        }

        if (!$this->validateProductRules($product)) {
            return;
        }

        $bilyet = DepositBilyet::find($this->deposit_bilyet_id);
        if (!$bilyet || $bilyet->status !== 'AVAILABLE') {
            $this->addError('deposit_bilyet_id', 'Bilyet tidak tersedia atau sudah digunakan.');
            return;
        }

        $pendingBilyetRequest = $this->pendingPlacementBilyetRequest((int) $this->deposit_bilyet_id);
        if ($pendingBilyetRequest) {
            $this->addError('deposit_bilyet_id', "Bilyet ini sedang menunggu approval pada permohonan #{$pendingBilyetRequest->id}.");
            return;
        }

        $data = [
            'cif_id'                    => $this->cif_id,
            'deposit_product_id'        => $this->deposit_product_id,
            'deposit_bilyet_id'         => $this->deposit_bilyet_id,
            'amount'                    => (float)$this->amount,
            'interest_rate'             => (float)$this->interest_rate,
            'tenor'                     => (int)$this->tenor,
            'interest_calculation_type' => $this->interest_calculation_type,
            'deposit_channel'           => $this->deposit_channel,
            // COA override: sub-akun spesifik yang dipilih user
            'coa_override_id'           => ($this->deposit_channel === 'ABA')
                                            ? ($this->bank_coa_id ?: null)
                                            : ($this->deposit_channel === 'CASH' ? ($this->cash_coa_id ?: null) : null),
            'placement_date'            => $this->placement_date,
            'rollover_type'             => $this->rollover_type,
            'saving_account_id'         => $this->saving_account_id,
            'source_saving_account_id'  => $this->source_saving_account_id,
            'reason'                    => $this->reason,
            'marketing_id'              => $this->marketing_id ?: null,
            'branch_id'                 => Auth::user()->branch_id,
        ];

        // Approval check
        $status = $this->interceptAction('deposits.placement', 'CREATE', $data);

        $this->logActivity('CREATE_DEPOSIT_REQUEST', "Mengajukan penempatan simpanan berjangka [{$this->amount}] untuk Anggota [{$this->selectedCif->name}]");

        if ($status === 'PENDING') {
            session()->flash('success', 'Permohonan penempatan simpanan berjangka telah diajukan ke antrean persetujuan.');
        } else {
            // Auto Approval or execution if no config
            $service->openAccount($data);
            session()->flash('success', 'Rekening simpanan berjangka berhasil dibuka.');
        }

        return redirect()->route('deposits.inquiry');
    }

    public function render()
    {
        $cifResults = [];
        if (strlen($this->searchCif) >= 3 && !$this->selectedCif) {
            $cifResults = Cif::where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchCif . '%')
                    ->orWhere('cif_no', 'like', '%' . $this->searchCif . '%')
                    ->orWhere('nik', 'like', '%' . $this->searchCif . '%');
            })->limit(5)->get();
        }

        $pendingBilyetIds = $this->pendingPlacementBilyetIds();
        $availableBilyets = DepositBilyet::where('status', 'AVAILABLE')
            ->where('branch_id', Auth::user()->branch_id)
            ->when($pendingBilyetIds !== [], fn ($query) => $query->whereNotIn('id', $pendingBilyetIds))
            ->get();

        $savingAccounts = $this->cif_id
            ? SavingAccount::where('cif_id', $this->cif_id)->where('status', 'ACTIVE')->get()
            : [];

        // COA selectable untuk dropdown bank/kas
        $abaCoas  = SettlementEngine::getSelectableCoas('ABA');
        $cashCoas = SettlementEngine::getSelectableCoas('CASH');

        return view('livewire.deposits.placement', [
            'cifResults'       => $cifResults,
            'products'         => DepositProduct::where('is_active', true)->get(),
            'selectedProduct'  => $this->deposit_product_id ? DepositProduct::find($this->deposit_product_id) : null,
            'availableBilyets' => $availableBilyets,
            'savingAccounts'   => $savingAccounts,
            'marketings'       => MarketingMaster::orderBy('name')->get(),
            'abaCoas'          => $abaCoas,
            'cashCoas'         => $cashCoas,
        ])->layout('layouts.app');
    }

    private function pendingPlacementBilyetIds(): array
    {
        return ApprovalRequest::where('module_key', 'deposits.placement')
            ->where('action', 'CREATE')
            ->where('status', 'PENDING')
            ->get(['data_after'])
            ->map(fn (ApprovalRequest $request) => (int) ($request->data_after['deposit_bilyet_id'] ?? 0))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function pendingPlacementBilyetRequest(int $bilyetId): ?ApprovalRequest
    {
        return ApprovalRequest::where('module_key', 'deposits.placement')
            ->where('action', 'CREATE')
            ->where('status', 'PENDING')
            ->orderBy('id')
            ->get()
            ->first(fn (ApprovalRequest $request) => (int) ($request->data_after['deposit_bilyet_id'] ?? 0) === $bilyetId);
    }

    private function validateProductRules(DepositProduct $product): bool
    {
        $ok = true;
        $unit = str_starts_with(strtoupper((string) $product->term_unit), 'DAY') ? 'hari' : 'bulan';

        if ((float) $this->amount < (float) $product->min_amount) {
            $this->addError('amount', "Minimal penempatan produk ini Rp " . number_format((float) $product->min_amount, 0, ',', '.'));
            $ok = false;
        }
        if ($product->max_amount && (float) $this->amount > (float) $product->max_amount) {
            $this->addError('amount', "Maksimal penempatan produk ini Rp " . number_format((float) $product->max_amount, 0, ',', '.'));
            $ok = false;
        }
        if ((int) $this->tenor < (int) $product->min_term) {
            $this->addError('tenor', "Tenor minimal produk ini {$product->min_term} {$unit}.");
            $ok = false;
        }
        if ($product->max_term && (int) $this->tenor > (int) $product->max_term) {
            $this->addError('tenor', "Tenor maksimal produk ini {$product->max_term} {$unit}.");
            $ok = false;
        }
        if ((float) $this->interest_rate < (float) $product->min_interest_rate || (float) $this->interest_rate > (float) $product->max_interest_rate) {
            $this->addError('interest_rate', "Suku bunga harus di antara {$product->min_interest_rate}% - {$product->max_interest_rate}%.");
            $ok = false;
        }

        return $ok;
    }
}
