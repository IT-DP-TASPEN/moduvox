<?php

namespace App\Livewire\Savings;

use App\Models\SavingAccount;
use App\Models\Coa;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Illuminate\Support\Facades\Auth;
use App\Services\SettlementEngine;
use Livewire\Component;
use Livewire\WithPagination;

class Withdrawal extends Component
{
    use ApprovesActions, LogsActivity, WithLogout, WithPagination;

    public $search = '';
    public $viewMode = 'list'; // list, form
    public $totalResults = 0;

    public $selectedAccountId = null;
    public $amount = 0;
    public $reference_no = '';
    public $description = '';
    public $channel = 'CASH';
    public $bank_coa_id = null;
    public $cash_coa_id = null;
    public $coa_id = null;
    public $coaSearch = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'viewMode' => ['except' => 'list']
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedChannel($channel)
    {
        $this->bank_coa_id = null;
        $this->cash_coa_id = null;
        $this->coa_id = null;
        $this->coaSearch = '';

        if ($channel === 'COA') {
            return;
        }

        $options = SettlementEngine::getSelectableCoas($channel);
        if ($options->count() === 1) {
            if ($channel === 'ABA') {
                $this->bank_coa_id = $options->first()->id;
            } else {
                $this->cash_coa_id = $options->first()->id;
            }
        }
    }

    public function updatedCoaSearch($value)
    {
        $this->coa_id = null;
        $value = trim((string) $value);

        if ($value === '') {
            return;
        }

        $code = str_contains($value, ' - ') ? trim(strtok($value, ' - ')) : $value;
        $coa = Coa::active()
            ->leaf()
            ->where(function ($query) use ($value, $code) {
                $query->where('coa_code', $code)
                    ->orWhere('coa_code', $value)
                    ->orWhereRaw("CONCAT(coa_code, ' - ', name) = ?", [$value]);
            })
            ->first();

        if ($coa) {
            $this->coa_id = $coa->id;
            $this->coaSearch = "{$coa->coa_code} - {$coa->name}";
        }
    }

    public function selectAccount($id)
    {
        $this->selectedAccountId = $id;
        $this->viewMode = 'form';

        // Auto-generate unique reference: purely random alphanumeric
        $this->reference_no = strtoupper(bin2hex(random_bytes(6)));
        $this->description = 'Penarikan Simpanan';

        $this->logActivity('WITHDRAWAL_SELECT_ACCOUNT', "Memilih rekening ID [{$id}] untuk penarikan");
    }

    public function closeView()
    {
        $this->viewMode = 'list';
        $this->reset(['selectedAccountId', 'amount', 'reference_no', 'description', 'channel', 'bank_coa_id', 'cash_coa_id', 'coa_id', 'coaSearch']);
        $this->channel = 'CASH';
        $this->updatedChannel($this->channel);
    }

    public function submit()
    {
        $account = SavingAccount::findOrFail($this->selectedAccountId);

        $this->validate([
            'amount' => 'required|numeric|min:100',
            'reference_no' => 'required|min:3',
            'description' => 'required|min:5',
            'channel' => 'required|in:CASH,ABA,INTERNAL,COA',
            'bank_coa_id' => 'nullable|exists:coas,id',
            'cash_coa_id' => 'nullable|exists:coas,id',
            'coa_id' => 'nullable|exists:coas,id',
        ]);

        // Security Checks
        if ($account->status !== 'ACTIVE') {
            $this->addError('amount', "Rekening sedang dalam status {$account->status}. Penarikan tidak diizinkan.");
            return;
        }

        if ($this->amount > $account->effective_balance) {
            $this->addError('amount', "Saldo efektif tidak mencukupi. Saldo tersedia saat ini: Rp " . number_format($account->effective_balance, 2, ',', '.'));
            return;
        }

        $coaOverrideId = $this->selectedSettlementCoaId();
        if ($this->channel === 'COA' && !$coaOverrideId) {
            $this->addError('coa_id', 'Pilih COA untuk jalur transaksi ini.');
            return;
        }

        $data = [
            'saving_account_id' => $account->id,
            'account_no' => $account->account_no,
            'amount' => (float)$this->amount,
            'reference_no' => $this->reference_no,
            'description' => $this->description,
            'channel' => $this->channel,
            'coa_override_id' => $coaOverrideId,
            'type' => 'WITHDRAWAL',
            'branch_id' => Auth::user()->branch_id,
        ];

        $status = $this->interceptAction('savings.withdrawal', 'WITHDRAWAL', $data);

        $this->logActivity('WITHDRAWAL_REQUEST', "Mengajukan penarikan Rp " . number_format($this->amount, 2, ',', '.') . " dari rekening [{$account->account_no}]");

        if ($status === 'PENDING') {
            session()->flash('success', 'Permohonan penarikan telah diajukan ke antrean persetujuan.');
        } else {
            $service = app(\App\Services\SavingOperationService::class);
            $service->postTransaction($account, 'WITHDRAWAL', $this->amount, $this->description, $this->reference_no, $this->channel, $coaOverrideId);
            session()->flash('success', 'Penarikan berhasil diposting.');
        }

        return redirect()->route('savings.inquiry');
    }

