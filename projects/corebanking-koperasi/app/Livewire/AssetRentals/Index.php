<?php

namespace App\Livewire\AssetRentals;

use App\Models\Asset;
use App\Models\AssetRental;
use App\Models\AssetRentalBilling;
use App\Models\Branch;
use App\Models\Coa;
use App\Models\Rekanan;
use App\Services\AssetOperationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\LogsActivity;
use App\Traits\ApprovesActions;

class Index extends Component
{
    use WithPagination, LogsActivity, ApprovesActions;

    public $search = '';
    public $filterStatus = '';
    public $viewMode = 'grid';
    public $selectedRental = null;

    // Contract form
    public $showContractForm = false;
    public $asset_id, $rekanan_id, $branch_id;
    public $rental_start_date, $rental_end_date;
    public $monthly_rate = 0;
    public $payment_due_day = 1;
    public $notes;

    // Payment modal
    public $showPaymentModal = false;
    public $payingBillingId = null;
    public $payment_reference;
    public $payment_debit_coa_id = '';
    public $payment_debit_coa_search = '';
    public $payment_credit_coa_id = '';
    public $payment_credit_coa_search = '';

    protected function rules(): array
    {
        return [
            'asset_id'          => 'required|exists:assets,id',
            'rekanan_id'        => 'required|exists:rekanan,id',
            'branch_id'         => 'required|exists:branches,id',
            'rental_start_date' => 'required|date',
            'rental_end_date'   => 'required|date|after:rental_start_date',
            'monthly_rate'      => 'required|numeric|min:1',
            'payment_due_day'   => 'required|integer|min:1|max:28',
            'notes'             => 'nullable|string',
        ];
    }

    public function mount(): void
    {
        $this->branch_id = Auth::user()->branch_id;
        $this->rental_start_date = now()->format('Y-m-d');
        $this->rental_end_date = now()->addYear()->format('Y-m-d');

        $this->logActivity('NAVIGATE', 'Jasa Sewa');
    }

    public function openContractForm(): void
    {
        $this->reset(['asset_id', 'rekanan_id', 'notes']);
        $this->monthly_rate = 0;
        $this->payment_due_day = 1;
        $this->rental_start_date = now()->format('Y-m-d');
        $this->rental_end_date = now()->addYear()->format('Y-m-d');
        $this->showContractForm = true;
    }

    public function saveContract(): void
    {
        $this->validate();
        $monthlyRate = (float) str_replace('.', '', $this->monthly_rate);

        $data = [
            'asset_id' => $this->asset_id,
            'rekanan_id' => $this->rekanan_id,
            'branch_id' => $this->branch_id,
            'rental_start_date' => $this->rental_start_date,
            'rental_end_date' => $this->rental_end_date,
            'monthly_rate' => $monthlyRate,
            'payment_due_day' => $this->payment_due_day,
            'notes' => $this->notes,
        ];
        $status = $this->interceptAction('asset-rentals.index', 'CREATE', $data);
        if ($status === 'PENDING') {
            $this->logActivity('CREATE_REQUEST', "Mengajukan kontrak sewa aset ID: {$this->asset_id}");
            $this->showContractForm = false;
            session()->flash('success', 'Pengajuan kontrak sewa berhasil dikirim ke antrean persetujuan.');
            return;
        }

        DB::transaction(function () use ($monthlyRate) {
            $prefix = 'KSW-' . now()->format('Ym') . '-';
            $count = AssetRental::where('contract_no', 'like', $prefix . '%')->count() + 1;
            $contractNo = $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);

            $rental = AssetRental::create([
                'contract_no'       => $contractNo,
                'asset_id'          => $this->asset_id,
                'rekanan_id'        => $this->rekanan_id,
                'branch_id'         => $this->branch_id,
                'rental_start_date' => $this->rental_start_date,
                'rental_end_date'   => $this->rental_end_date,
                'monthly_rate'      => $monthlyRate,
                'payment_due_day'   => $this->payment_due_day,
                'status'            => 'ACTIVE',
                'notes'             => $this->notes,
                'created_by'        => Auth::id(),
            ]);

            // Mark asset as RENTED
            Asset::find($this->asset_id)->update(['status' => 'RENTED', 'updated_by' => Auth::id()]);

            // Auto-generate billings for all months
            $this->generateBillings($rental);
        });

