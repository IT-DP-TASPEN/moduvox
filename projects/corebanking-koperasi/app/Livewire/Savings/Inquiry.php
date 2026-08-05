<?php

namespace App\Livewire\Savings;

use App\Models\SavingAccount;
use App\Models\SavingTransaction;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
use App\Traits\WithLogout;
use App\Traits\LogsActivity;
use Livewire\Component;
use Livewire\WithPagination;

class Inquiry extends Component
{
    use WithPagination, WithLogout, LogsActivity;

    public $search = '';
    public $filter_branch = '';
    
    public $user, $role;
    public $totalResults = 0;

    // View State Management
    public $viewMode = 'grid'; 
    public $selectedAccountId = null;
    public $isReadOnly = true;

    // CIF Form Fields (Mirrored from CIF/Inquiry)
    public $cities = [], $districts = [], $subdistricts = [];
    public $cif_no, $nik, $npwp, $name;
    public $birth_place, $birth_date, $gender, $blood_type, $religion, $marital_status = 'SINGLE', $mother_maiden_name;
    public $address, $rt, $rw, $province_id, $city_id, $district_id, $subdistrict_id, $postal_code, $domicile_address;
    public $phone, $email;
    public $occupation, $occupation_nip, $company_name, $income_range;
    public $spouse_name, $spouse_nik, $emergency_name, $emergency_phone;
    public $branch_id, $marketing_id, $status = 'ACTIVE';

    // Filter Dates for History
    public $dateFrom = '';
    public $dateTo = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filter_branch' => ['except' => ''],
        'viewMode' => ['except' => 'grid'],
        'selectedAccountId' => ['except' => null],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount($id = null)
    {
        $this->user = auth()->user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        
        // Handle ID from route or query string
        $targetId = $id ?? request()->query('selectedAccountId');
        
        if ($targetId) {
            $this->selectedAccountId = $targetId;
            $this->viewAccount($targetId);
        }
        
        $this->logActivity('NAVIGATE', 'Inquiry Simpanan');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterBranch()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage('historyPage');
    }

    public function updatedDateTo()
    {
        $this->resetPage('historyPage');
    }

    public function viewAccount($id)
    {
        $account = SavingAccount::with([
            'cif.province', 'cif.city', 'cif.district', 'cif.subdistrict',
            'loanAccounts.product',
            'depositAccounts.product',
        ])->findOrFail($id);
        
        $this->selectedAccountId = $id;
        $this->viewMode = 'form';

        if (!$this->dateFrom) {
            $firstTransactionDate = SavingTransaction::where('saving_account_id', $id)->min('transaction_date');
            $this->dateFrom = $firstTransactionDate
                ? \Carbon\Carbon::parse($firstTransactionDate)->format('Y-m-d')
                : ($account->opened_at?->format('Y-m-d') ?? now()->startOfMonth()->format('Y-m-d'));
        }

        if (!$this->dateTo) {
            $this->dateTo = now()->format('Y-m-d');
        }

        if ($account->cif) {
            $this->fill($account->cif->toArray());
            $this->birth_date = is_object($account->cif->birth_date) ? $account->cif->birth_date->format('Y-m-d') : $account->cif->birth_date;

            // Fetch associative data for detail view
            if ($this->province_id) $this->cities = City::where('province_id', $this->province_id)->get();
            if ($this->city_id) $this->districts = District::where('regency_id', $this->city_id)->get();
            if ($this->district_id) $this->subdistricts = Subdistrict::where('district_id', $this->district_id)->get();
        }
    }

    public function closeView()
    {
        $this->viewMode = 'grid';
        $this->selectedAccountId = null;
        $this->dateFrom = '';
        $this->dateTo = '';
    }

    public function render()
    {
        $items = collect();
        $query = SavingAccount::with(['cif', 'product', 'branch']);

        // Only load data if search exists or branch filter is applied
        if (!empty(trim($this->search)) || !empty($this->filter_branch)) {
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('account_no', 'like', '%' . $this->search . '%')
                      ->orWhereHas('cif', function($qc) {
                          $qc->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('cif_no', 'like', '%' . $this->search . '%');
                      });
                });
            }

            if ($this->filter_branch) {
                $query->where('branch_id', $this->filter_branch);
            }

            $items = $query->orderBy('id', 'desc')->distinct()->paginate(10);
            $this->totalResults = $items->total();
        } else {
            $items = SavingAccount::whereRaw('1 = 0')->paginate(1);
            $this->totalResults = 0;
        }

        $history = collect();
        $selectedAccountModel = null;
        if ($this->selectedAccountId) {
            $selectedAccountModel = SavingAccount::with(['cif', 'product', 'branch', 'loanAccounts.product', 'depositAccounts.product'])->find($this->selectedAccountId);
            
            if ($selectedAccountModel) {
                // Ensure viewMode is 'form' if we have a selected account
                $this->viewMode = 'form';
                
                $history = SavingTransaction::with('originalTransaction')
                    ->where('saving_account_id', $this->selectedAccountId)
                    ->whereBetween('transaction_date', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
                    ->orderBy('transaction_date', 'desc')
                    ->paginate(10, ['*'], 'historyPage');
            }
        }

        return view('livewire.savings.inquiry', [
            'items' => $items,
            'history' => $history,
            'selectedAccount' => $selectedAccountModel,
            'branches' => \App\Models\Branch::where('is_active', true)->get(),
            'marketings' => \App\Models\MarketingMaster::where('is_active', true)->get(),
            'provinces' => \App\Models\Province::all()
        ])->layout('layouts.app');
    }
}