    public function mount()
    {
        $accountNo = request()->query('account');
        if ($accountNo) {
            $account = SavingAccount::where('account_no', $accountNo)->first();
            if ($account) {
                $this->selectedAccountId = $account->id;
                $this->viewMode = 'form';
                // Auto-generate unique reference: purely random alphanumeric
                $this->reference_no = strtoupper(bin2hex(random_bytes(6)));
                $this->description = 'Penarikan Simpanan';
            }
        }
        $this->updatedChannel($this->channel);
        $this->logActivity('NAVIGATE', 'Penarikan Simpanan');
    }

    public function render()
    {
        $query = SavingAccount::with(['cif', 'product'])->where('status', 'ACTIVE');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('account_no', 'like', '%' . $this->search . '%')
                    ->orWhereHas('cif', function ($sq) {
                        $sq->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('nik', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if (!empty(trim($this->search))) {
            $items = $query->orderBy('id', 'desc')->distinct()->paginate(10);
            $this->totalResults = $items->total();
        } else {
            $items = $query->whereRaw('1 = 0')->paginate(1);
            $this->totalResults = 0;
        }

        $selectedAccount = null;
        if ($this->selectedAccountId) {
            $selectedAccount = SavingAccount::with(['cif', 'product'])->find($this->selectedAccountId);
        }

        $abaCoas = SettlementEngine::getSelectableCoas('ABA');
        $cashCoas = SettlementEngine::getSelectableCoas('CASH');
        $allCoas = $this->coaOptions();

        return view('livewire.savings.withdrawal', [
            'items' => $items,
            'selectedAccount' => $selectedAccount,
            'abaCoas' => $abaCoas,
            'cashCoas' => $cashCoas,
            'allCoas' => $allCoas,
            'user' => Auth::user(),
            'role' => Auth::user()->getRoleNames()->first()
        ])->layout('layouts.app');
    }

    private function selectedSettlementCoaId(): ?int
    {
        $channel = SettlementEngine::normalizeChannel($this->channel);

        if ($this->channel === 'COA') {
            $selectedId = $this->coa_id ? (int) $this->coa_id : null;
            return $selectedId && Coa::active()->leaf()->whereKey($selectedId)->exists() ? $selectedId : null;
        }

        $options = SettlementEngine::getSelectableCoas($channel);

        if ($options->isEmpty()) {
            return null;
        }

        if ($channel === SettlementEngine::CHANNEL_ABA) {
            $selectedId = $this->bank_coa_id ? (int) $this->bank_coa_id : null;
            return $selectedId && $options->contains('id', $selectedId) ? $selectedId : null;
        }

        $selectedId = $this->cash_coa_id ? (int) $this->cash_coa_id : null;
        return $selectedId && $options->contains('id', $selectedId) ? $selectedId : null;
    }

    private function coaOptions()
    {
        $search = trim($this->coaSearch);

        $query = Coa::active()->leaf()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('coa_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('coa_code')
            ->limit(50);

        $options = $query->get();

        if ($this->coa_id && !$options->contains('id', (int) $this->coa_id)) {
            $selected = Coa::active()->leaf()->whereKey($this->coa_id)->first();
            if ($selected) {
                $options->prepend($selected);
            }
        }

        return $options;
    }
}