        session()->flash('success', 'Kontrak sewa berhasil dibuat. Tagihan bulanan telah digenerate.');
        $this->showContractForm = false;
        
        $this->logActivity('CREATE', "Membuat kontrak sewa baru untuk aset ID: {$this->asset_id}");
    }

    private function generateBillings(AssetRental $rental): void
    {
        $start = Carbon::parse($rental->rental_start_date)->startOfMonth()->addMonth();
        $end   = Carbon::parse($rental->rental_end_date)->startOfMonth();

        $current = $start->copy();
        while ($current->lte($end)) {
            $period = $current->format('Y-m');
            $dueDate = $current->copy()->day($rental->payment_due_day);

            AssetRentalBilling::create([
                'asset_rental_id'    => $rental->id,
                'billing_period'     => $period,
                'billing_date'       => now()->toDateString(),
                'due_date'           => $dueDate->toDateString(),
                'amount'             => $rental->monthly_rate,
                'status'             => 'UNPAID',
                'created_by'         => Auth::id(),
            ]);

            $current->addMonth();
        }
    }

    public function viewRental(int $id): void
    {
        $this->selectedRental = AssetRental::with(['asset.category', 'rekanan', 'branch', 'billings'])->findOrFail($id);
        $this->viewMode = 'detail';
    }

    public function closeView(): void
    {
        $this->selectedRental = null;
        $this->viewMode = 'grid';
    }

    public function openPaymentModal(int $billingId): void
    {
        $this->payingBillingId = $billingId;
        $this->payment_reference = '';
        $this->setPaymentCoaDefaults();
        $this->showPaymentModal = true;
    }

    public function updatedPaymentDebitCoaSearch($value): void
    {
        $this->resolvePaymentCoa('debit', $value);
    }

    public function updatedPaymentCreditCoaSearch($value): void
    {
        $this->resolvePaymentCoa('credit', $value);
    }

    private function resolvePaymentCoa(string $side, mixed $value): void
    {
        $idProperty = "payment_{$side}_coa_id";
        $searchProperty = "payment_{$side}_coa_search";
        $this->{$idProperty} = '';

        $value = trim((string) $value);
        if ($value === '') {
            return;
        }

        $code = str_contains($value, ' - ') ? trim(explode(' - ', $value, 2)[0]) : $value;
        $type = $side === 'debit' ? 'LIABILITY' : 'REVENUE';
        $coa = Coa::active()
            ->leaf()
            ->where('type', $type)
            ->where(function ($query) use ($value, $code) {
                $query->where('coa_code', $code)
                    ->orWhere('coa_code', $value)
                    ->orWhereRaw("CONCAT(coa_code, ' - ', name) = ?", [$value]);
            })
            ->first();

        if ($coa) {
            $this->{$idProperty} = $coa->id;
            $this->{$searchProperty} = "{$coa->coa_code} - {$coa->name}";
        }
    }

    private function setPaymentCoaDefaults(): void
    {
        $this->payment_debit_coa_id = '';
        $this->payment_debit_coa_search = '';
        $this->payment_credit_coa_id = '';
        $this->payment_credit_coa_search = '';

        $defaults = [
            'debit' => ['code' => '219011', 'type' => 'LIABILITY'],
            'credit' => ['code' => '417000', 'type' => 'REVENUE'],
        ];

        foreach ($defaults as $side => $rule) {
            $coa = Coa::active()->leaf()->where('type', $rule['type'])->where('coa_code', $rule['code'])->first();
            if ($coa) {
                $this->{"payment_{$side}_coa_id"} = $coa->id;
                $this->{"payment_{$side}_coa_search"} = "{$coa->coa_code} - {$coa->name}";
            }
        }
    }

    public function confirmPayment(AssetOperationService $assetService): void
    {
        $this->resolvePaymentCoa('debit', $this->payment_debit_coa_search);
        $this->resolvePaymentCoa('credit', $this->payment_credit_coa_search);

        $this->validate([
            'payment_reference' => 'nullable|string|max:100',
            'payment_debit_coa_id' => 'required|exists:coas,id',
            'payment_credit_coa_id' => 'required|exists:coas,id',
        ]);

        $data = [
            'billing_id' => $this->payingBillingId,
            'payment_reference' => $this->payment_reference,
            'payment_debit_coa_id' => $this->payment_debit_coa_id,
            'payment_credit_coa_id' => $this->payment_credit_coa_id,
        ];

        $status = $this->interceptAction('asset-rentals.index', 'UPDATE', $data, $this->payingBillingId);
        if ($status === 'PENDING') {
            $this->logActivity('UPDATE_REQUEST', "Mengajukan pembayaran tagihan sewa ID: {$this->payingBillingId}");
            $this->showPaymentModal = false;
            session()->flash('success', 'Pengajuan pembayaran tagihan dikirim ke antrean persetujuan.');
            return;
        }

        DB::transaction(function () use ($assetService) {
            $billing = AssetRentalBilling::with('rental.asset', 'rental.rekanan')->findOrFail($this->payingBillingId);
            $journal = $assetService->recognizeRentalRevenue(
                $billing,
                (int) $this->payment_debit_coa_id,
                (int) $this->payment_credit_coa_id
            );

            $billing->update([
                'status'             => 'PAID',
                'paid_at'            => now(),
                'payment_reference'  => $this->payment_reference ?: $journal->reference_no,
            ]);
        });

        // Refresh selected rental
        if ($this->selectedRental) {
            $this->selectedRental = AssetRental::with(['asset.category', 'rekanan', 'branch', 'billings'])
                ->find($this->selectedRental->id);
        }

        $this->showPaymentModal = false;
        $this->logActivity('UPDATE', "Mencatat pembayaran untuk tagihan ID: {$this->payingBillingId}");
        session()->flash('success', 'Tagihan berhasil ditandai sebagai LUNAS.');
    }

    public function terminateContract(int $rentalId): void
    {
        $status = $this->interceptAction('asset-rentals.index', 'DELETE', ['rental_id' => $rentalId], $rentalId);
        if ($status === 'PENDING') {
            $this->logActivity('DELETE_REQUEST', "Mengajukan terminasi kontrak sewa ID: {$rentalId}");
            $this->closeView();
            session()->flash('success', 'Pengajuan terminasi kontrak dikirim ke antrean persetujuan.');
            return;
        }

        DB::transaction(function () use ($rentalId) {
            $rental = AssetRental::findOrFail($rentalId);
            $rental->update(['status' => 'TERMINATED', 'updated_by' => Auth::id()]);
            Asset::find($rental->asset_id)->update(['status' => 'ACTIVE', 'updated_by' => Auth::id()]);
            // Cancel pending billings
            $rental->billings()->where('status', 'UNPAID')->update(['status' => 'OVERDUE']);
        });

        $this->closeView();
        $this->logActivity('UPDATE', "Menerminasi kontrak sewa ID: {$rentalId}");
        session()->flash('success', 'Kontrak sewa telah diterminasi.');
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function render()
    {
        $rentals = AssetRental::with(['asset', 'rekanan', 'branch'])
            ->when($this->search, fn($q) => $q
                ->where('contract_no', 'like', "%{$this->search}%")
                ->orWhereHas('rekanan', fn($r) => $r->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('asset', fn($a) => $a->where('name', 'like', "%{$this->search}%"))
            )
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.asset-rentals.index', [
            'rentals'          => $rentals,
            'availableAssets'  => Asset::where('status', 'ACTIVE')->orderBy('name')->get(),
            'allRekanan'       => Rekanan::where('is_active', true)->orderBy('name')->get(),
            'branches'         => Branch::where('is_active', true)->orderBy('name')->get(),
            'paymentDebitCoas' => Coa::active()->leaf()->where('type', 'LIABILITY')->orderBy('coa_code')->get(),
            'paymentCreditCoas' => Coa::active()->leaf()->where('type', 'REVENUE')->orderBy('coa_code')->get(),
        ])->layout('layouts.app');
    }
}
