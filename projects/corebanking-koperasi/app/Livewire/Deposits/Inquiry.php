<?php

namespace App\Livewire\Deposits;

use App\Models\DepositAccount;
use App\Models\DepositTransaction;
use App\Models\Branch;
use App\Traits\WithLogout;
use App\Traits\LogsActivity;
use App\Services\DepositOperationService;
use Livewire\Component;
use Livewire\WithPagination;

class Inquiry extends Component
{
    use WithPagination, WithLogout, LogsActivity;

    public $search = '';
    public $filter_branch = '';
    public $viewMode = 'grid'; // grid | detail
    public $selectedAccountId = null;
    public $mutation_date_from = '';
    public $mutation_date_to = '';
    
    // Member Details for Header
    public $cif_name, $cif_no, $nik, $phone, $address;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter_branch' => ['except' => ''],
        'viewMode' => ['except' => 'grid'],
        'selectedAccountId' => ['except' => null],
        'mutation_date_from' => ['except' => ''],
        'mutation_date_to' => ['except' => ''],
    ];

    public function mount($id = null)
    {
        if ($id) {
            $this->viewAccount($id);
        } elseif ($this->selectedAccountId) {
            $this->viewAccount($this->selectedAccountId);
        }
        $this->logActivity('NAVIGATE', 'Inquiry Simpanan Berjangka');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedMutationDateFrom()
    {
        $this->resetPage('historyPage');
    }

    public function updatedMutationDateTo()
    {
        $this->resetPage('historyPage');
    }

    public function viewAccount($id)
    {
        $account = DepositAccount::with(['cif'])->findOrFail($id);
        $this->selectedAccountId = $id;
        $this->viewMode = 'detail';

        if (!$this->mutation_date_from) {
            $this->mutation_date_from = $account->placement_date?->format('Y-m-d') ?? now()->startOfMonth()->format('Y-m-d');
        }

        if (!$this->mutation_date_to) {
            $this->mutation_date_to = now()->format('Y-m-d');
        }
        
        if ($account->cif) {
            $this->cif_name = $account->cif->name;
            $this->cif_no = $account->cif->cif_no;
            $this->nik = $account->cif->nik;
            $this->phone = $account->cif->phone;
            $this->address = $account->cif->address;
        }
    }

    public function closeView()
    {
        $this->viewMode = 'grid';
        $this->selectedAccountId = null;
        $this->mutation_date_from = '';
        $this->mutation_date_to = '';
    }

    public function render()
    {
        $selectedAccount = null;
        $history = collect();
        $schedules = collect();
        $paidMonths = 0;
        $totalScheduleMonths = 0;

        if ($this->selectedAccountId) {
            $selectedAccount = DepositAccount::with(['cif', 'product', 'branch', 'bilyet', 'savingAccount.product', 'savingAccount.cif', 'schedules'])->find($this->selectedAccountId);
            if ($selectedAccount) {
                $history = DepositTransaction::with('interestSchedule')
                    ->where('deposit_account_id', $this->selectedAccountId)
                    ->when($this->mutation_date_from, fn($q) => $q->whereDate('transaction_date', '>=', $this->mutation_date_from))
                    ->when($this->mutation_date_to, fn($q) => $q->whereDate('transaction_date', '<=', $this->mutation_date_to))
                    ->orderBy('transaction_date', 'desc')
                    ->paginate(10, ['*'], 'historyPage');
                
                $schedules = $selectedAccount->schedules()
                    ->orderBy('month_index', 'asc')
                    ->paginate(10, ['*'], 'schedulePage');
                
                // Count paid months from ALL schedules (not just current page)
                $paidMonths = $selectedAccount->schedules()->where('status', 'PAID')->count();
                $totalScheduleMonths = $selectedAccount->schedules()->count();

                // Fallback: If no records in schedules table, generate them on the fly
                if ($schedules->isEmpty() && $selectedAccount->schedules()->count() === 0) {
                    $projection = app(DepositOperationService::class)->calculateSimulation(
                        $selectedAccount->amount,
                        $selectedAccount->deposit_product_id,
                        $selectedAccount->tenor,
                        $selectedAccount->interest_rate,
                        $selectedAccount->interest_calculation_type,
                        $selectedAccount->placement_date,
                        20
                    );
                    
                    $schedules = collect($projection['schedule'])->map(function($row) {
                        return (object)[
                            'month_index' => $row['month'],
                            'schedule_date' => \Carbon\Carbon::parse($row['date']),
                            'gross_interest' => $row['gross_interest'],
                            'tax_amount' => $row['tax'],
                            'net_interest' => $row['net_interest'],
                            'status' => 'PENDING'
                        ];
                    });
                    
                    $paidCount = DepositTransaction::where('deposit_account_id', $this->selectedAccountId)
                        ->where('type', 'INTEREST_PAYMENT')
                        ->count();
                    $paidMonths = $paidCount;
                    $totalScheduleMonths = count($projection['schedule']);
                }
            }
        }

        $items = collect();
        $query = DepositAccount::with(['cif', 'product', 'branch', 'bilyet']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('account_no', 'like', '%' . $this->search . '%')
                    ->orWhereHas('cif', function ($qc) {
                        $qc->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('cif_no', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filter_branch) {
            $query->where('branch_id', $this->filter_branch);
        }

        if ($this->search || $this->filter_branch) {
            $items = $query->orderBy('id', 'desc')->paginate(10);
        } else {
            $items = DepositAccount::whereRaw('1 = 0')->paginate(10);
        }


        return view('livewire.deposits.inquiry', [
            'items' => $items,
            'selectedAccount' => $selectedAccount,
            'history' => $history,
            'schedules' => $schedules,
            'paidMonths' => $paidMonths,
            'totalScheduleMonths' => $totalScheduleMonths,
            'branches' => Branch::where('is_active', true)->get()
        ])->layout('layouts.app');
    }
}
