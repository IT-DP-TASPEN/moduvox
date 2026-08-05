<?php

namespace App\Livewire\Loans;

use App\Models\LoanAccount;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Illuminate\Support\Facades\Auth;

class Inquiry extends Component
{
    use WithPagination, LogsActivity, WithLogout;

    public $user;
    public $role;

    public function mount()
    {
        $this->user = Auth::user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->logActivity('NAVIGATE', 'Inquiry Pinjaman');
    }

    public $search = '';
    public $statusFilter = '';
    public int $perPage = 12;
    public int $schedulePage = 1;
    public int $transactionPage = 1;
    public int $documentPage = 1;

    // Detail View
    public $viewMode = 'grid'; // grid or detail
    public $selectedAccount = null;
    public $detailStats = [
        'paid_installments' => 0,
        'total_installments' => 0,
        'previous_month_shortfall' => 0,
    ];

    public function viewAccount($id)
    {
        $this->selectedAccount = $this->loadDetailAccount($id);

        $endPrevMonth = now()->startOfMonth()->subDay()->toDateString();
        $previousShortfall = $this->selectedAccount->schedules
            ->where('due_date', '<=', $endPrevMonth)
            ->whereIn('status', ['UNPAID', 'PARTIAL'])
            ->sum(function ($sched) {
                return ($sched->principal_amount - $sched->principal_paid)
                    + ($sched->interest_amount - $sched->interest_paid)
                    + ($sched->penalty_amount - $sched->penalty_paid);
            });

        $this->detailStats = [
            'paid_installments' => $this->selectedAccount->schedules->where('status', 'PAID')->count(),
            'total_installments' => $this->selectedAccount->schedules->count(),
            'previous_month_shortfall' => (float) $previousShortfall,
        ];

        $this->viewMode = 'detail';
        $this->schedulePage = 1;
        $this->transactionPage = 1;
        $this->documentPage = 1;
    }

    public function closeView()
    {
        $this->viewMode = 'grid';
        $this->selectedAccount = null;
        $this->detailStats = [
            'paid_installments' => 0,
            'total_installments' => 0,
            'previous_month_shortfall' => 0,
        ];
        $this->schedulePage = 1;
        $this->transactionPage = 1;
        $this->documentPage = 1;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        if ($this->selectedAccount) {
            $this->selectedAccount = $this->loadDetailAccount($this->selectedAccount->id);
        }

        $query = LoanAccount::with(['cif', 'product', 'branch', 'schedules']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('account_no', 'like', '%' . $this->search . '%')
                    ->orWhere('pk_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('cif', function ($qCif) {
                        $qCif->where('cif_no', 'like', '%' . $this->search . '%')
                            ->orWhere('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search || $this->statusFilter) {
            $loans = $query->orderBy('created_at', 'desc')->paginate($this->perPage);
        } else {
            $loans = LoanAccount::whereRaw('1 = 0')->paginate($this->perPage);
        }

        $schedulePaginator = $this->selectedAccount
            ? $this->paginateCollection($this->selectedAccount->schedules, $this->schedulePage, 'schedulePage')
            : null;
        $transactionPaginator = $this->selectedAccount
            ? $this->paginateCollection($this->selectedAccount->transactions, $this->transactionPage, 'transactionPage')
            : null;
        $documentPaginator = $this->selectedAccount
            ? $this->paginateCollection($this->selectedAccount->documents, $this->documentPage, 'documentPage')
            : null;

        return view('livewire.loans.inquiry', [
            'loans' => $loans,
            'schedulePaginator' => $schedulePaginator,
            'transactionPaginator' => $transactionPaginator,
            'documentPaginator' => $documentPaginator,
        ]);
    }

    public function gotoSchedulePage(int $page): void
    {
        $this->schedulePage = max(1, $page);
    }

    public function gotoTransactionPage(int $page): void
    {
        $this->transactionPage = max(1, $page);
    }

    public function gotoDocumentPage(int $page): void
    {
        $this->documentPage = max(1, $page);
    }

    private function paginateCollection(Collection $items, int $page, string $pageName): LengthAwarePaginator
    {
        $total = $items->count();
        $lastPage = max(1, (int) ceil($total / $this->perPage));
        $page = min(max(1, $page), $lastPage);

        return new LengthAwarePaginator(
            $items->forPage($page, $this->perPage)->values(),
            $total,
            $this->perPage,
            $page,
            ['pageName' => $pageName]
        );
    }

    private function loadDetailAccount(int $id): LoanAccount
    {
        return LoanAccount::with([
            'cif',
            'product',
            'savingAccount.product',
            'savingAccount.cif',
            'schedules' => function ($q) {
                $q->orderBy('installment_number');
            },
            'transactions' => function ($q) {
                $q->latest();
            },
            'documents.uploader',
        ])->findOrFail($id);
    }
}
