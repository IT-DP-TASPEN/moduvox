<?php

namespace App\Livewire\Deposits;

use App\Models\Branch;
use App\Models\DepositAccount;
use App\Models\DepositSchedule;
use App\Services\DepositOperationService;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Livewire\Component;
use Livewire\WithPagination;

class InterestPayment extends Component
{
    use WithPagination, ApprovesActions, LogsActivity, WithLogout;

    public $search = '';
    public $filter_branch = '';
    public $viewMode = 'grid';
    public $selectedAccountId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter_branch' => ['except' => ''],
        'viewMode' => ['except' => 'grid'],
        'selectedAccountId' => ['except' => null],
    ];

    public function mount(): void
    {
        $this->logActivity('NAVIGATE', 'Pembayaran Bunga Simpanan Berjangka');
    }

    public function selectAccount(int $id): void
    {
        DepositAccount::whereIn('status', ['ACTIVE', 'MATURED'])->findOrFail($id);

        $this->selectedAccountId = $id;
        $this->viewMode = 'detail';
        $this->resetPage();
    }

    public function closeView(): void
    {
        $this->viewMode = 'grid';
        $this->selectedAccountId = null;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterBranch(): void
    {
        $this->resetPage();
    }

    public function pay(int $scheduleId, DepositOperationService $service)
    {
        $schedule = DepositSchedule::with(['account.cif', 'account.savingAccount'])
            ->where('status', 'PENDING')
            ->when($this->selectedAccountId, fn($q) => $q->where('deposit_account_id', $this->selectedAccountId))
            ->findOrFail($scheduleId);

        $data = [
            'deposit_schedule_id' => $schedule->id,
            'deposit_account_id' => $schedule->deposit_account_id,
            'account_no' => $schedule->account->account_no,
            'net_interest' => (float) $schedule->net_interest,
        ];

        $status = $this->interceptAction('deposits.interest-payment', 'PAY', $data, $schedule->id);
        $this->logActivity('PAY_DEPOSIT_INTEREST_REQUEST', "Pembayaran bunga simpanan berjangka [{$schedule->account->account_no}] bulan ke-{$schedule->month_index}");

        if ($status === 'PENDING') {
            session()->flash('success', 'Permohonan pembayaran bunga telah diajukan ke antrean persetujuan.');
            return;
        }

        try {
            $service->disbursePeriodInterest($schedule);
            session()->flash('success', 'Bunga simpanan berjangka berhasil dibayarkan.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $selectedAccount = $this->selectedAccountId
            ? DepositAccount::with(['cif', 'product', 'branch', 'savingAccount'])->find($this->selectedAccountId)
            : null;

        if ($this->viewMode === 'detail' && $selectedAccount) {
            $depositService = app(DepositOperationService::class);
            $depositService->ensureSchedules($selectedAccount);
            $depositService->ensureRolloverSchedules($selectedAccount);

            $items = DepositSchedule::with(['account.cif', 'account.product', 'account.branch', 'account.savingAccount'])
                ->where('deposit_account_id', $selectedAccount->id)
                ->orderBy('month_index')
                ->paginate(10);
        } elseif ($this->search || $this->filter_branch) {
            $items = DepositAccount::with(['cif', 'product', 'branch', 'savingAccount'])
                ->whereIn('status', ['ACTIVE', 'MATURED'])
                ->when($this->filter_branch, fn($q) => $q->where('branch_id', $this->filter_branch))
                ->when($this->search, function ($q) {
                    $q->where(function ($q) {
                        $q->where('account_no', 'like', '%' . $this->search . '%')
                            ->orWhereHas('cif', function ($qc) {
                                $qc->where('name', 'like', '%' . $this->search . '%')
                                    ->orWhere('cif_no', 'like', '%' . $this->search . '%');
                            });
                    });
                })
                ->orderByDesc('id')
                ->paginate(10);
        } else {
            $items = DepositAccount::whereRaw('1 = 0')->paginate(10);
        }

        return view('livewire.deposits.interest-payment', [
            'items' => $items,
            'selectedAccount' => $selectedAccount,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
