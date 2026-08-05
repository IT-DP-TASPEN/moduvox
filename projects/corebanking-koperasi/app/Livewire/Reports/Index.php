<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Branch;
use App\Models\Company;
use App\Models\SavingAccount;
use App\Models\SavingProduct;
use App\Models\LoanAccount;
use App\Models\LoanTransaction;
use App\Models\LoanProduct;
use App\Models\DepositAccount;
use App\Models\DepositTransaction;
use App\Models\DepositProduct;
use App\Models\Asset;
use App\Models\AssetRental;
use App\Models\Cif;
use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\TaxSetting;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use App\Traits\WithLogout;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithLogout, LogsActivity;

    public $report_type = '';
    public $branch_id = '';
    public $product_id = '';
    public $status = '';
    public $date_start = '';
    public $date_end = '';

    public array $templateReports = [
        'loan_nominative' => 'LAPORAN NOMINATIF KREDIT',
        'loan_deferred_interest' => 'LAP BUNGA DITERIMA DIMUKA KREDIT',
        'loan_repayment' => 'LAPORAN PEMBAYARAN ANGSURAN',
        'loan_disbursement' => 'LAPORAN REALISASI',
        'loan_settlement' => 'LAPORAN PELUNASAN',
        'deposit_nominative' => 'LAPORAN NOMINATIF DEPOSITO',
        'deposit_interest_payment' => 'LAP PEMBAYARAN BUNGA DEPOSITO',
        'deposit_withdrawal' => 'LAP DEPOSITO CAIR',
        'deposit_placement' => 'LAP DEPOSITO PENEMPATAN',
        'saving_nominative' => 'LAPORAN NOMINATIF TABUNGAN',
        'saving_opening' => 'LAPORAN TABUNGAN BUKA',
        'saving_closing' => 'LAPORAN TABUNGAN TUTUP',
    ];
    
    // Sidebar info
    public $user;
    public $role;
    public $company;
    public $branch;

    // Branches for dropdown
    public $branches = [];

    public function mount()
    {
        $this->user = Auth::user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->company = Company::find($this->user->company_id);
        $this->branch = Branch::find($this->user->branch_id);

        $this->branches = Branch::where('is_active', true)->get();
        
        $this->date_start = '';
        $this->date_end = '';

        $this->logActivity('NAVIGATE', 'Pusat Laporan');
    }

    public function updatedReportType()
    {
        $this->status = '';
        $this->product_id = '';

        if (in_array($this->report_type, ['balance_sheet', 'income_statement', 'cash_flow', 'equity_change'], true)) {
            $this->date_end = now()->format('Y-m-d');
            $this->date_start = $this->report_type === 'balance_sheet'
                ? ''
                : now()->startOfYear()->format('Y-m-d');
        } else {
            $this->date_start = '';
            $this->date_end = '';
        }

        $this->resetPage();
    }

    public function updatedBranchId()
    {
        $this->resetPage();
    }

    public function updatedProductId()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedDateStart()
    {
        $this->resetPage();
    }

    public function updatedDateEnd()
    {
        $this->resetPage();
    }

    public function getStatusesProperty()
    {
        switch ($this->report_type) {
            case 'savings':
            case 'saving_nominative':
            case 'saving_opening':
            case 'saving_closing':
                return ['ACTIVE' => 'Aktif', 'BLOCKED' => 'Diblokir', 'DORMANT' => 'Dormant', 'CLOSED' => 'Tutup'];
            case 'loans':
            case 'loan_nominative':
            case 'loan_deferred_interest':
                return ['PENDING' => 'Menunggu', 'ACTIVE' => 'Aktif', 'PAID' => 'Lunas', 'NPL' => 'Macet', 'REJECTED' => 'Ditolak'];
            case 'deposits':
            case 'deposit_nominative':
                return ['ACTIVE' => 'Aktif', 'MATURED' => 'Jatuh Tempo', 'CLOSED' => 'Tutup/Dicairkan'];
            case 'cifs':
                return ['ACTIVE' => 'Aktif', 'INACTIVE' => 'Nonaktif', 'BLOCKED' => 'Diblokir'];
            case 'assets':
                return ['ACTIVE' => 'Aktif', 'RENTED' => 'Disewakan', 'DISPOSED' => 'Dilepas', 'LOST' => 'Hilang', 'SOLD' => 'Terjual'];
            case 'asset_rentals':
                return ['ACTIVE' => 'Aktif', 'EXPIRED' => 'Berakhir', 'TERMINATED' => 'Dihentikan'];
            case 'audit':
                return ActivityLog::select('action')->distinct()->pluck('action', 'action')->toArray();
            case 'balance_sheet':
            case 'income_statement':
            case 'cash_flow':
            case 'equity_change':
                return [];
            default:
                return [];
        }
    }

    public function getProductsProperty(): Collection
    {
        return match ($this->report_type) {
            'savings', 'saving_nominative', 'saving_opening', 'saving_closing' => SavingProduct::where('is_active', true)->orderBy('product_code')->get(['id', 'product_code', 'name']),
            'loans', 'loan_nominative', 'loan_deferred_interest', 'loan_repayment', 'loan_disbursement', 'loan_settlement' => LoanProduct::where('is_active', true)->orderBy('product_code')->get(['id', 'product_code', 'name']),
            'deposits', 'deposit_nominative', 'deposit_interest_payment', 'deposit_withdrawal', 'deposit_placement' => DepositProduct::where('is_active', true)->orderBy('product_code')->get(['id', 'product_code', 'name']),
            default => collect(),
        };
    }

    public function getReportData()
    {
        if (empty($this->report_type)) {
            return collect([]);
        }

        switch ($this->report_type) {
            case 'savings':
            case 'saving_nominative':
            case 'saving_opening':
            case 'saving_closing':
                $dateColumn = $this->report_type === 'saving_closing' ? 'closed_at' : 'opened_at';
                return SavingAccount::with(['cif', 'product', 'branch'])
                    ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
                    ->when($this->product_id, fn($q) => $q->where('saving_product_id', $this->product_id))
                    ->when($this->report_type === 'saving_closing', fn($q) => $q->where('status', 'CLOSED'))
                    ->when($this->status && $this->report_type !== 'saving_closing', fn($q) => $q->where('status', $this->status))
                    ->when($this->date_start, fn($q) => $q->whereDate($dateColumn, '>=', $this->date_start))
                    ->when($this->date_end, fn($q) => $q->whereDate($dateColumn, '<=', $this->date_end))
                    ->latest();
            case 'loans':
            case 'loan_nominative':
            case 'loan_deferred_interest':
                return LoanAccount::with(['cif', 'product.deferredInterestCoa', 'branch', 'savingAccount', 'marketing', 'insuranceProduct.provider', 'schedules'])
                    ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
                    ->when($this->product_id, fn($q) => $q->where('loan_product_id', $this->product_id))
                    ->when($this->report_type === 'loan_deferred_interest', fn($q) => $q->where('is_diskonto', true)->where('diskonto_upfront_amount', '>', 0))
                    ->when($this->status, fn($q) => $q->where('status', $this->status))
                    ->when($this->date_start, fn($q) => $q->whereDate('created_at', '>=', $this->date_start))
                    ->when($this->date_end, fn($q) => $q->whereDate('created_at', '<=', $this->date_end))
                    ->latest();
            case 'loan_repayment':
            case 'loan_disbursement':
            case 'loan_settlement':
                $types = match ($this->report_type) {
                    'loan_disbursement' => ['DISBURSEMENT'],
                    'loan_settlement' => ['REPAYMENT_SETTLEMENT'],
                    default => ['REPAYMENT_MANUAL', 'REPAYMENT_AUTO', 'REPAYMENT_SETTLEMENT'],
                };

                return LoanTransaction::with(['loanAccount.cif', 'loanAccount.product', 'loanAccount.branch', 'loanAccount.marketing', 'loanAccount.insuranceProduct.provider', 'loanAccount.schedules'])
                    ->whereIn('transaction_type', $types)
                    ->when($this->branch_id, fn($q) => $q->whereHas('loanAccount', fn($loan) => $loan->where('branch_id', $this->branch_id)))
                    ->when($this->product_id, fn($q) => $q->whereHas('loanAccount', fn($loan) => $loan->where('loan_product_id', $this->product_id)))
                    ->when($this->date_start, fn($q) => $q->whereDate('created_at', '>=', $this->date_start))
                    ->when($this->date_end, fn($q) => $q->whereDate('created_at', '<=', $this->date_end))
                    ->latest();
            case 'deposits':
            case 'deposit_nominative':
                return DepositAccount::with(['cif', 'product', 'branch', 'savingAccount', 'bilyet', 'schedules' => fn($q) => $q->orderBy('month_index')])
                    ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
                    ->when($this->product_id, fn($q) => $q->where('deposit_product_id', $this->product_id))
                    ->when($this->status, fn($q) => $q->where('status', $this->status))
                    ->when($this->date_start, fn($q) => $q->whereDate('placement_date', '>=', $this->date_start))
                    ->when($this->date_end, fn($q) => $q->whereDate('placement_date', '<=', $this->date_end))
                    ->latest();
            case 'deposit_interest_payment':
            case 'deposit_withdrawal':
            case 'deposit_placement':
                $type = match ($this->report_type) {
                    'deposit_interest_payment' => 'INTEREST_PAYMENT',
                    'deposit_withdrawal' => 'WITHDRAWAL',
                    default => 'PLACEMENT',
                };

                return DepositTransaction::with(['account.cif', 'account.product', 'account.branch', 'account.savingAccount', 'account.bilyet', 'interestSchedule'])
                    ->where('type', $type)
                    ->when($this->branch_id, fn($q) => $q->whereHas('account', fn($deposit) => $deposit->where('branch_id', $this->branch_id)))
                    ->when($this->product_id, fn($q) => $q->whereHas('account', fn($deposit) => $deposit->where('deposit_product_id', $this->product_id)))
                    ->when($this->date_start, fn($q) => $q->whereDate('transaction_date', '>=', $this->date_start))
                    ->when($this->date_end, fn($q) => $q->whereDate('transaction_date', '<=', $this->date_end))
                    ->latest('transaction_date');
            case 'cifs':
                return Cif::with(['branch'])
                    ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
                    ->when($this->status, fn($q) => $q->where('status', $this->status))
                    ->when($this->date_start, fn($q) => $q->whereDate('created_at', '>=', $this->date_start))
                    ->when($this->date_end, fn($q) => $q->whereDate('created_at', '<=', $this->date_end))
                    ->latest();
            case 'assets':
                return Asset::with(['category', 'branch', 'depreciations'])
                    ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
                    ->when($this->status, fn($q) => $q->where('status', $this->status))
                    ->when($this->date_start, fn($q) => $q->whereDate('purchase_date', '>=', $this->date_start))
                    ->when($this->date_end, fn($q) => $q->whereDate('purchase_date', '<=', $this->date_end))
                    ->latest();
            case 'asset_rentals':
                return AssetRental::with(['asset', 'rekanan', 'branch', 'latestPaidBilling'])
                    ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
                    ->when($this->status, fn($q) => $q->where('status', $this->status))
                    ->when($this->date_start, fn($q) => $q->whereDate('rental_start_date', '>=', $this->date_start))
                    ->when($this->date_end, fn($q) => $q->whereDate('rental_start_date', '<=', $this->date_end))
                    ->latest();
            case 'audit':
                return ActivityLog::with(['user'])
                    ->when($this->status, fn($q) => $q->where('action', $this->status))
                    ->when($this->date_start, fn($q) => $q->whereDate('created_at', '>=', $this->date_start))
                    ->when($this->date_end, fn($q) => $q->whereDate('created_at', '<=', $this->date_end))
                    ->latest();
            case 'balance_sheet':
                return collect($this->uppercaseReportRows($this->buildBalanceSheetData()));
            case 'income_statement':
                return collect($this->uppercaseReportRows($this->buildIncomeStatementData()));
            case 'cash_flow':
                return collect($this->uppercaseReportRows($this->buildCashFlowData()));
            case 'equity_change':
                return collect($this->uppercaseReportRows($this->buildEquityChangeData()));
            default:
                return collect([]);
        }
    }

    private function uppercaseReportRows(array $rows): array
    {
        return array_map(function (array $row): array {
            if (isset($row['section'])) {
                $row['section'] = strtoupper((string) $row['section']);
            }

            if (isset($row['account'])) {
                $row['account'] = strtoupper((string) $row['account']);
            }

            return $row;
        }, $rows);
    }

    public function templateReportColumns(): array
    {
        return match ($this->report_type) {
            'loan_nominative' => ['No', 'CIF', 'Norek Kredit', 'Produk', 'Skim', 'Nama', 'Alamat', 'Plafond', 'Baki Debet', 'Tgl Realisasi', 'Tenor', 'Tgl Jt Tempo', 'Sys Bunga', 'Bunga (Thn)', 'Angsuran', 'Tunggakan pokok', 'Tunggakan Bunga', 'Kolektibilitas', 'Perusahaan Asuransi', 'Provisi (Rp)', 'Admin (Rp)', 'Asuransi (Rp)', 'Angsuran Dimuka (Rp)', 'Bunga Diterima Dimuka', 'No HP', 'Marketing', 'Status', 'Tgl Status'],
            'loan_deferred_interest' => ['No', 'CIF', 'Norek Kredit', 'Produk', 'Nama', 'Plafond', 'Tenor', 'Bunga (Thn)', 'Bunga Diterima Dimuka', 'COA Bunga Dimuka', 'Tgl Realisasi', 'Status'],
            'loan_repayment' => ['No', 'CIF', 'Norek Kredit', 'Produk', 'Skim', 'Nama', 'Alamat', 'Plafond', 'Baki Debet Awal', 'Tgl Realisasi', 'Tenor', 'Tgl Jt Tempo', 'Sys Bunga', 'Bunga (Thn)', 'Pokok', 'Bunga', 'Denda', 'Pinalti', 'Total', 'Baki Debet Akhir', 'Kol Sebelum', 'Kol Sesudah'],
            'loan_disbursement' => ['No', 'CIF', 'Norek Kredit', 'Produk', 'Skim', 'Nama', 'Alamat', 'Plafond', 'Baki Debet', 'Tgl Realisasi', 'Tenor', 'Tgl Jt Tempo', 'Sys Bunga', 'Bunga', 'Provisi', 'Administrasi', 'Premi Asuransi', 'Bunga Diterima Dimuka', 'Perusahaan Asuransi', 'Marketing', 'No HP', 'Status', 'Tanggal Status'],
            'loan_settlement' => ['No', 'CIF', 'Norek Kredit', 'Produk', 'Skim', 'Nama', 'Alamat', 'Plafond', 'Baki Debet', 'Tgl Realisasi', 'Tenor', 'Tgl Jt Tempo', 'Sys Bunga', 'Bunga', 'Tunggakan Pokok', 'Tunggakan Bunga', 'Baki Debet', 'Marketing', 'Status', 'No HP'],
            'deposit_nominative' => ['No', 'CIF', 'Norek Deposito', 'No Bilyet', 'Nama', 'Alamat', 'Tgl Penempatan', 'Tgl Mulai', 'JKW', 'Tgl Jt Tempo', 'Bunga', 'Perpanjangan', 'Nominal', 'Rek Pembayaran Bunga', 'Bank', 'Nama Penerima Pembayaran Bunga', 'Status', 'Tgl Status'],
            'deposit_withdrawal', 'deposit_placement' => ['No', 'CIF', 'Norek Deposito', 'No Bilyet', 'Nama', 'Alamat', 'Tgl Penempatan', 'Tgl Mulai', 'JKW', 'Tgl Jt Tempo', '% Bunga', 'Perpanjangan', 'Nominal', 'Rek Pembayaran Bunga', 'Bank', 'Nama Penerima Pembayaran Bunga', 'Status', 'Tgl Status'],
            'deposit_interest_payment' => ['No', 'CIF', 'Norek Deposito', 'No Bilyet', 'Nama', 'Alamat', 'Tgl Penempatan', 'Tgl Mulai', 'JKW', 'Tgl Jt Tempo', '% Bunga', 'Perpanjangan', 'Nominal', 'Brutto', 'Pajak', 'Netto', 'Rek Pembayaran Bunga', 'Bank', 'Nama Penerima Pembayaran Bunga', 'Status', 'Tgl Status'],
            'saving_nominative', 'saving_opening', 'saving_closing' => ['No', 'CIF', 'Norek Tabungan', 'Jenis Tabungan', 'Nama', 'Alamat', 'Tgl Pembukaan', 'Saldo', 'Status', 'Tgl Status'],
            default => [],
        };
    }

    public function templateReportRow($row, int $number): array
    {
        if (in_array($this->report_type, ['loan_nominative'], true)) {
            return $this->loanTemplateRow($row, $number);
        }

        if (in_array($this->report_type, ['loan_deferred_interest'], true)) {
            return $this->loanDeferredInterestTemplateRow($row, $number);
        }

        if (in_array($this->report_type, ['loan_repayment', 'loan_disbursement', 'loan_settlement'], true)) {
            return $this->loanTransactionTemplateRow($row, $number);
        }

        if (in_array($this->report_type, ['deposit_nominative'], true)) {
            return $this->depositTemplateRow($row, $number);
        }

        if (in_array($this->report_type, ['deposit_interest_payment', 'deposit_withdrawal', 'deposit_placement'], true)) {
            return $this->depositTemplateRow($row->account, $number, $row);
        }

        if (in_array($this->report_type, ['saving_nominative', 'saving_opening', 'saving_closing'], true)) {
            return $this->savingTemplateRow($row, $number);
        }

        return [];
    }

    private function loanTemplateRow(LoanAccount $loan, int $number): array
    {
        $firstSchedule = $loan->schedules->first();
        $lastSchedule = $loan->schedules->last();
        $installment = $firstSchedule ? (float) $firstSchedule->principal_amount + (float) $firstSchedule->interest_amount + (float) $firstSchedule->penalty_amount : 0;
        $dueSchedules = $loan->schedules->filter(fn($schedule) => in_array($schedule->status, ['UNPAID', 'PARTIAL'], true) && $schedule->due_date <= now());
        $insurance = $loan->insuranceProduct?->provider?->name ?? $loan->insuranceProduct?->name ?? '-';

        return [
            $number,
            $loan->cif?->cif_no ?? '-',
            $loan->account_no ?? '-',
            $loan->product?->name ?? '-',
            $loan->is_diskonto ? 'Diskonto' : 'Reguler',
            $loan->cif?->name ?? '-',
            $loan->cif?->alamat_lengkap ?? $loan->cif?->address ?? '-',
            (float) $loan->principal_amount,
            (float) $loan->outstanding_total,
            $this->dateValue($loan->disbursement_date),
            $loan->tenor,
            $this->dateValue($lastSchedule?->due_date),
            $loan->calculation_method,
            (float) $loan->interest_rate,
            $installment,
            (float) $dueSchedules->sum(fn($s) => max(0, (float) $s->principal_amount - (float) $s->principal_paid)),
            (float) $dueSchedules->sum(fn($s) => max(0, (float) $s->interest_amount - (float) $s->interest_paid)),
            $loan->kol_level,
            $insurance,
            (float) $loan->provision_fee,
            (float) $loan->admin_fee,
            (float) $loan->insurance_fee,
            (float) $loan->prepaid_installment_amount,
            (float) $loan->diskonto_upfront_amount,
            $loan->cif?->phone ?? '-',
            $loan->marketing?->name ?? '-',
            $loan->status,
            $this->dateValue($loan->updated_at),
        ];
    }

    private function loanDeferredInterestTemplateRow(LoanAccount $loan, int $number): array
    {
        $coa = $loan->product?->deferredInterestCoa;

        return [
            $number,
            $loan->cif?->cif_no ?? '-',
            $loan->account_no ?? '-',
            $loan->product?->name ?? '-',
            $loan->cif?->name ?? '-',
            (float) $loan->principal_amount,
            $loan->tenor,
            (float) $loan->interest_rate,
            (float) $loan->diskonto_upfront_amount,
            $coa ? "{$coa->coa_code} - {$coa->name}" : '-',
            $this->dateValue($loan->disbursement_date),
            $loan->status,
        ];
    }

    private function loanTransactionTemplateRow(LoanTransaction $trx, int $number): array
    {
        $loan = $trx->loanAccount;
        $lastSchedule = $loan?->schedules?->last();

        if ($this->report_type === 'loan_repayment') {
            return [
                $number, $loan?->cif?->cif_no ?? '-', $loan?->account_no ?? '-', $loan?->product?->name ?? '-',
                $loan?->is_diskonto ? 'Diskonto' : 'Reguler', $loan?->cif?->name ?? '-', $loan?->cif?->alamat_lengkap ?? $loan?->cif?->address ?? '-',
                (float) ($loan?->principal_amount ?? 0), (float) ($loan?->outstanding_total ?? 0) + (float) $trx->amount_principal,
                $this->dateValue($loan?->disbursement_date), $loan?->tenor ?? '-', $this->dateValue($lastSchedule?->due_date),
                $loan?->calculation_method ?? '-', (float) ($loan?->interest_rate ?? 0), (float) $trx->amount_principal,
                (float) $trx->amount_interest, (float) $trx->amount_penalty, 0, (float) $trx->total_amount,
                (float) ($loan?->outstanding_total ?? 0), $loan?->kol_level ?? '-', $loan?->kol_level ?? '-',
            ];
        }

        if ($this->report_type === 'loan_disbursement') {
            return [
                $number, $loan?->cif?->cif_no ?? '-', $loan?->account_no ?? '-', $loan?->product?->name ?? '-',
                $loan?->is_diskonto ? 'Diskonto' : 'Reguler', $loan?->cif?->name ?? '-', $loan?->cif?->alamat_lengkap ?? $loan?->cif?->address ?? '-',
                (float) ($loan?->principal_amount ?? 0), (float) ($loan?->outstanding_total ?? 0), $this->dateValue($loan?->disbursement_date),
                $loan?->tenor ?? '-', $this->dateValue($lastSchedule?->due_date), $loan?->calculation_method ?? '-', (float) ($loan?->interest_rate ?? 0),
                (float) ($loan?->provision_fee ?? 0), (float) ($loan?->admin_fee ?? 0), (float) ($loan?->insurance_fee ?? 0), (float) ($loan?->diskonto_upfront_amount ?? 0),
                $loan?->insuranceProduct?->provider?->name ?? $loan?->insuranceProduct?->name ?? '-', $loan?->marketing?->name ?? '-', $loan?->cif?->phone ?? '-',
                $loan?->status ?? '-', $this->dateValue($trx->created_at),
            ];
        }

        return [
            $number, $loan?->cif?->cif_no ?? '-', $loan?->account_no ?? '-', $loan?->product?->name ?? '-',
            $loan?->is_diskonto ? 'Diskonto' : 'Reguler', $loan?->cif?->name ?? '-', $loan?->cif?->alamat_lengkap ?? $loan?->cif?->address ?? '-',
            (float) ($loan?->principal_amount ?? 0), (float) ($loan?->outstanding_total ?? 0), $this->dateValue($loan?->disbursement_date),
            $loan?->tenor ?? '-', $this->dateValue($lastSchedule?->due_date), $loan?->calculation_method ?? '-', (float) ($loan?->interest_rate ?? 0),
            (float) $trx->amount_principal, (float) $trx->amount_interest, (float) ($loan?->outstanding_total ?? 0),
            $loan?->marketing?->name ?? '-', $loan?->status ?? '-', $loan?->cif?->phone ?? '-',
        ];
    }

    private function depositTemplateRow(?DepositAccount $deposit, int $number, ?DepositTransaction $trx = null): array
    {
        $schedule = $trx?->interestSchedule ?? $deposit?->schedules?->first();
        $base = [
            $number,
            $deposit?->cif?->cif_no ?? '-',
            $deposit?->account_no ?? '-',
            $deposit?->bilyet?->bilyet_number ?? $deposit?->bilyet?->kode_bilyet ?? '-',
            $deposit?->cif?->name ?? '-',
            $deposit?->cif?->alamat_lengkap ?? $deposit?->cif?->address ?? '-',
            $this->dateValue($deposit?->placement_date),
            $this->dateValue($deposit?->placement_date),
            $deposit?->tenor ?? '-',
            $this->dateValue($deposit?->maturity_date),
            (float) ($deposit?->interest_rate ?? 0),
            $deposit?->rollover_type ?? '-',
            (float) ($deposit?->amount ?? 0),
            $deposit?->savingAccount?->account_no ?? '-',
            $deposit?->fund_channel ?? $trx?->channel ?? '-',
            $deposit?->cif?->name ?? '-',
        ];

        if ($this->report_type === 'deposit_interest_payment') {
            return array_merge(array_slice($base, 0, 13), [
                (float) ($schedule?->gross_interest ?? $trx?->amount ?? 0),
                (float) ($schedule?->tax_amount ?? 0),
                (float) ($schedule?->net_interest ?? $trx?->amount ?? 0),
            ], array_slice($base, 13), [
                $deposit?->status ?? '-',
                $this->dateValue($trx?->transaction_date ?? $deposit?->updated_at),
            ]);
        }

        return array_merge($base, [
            $deposit?->status ?? '-',
            $this->dateValue($trx?->transaction_date ?? $deposit?->updated_at),
        ]);
    }

    private function savingTemplateRow(SavingAccount $account, int $number): array
    {
        $statusDate = $this->report_type === 'saving_closing' ? $account->closed_at : $account->updated_at;

        return [
            $number,
            $account->cif?->cif_no ?? '-',
            $account->account_no ?? '-',
            $account->product?->name ?? '-',
            $account->cif?->name ?? '-',
            $account->cif?->alamat_lengkap ?? $account->cif?->address ?? '-',
            $this->dateValue($account->opened_at),
            (float) $account->balance,
            $account->status,
            $this->dateValue($statusDate),
        ];
    }

    private function dateValue($date): string
    {
        return $date ? \Carbon\Carbon::parse($date)->format('Y-m-d') : '-';
    }

    public function assetInventoryRow(Asset $asset, int $number): array
    {
        $usefulLifeMonths = (int) ($asset->useful_life_months ?: (($asset->useful_life_years ?: 0) * 12));
        $endDate = $asset->purchase_date && $usefulLifeMonths > 0
            ? $asset->purchase_date->copy()->addMonthsNoOverflow($usefulLifeMonths)
            : null;
        $purchasePrice = (float) $asset->purchase_price;
        $currentBookValue = (float) ($asset->current_book_value ?? $purchasePrice);
        $accumulatedDepreciation = (float) $asset->accumulated_depreciation;
        $latestDepreciation = $asset->depreciations->first();
        $depreciationAmount = $latestDepreciation ? (float) $latestDepreciation->depreciation_amount : 0.0;
        $previousBookValue = $latestDepreciation
            ? round((float) $latestDepreciation->book_value_after + $depreciationAmount, 2)
            : $currentBookValue;

        return [
            $number,
            $asset->serial_number ?: $asset->asset_code,
            $asset->name,
            $asset->purchase_date ? $asset->purchase_date->format('d/m/y') : '-',
            $usefulLifeMonths,
            $endDate ? $endDate->format('d/m/y') : '-',
            (float) $asset->purchase_price,
            $previousBookValue,
            $depreciationAmount,
            $accumulatedDepreciation,
            $currentBookValue,
        ];
    }

    private function baseJournalEntryQuery(bool $asOf = false, bool $excludeClosing = false)
    {
        return JournalEntry::query()
            ->with(['coa', 'journal'])
            ->whereHas('journal', function ($q) use ($asOf, $excludeClosing) {
                $q->when($this->branch_id, fn($q2) => $q2->where('branch_id', $this->branch_id))
                  ->where('status', 'APPROVED');

                if ($excludeClosing) {
                    $q->where('reference_no', 'not like', 'CLS%');
                }

                if ($asOf || $this->report_type === 'income_statement') {
                    $q->when($this->date_end, fn($q2) => $q2->whereDate('transaction_date', '<=', $this->date_end));
                    return;
                }

                $q->when($this->date_start, fn($q2) => $q2->whereDate('transaction_date', '>=', $this->date_start))
                  ->when($this->date_end, fn($q2) => $q2->whereDate('transaction_date', '<=', $this->date_end));
            });
    }

    private function sumByType(string $type, bool $asOf = false): float
    {
        $entries = $this->baseJournalEntryQuery($asOf, in_array($type, ['REVENUE', 'EXPENSE'], true))
            ->whereHas('coa', fn($q) => $q->where('type', $type)->where('is_leaf', true))
            ->get();

        return (float) $entries->sum(function ($entry) use ($type) {
            if ($type === 'ASSET' || $type === 'EXPENSE') {
                return (float) $entry->debit - (float) $entry->credit;
            }
            return (float) $entry->credit - (float) $entry->debit;
        });
    }

    private function sumByPrefix(string $prefix, string $type, bool $asOf = false): float
    {
        $entries = $this->baseJournalEntryQuery($asOf, in_array($type, ['REVENUE', 'EXPENSE'], true))
            ->whereHas('coa', function ($q) use ($prefix, $type) {
                $q->where('type', $type)
                  ->where('is_leaf', true)
                  ->where('coa_code', 'like', $prefix . '%');
            })
            ->get();

        return (float) $entries->sum(function ($entry) use ($type) {
            if ($type === 'ASSET' || $type === 'EXPENSE') {
                return (float) $entry->debit - (float) $entry->credit;
            }
            return (float) $entry->credit - (float) $entry->debit;
        });
    }

    private function sumByPrefixes(array $prefixes, string $type, bool $asOf = false, array $excludePrefixes = []): float
    {
        $entries = $this->baseJournalEntryQuery($asOf, in_array($type, ['REVENUE', 'EXPENSE'], true))
            ->whereHas('coa', function ($q) use ($prefixes, $type, $excludePrefixes) {
                $q->where('type', $type)
                  ->where('is_leaf', true)
                  ->where(function ($query) use ($prefixes) {
                      foreach ($prefixes as $prefix) {
                          $query->orWhere('coa_code', 'like', $prefix . '%');
                      }
                  });

                foreach ($excludePrefixes as $prefix) {
                    $q->where('coa_code', 'not like', $prefix . '%');
                }
            })
            ->get();

        return (float) $entries->sum(function ($entry) use ($type) {
            if ($type === 'ASSET' || $type === 'EXPENSE') {
                return (float) $entry->debit - (float) $entry->credit;
            }

            return (float) $entry->credit - (float) $entry->debit;
        });
    }

    private function sumByCodes(array $codes, bool $asOf = false): float
    {
        $entries = $this->baseJournalEntryQuery($asOf)
            ->whereHas('coa', fn($q) => $q->whereIn('coa_code', $codes)->where('is_leaf', true))
            ->get();

        return (float) $entries->sum(function ($entry) {
            $type = $entry->coa->type;
            if ($type === 'ASSET' || $type === 'EXPENSE') {
                return (float) $entry->debit - (float) $entry->credit;
            }

            return (float) $entry->credit - (float) $entry->debit;
        });
    }

    private function incomeStatementRowsByPrefixes(string $section, array $prefixes, string $type, array $excludePrefixes = []): array
    {
        $coas = Coa::query()
            ->where('type', $type)
            ->where('is_leaf', true)
            ->where(function ($query) use ($prefixes) {
                foreach ($prefixes as $prefix) {
                    $query->orWhere('coa_code', 'like', $prefix . '%');
                }
            })
            ->when($excludePrefixes !== [], function ($query) use ($excludePrefixes) {
                foreach ($excludePrefixes as $prefix) {
                    $query->where('coa_code', 'not like', $prefix . '%');
                }
            })
            ->orderBy('coa_code')
            ->get()
            ->keyBy('coa_code');

        $entries = $this->baseJournalEntryQuery(false, true)
            ->whereHas('coa', function ($q) use ($prefixes, $type, $excludePrefixes) {
                $q->where('type', $type)
                  ->where('is_leaf', true)
                  ->where(function ($query) use ($prefixes) {
                      foreach ($prefixes as $prefix) {
                          $query->orWhere('coa_code', 'like', $prefix . '%');
                      }
                  });

                foreach ($excludePrefixes as $prefix) {
                    $q->where('coa_code', 'not like', $prefix . '%');
                }
            })
            ->get();

        $amountByCode = $entries
            ->groupBy(fn($entry) => $entry->coa->coa_code)
            ->map(function ($rows) {
                return (float) $rows->sum(function ($entry) {
                    $type = $entry->coa->type;
                    if ($type === 'ASSET' || $type === 'EXPENSE') {
                        return (float) $entry->debit - (float) $entry->credit;
                    }

                    return (float) $entry->credit - (float) $entry->debit;
                });
            });

        return $coas
            ->map(function (Coa $coa) use ($section, $amountByCode) {
                $amount = round((float) ($amountByCode[$coa->coa_code] ?? 0), 2);
                if (abs($amount) < 0.01) {
                    return null;
                }

                return [
                    'section' => strtoupper($section),
                    'account' => strtoupper("{$coa->coa_code} - {$coa->name}"),
                    'amount' => $amount,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function incomeStatementTotals(): array
    {
        $pendBunga = $this->sumByPrefixes(['411'], 'REVENUE');
        $pendAdminKredit = $this->sumByPrefixes(['412', '413', '414', '415', '416', '417'], 'REVENUE');
        $pendUsahaSewa = 0.0;
        $pendBungaTabungan = $this->sumByPrefixes(['42'], 'REVENUE');
        $totalPendapatan = $this->sumByPrefixes(['41', '42'], 'REVENUE');

        $bebanOperasional = $this->sumByPrefixes(['51'], 'EXPENSE', false, ['515']);
        $bebanPenyusutan = $this->sumByPrefixes(['515'], 'EXPENSE');
        $bebanAdmTabungan = $this->sumByPrefixes(['52'], 'EXPENSE', false, ['523']);
        $bebanPajak = $this->sumByPrefixes(['523'], 'EXPENSE');
        $totalBeban = $bebanOperasional + $bebanPenyusutan + $bebanAdmTabungan;
        $labaSebelumPajak = $totalPendapatan - $totalBeban;
        $labaBersih = $labaSebelumPajak - $bebanPajak;

        return compact(
            'pendBunga',
            'pendAdminKredit',
            'pendUsahaSewa',
            'pendBungaTabungan',
            'totalPendapatan',
            'bebanOperasional',
            'bebanPenyusutan',
            'bebanAdmTabungan',
            'bebanPajak',
            'totalBeban',
            'labaSebelumPajak',
            'labaBersih'
        );
    }

    private function isPriorYearCorrection(JournalEntry $entry): bool
    {
        $description = strtoupper((string) $entry->journal?->description);
        $transactionYear = (int) ($entry->journal?->transaction_date?->format('Y') ?? 0);

        if ($transactionYear === 0 || !str_contains($description, 'KOREKSI')) {
            return false;
        }

        preg_match_all('/20\d{2}/', $description, $matches);

        return collect($matches[0] ?? [])->contains(fn($year) => (int) $year < $transactionYear);
    }

    private function netIncomeBetween(string $dateStart, string $dateEnd): float
    {
        return $this->sumByTypeBetween('REVENUE', $dateStart, $dateEnd)
            - $this->sumByTypeBetween('EXPENSE', $dateStart, $dateEnd);
    }

    private function sumByTypeBetween(string $type, string $dateStart, string $dateEnd): float
    {
        $entries = JournalEntry::query()
            ->with(['coa', 'journal'])
            ->whereHas('journal', function ($q) use ($dateStart, $dateEnd) {
                $q->when($this->branch_id, fn($q2) => $q2->where('branch_id', $this->branch_id))
                  ->where('status', 'APPROVED')
                  ->whereDate('transaction_date', '>=', $dateStart)
                  ->whereDate('transaction_date', '<=', $dateEnd)
                  ->where('reference_no', 'not like', 'CLS%');
            })
            ->whereHas('coa', function ($q) use ($type) {
                $q->where('type', $type)->where('is_leaf', true);
            })
            ->get();

        return (float) $entries->sum(fn($entry) => $this->signedEntryAmount($entry));
    }

    private function priorYearNetIncomeBetween(string $dateStart, string $dateEnd): float
    {
        $totalPendapatan = $this->sumByPrefixesBetween(['41', '42'], 'REVENUE', $dateStart, $dateEnd);
        $totalBeban = $this->sumByPrefixesBetween(['51'], 'EXPENSE', $dateStart, $dateEnd, ['511', '515'])
            + $this->sumByPrefixesBetween(['515'], 'EXPENSE', $dateStart, $dateEnd)
            + $this->sumByPrefixesBetween(['52'], 'EXPENSE', $dateStart, $dateEnd, ['523']);
        $bebanPajak = $this->sumByPrefixesBetween(['523'], 'EXPENSE', $dateStart, $dateEnd);

        return $totalPendapatan - $totalBeban - $bebanPajak;
    }

    private function sumByPrefixesBetween(array $prefixes, string $type, string $dateStart, string $dateEnd, array $excludePrefixes = []): float
    {
        $entries = JournalEntry::query()
            ->with(['coa', 'journal'])
            ->whereHas('journal', function ($q) use ($dateStart, $dateEnd) {
                $q->when($this->branch_id, fn($q2) => $q2->where('branch_id', $this->branch_id))
                  ->where('status', 'APPROVED')
                  ->whereDate('transaction_date', '>=', $dateStart)
                  ->whereDate('transaction_date', '<=', $dateEnd)
                  ->where('reference_no', 'not like', 'CLS%');
            })
            ->whereHas('coa', function ($q) use ($prefixes, $type, $excludePrefixes) {
                $q->where('type', $type)
                  ->where('is_leaf', true)
                  ->where(function ($query) use ($prefixes) {
                      foreach ($prefixes as $prefix) {
                          $query->orWhere('coa_code', 'like', $prefix . '%');
                      }
                  });

                foreach ($excludePrefixes as $prefix) {
                    $q->where('coa_code', 'not like', $prefix . '%');
                }
            })
            ->get()
            ->reject(fn($entry) => $this->isPriorYearCorrection($entry));

        return (float) $entries->sum(function ($entry) use ($type) {
            if ($type === 'ASSET' || $type === 'EXPENSE') {
                return (float) $entry->debit - (float) $entry->credit;
            }

            return (float) $entry->credit - (float) $entry->debit;
        });
    }

    private function buildBalanceSheetData(): array
    {
        [$assetRows, $asset] = $this->financialRowsByType('ASSET', true);
        [$liabilityRows, $liability] = $this->financialRowsByType('LIABILITY', true);
        [$equityRows, $regularEquity] = $this->financialRowsByType('EQUITY', true, ['314000', '315000']);

        $dateTo = Carbon::parse($this->date_end ?: now());
        $yearStart = $dateTo->copy()->startOfYear();
        $shuLabaTahunLalu = $this->sumByCodes(['315000'], true);
        $currentYearRevenue = $this->sumByTypeBetween('REVENUE', $yearStart->toDateString(), $dateTo->toDateString());
        $currentYearExpense = $this->sumByTypeBetween('EXPENSE', $yearStart->toDateString(), $dateTo->toDateString());
        $profitTax = $this->profitTaxBreakdown($currentYearRevenue, $currentYearRevenue - $currentYearExpense, $dateTo->toDateString());
        $labaRugiTahunBerjalan = $profitTax['after_tax']
            - $this->closedProfitBalance((int) $dateTo->year, $dateTo->toDateString());

        if ($profitTax['estimated_tax'] > 0) {
            $liabilityRows[] = ['section' => 'Kewajiban', 'account' => 'Taksiran Pajak', 'amount' => $this->reportAmount($profitTax['estimated_tax'])];
            $liability += $profitTax['estimated_tax'];
        }

        $equity = $regularEquity + $shuLabaTahunLalu + $labaRugiTahunBerjalan;

        return array_values(array_merge(
            $assetRows,
            [['section' => 'Aset', 'account' => 'Total Aset', 'amount' => $this->reportAmount($asset)]],
            $liabilityRows,
            [['section' => 'Kewajiban', 'account' => 'Total Kewajiban', 'amount' => $this->reportAmount($liability)]],
            $equityRows,
            [
            ['section' => 'Modal', 'account' => 'SHU/LABA Tahun Lalu', 'amount' => $this->reportAmount($shuLabaTahunLalu)],
            ['section' => 'Modal', 'account' => 'SHU/LABA Tahun Berjalan', 'amount' => $this->reportAmount($labaRugiTahunBerjalan)],
            ['section' => 'Modal', 'account' => 'Jumlah Modal', 'amount' => $this->reportAmount($equity)],

            ['section' => 'Neraca', 'account' => 'Kewajiban + Ekuitas', 'amount' => $this->reportAmount($liability + $equity)],
            ['section' => 'Neraca', 'account' => 'Selisih (Aset - Kewajiban - Ekuitas)', 'amount' => $this->reportAmount($asset - ($liability + $equity))],
            ]
        ));
    }

    private function buildIncomeStatementData(): array
    {
        [$revenueRows, $totalRevenue] = $this->financialRowsByType('REVENUE');
        [$expenseRows, $totalExpense] = $this->financialRowsByType('EXPENSE');
        $netIncome = $totalRevenue - $totalExpense;

        return array_values(array_merge(
            $revenueRows,
            [['section' => 'Pendapatan', 'account' => 'Total Pendapatan', 'amount' => $this->reportAmount($totalRevenue)]],
            $expenseRows,
            [
                ['section' => 'Beban', 'account' => 'Total Beban', 'amount' => $this->reportAmount($totalExpense)],
                ['section' => 'Laba Rugi', 'account' => 'Laba Bersih', 'amount' => $this->reportAmount($netIncome)],
            ]
        ));
    }

    private function financialRowsByType(string $type, bool $asOf = false, array $excludeCodes = []): array
    {
        $coas = Coa::query()
            ->where('type', $type)
            ->where('is_leaf', true)
            ->when($excludeCodes !== [], fn($query) => $query->whereNotIn('coa_code', $excludeCodes))
            ->orderBy('coa_code')
            ->get()
            ->keyBy('coa_code');

        $entries = $this->baseJournalEntryQuery($asOf, in_array($type, ['REVENUE', 'EXPENSE'], true))
            ->whereHas('coa', function ($query) use ($type, $excludeCodes) {
                $query->where('type', $type)->where('is_leaf', true);

                if ($excludeCodes !== []) {
                    $query->whereNotIn('coa_code', $excludeCodes);
                }
            })
            ->get();

        $amountByCode = $entries
            ->groupBy(fn($entry) => $entry->coa->coa_code)
            ->map(fn($rows) => (float) $rows->sum(fn($entry) => $this->signedEntryAmount($entry)));

        $total = 0.0;
        $rows = $coas
            ->map(function (Coa $coa) use ($amountByCode, &$total) {
                $amount = $this->reportAmount((float) ($amountByCode[$coa->coa_code] ?? 0));

                if (abs($amount) < 0.01) {
                    return null;
                }

                $total += $amount;

                return [
                    'section' => $coa->category_label,
                    'account' => "{$coa->coa_code} - {$coa->name}",
                    'amount' => $amount,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return [$rows, $total];
    }

    private function closedProfitBalance(int $year, string $date): float
    {
        $entries = JournalEntry::query()
            ->with(['coa', 'journal'])
            ->whereHas('coa', fn ($query) => $query->where('coa_code', '315000'))
            ->whereHas('journal', function ($query) use ($year, $date) {
                $query->when($this->branch_id, fn ($q) => $q->where('branch_id', $this->branch_id))
                    ->where('status', 'APPROVED')
                    ->where('reference_no', 'like', "CLS{$year}%")
                    ->whereDate('transaction_date', '<=', $date);
            })
            ->get();

        return round((float) $entries->sum(fn ($entry) => $this->signedEntryAmount($entry)), 2);
    }

    private function profitTaxBreakdown(float $revenue, float $profit, string $date): array
    {
        $taxSetting = TaxSetting::effectiveFor($date);
        $taxRate = (float) ($taxSetting?->tax_rate ?? 0);
        $taxBase = $taxSetting?->calculation_base === 'TOTAL_REVENUE' ? $revenue : $profit;
        $estimatedTax = round(max(0, $taxBase) * ($taxRate / 100), 2);

        return [
            'estimated_tax' => $estimatedTax,
            'after_tax' => round($profit - $estimatedTax, 2),
        ];
    }

    private function signedEntryAmount(JournalEntry $entry): float
    {
        return in_array($entry->coa->type, ['ASSET', 'EXPENSE'], true)
            ? (float) $entry->debit - (float) $entry->credit
            : (float) $entry->credit - (float) $entry->debit;
    }

    private function reportAmount(float $amount): float
    {
        $rounded = round($amount, 2);

        return abs($rounded) < 0.01 ? 0.0 : $rounded;
    }

    private function buildCashFlowData(): array
    {
        $entries = $this->baseJournalEntryQuery()
            ->whereHas('coa', fn($q) => $q->where('is_cash', true)->where('is_leaf', true))
            ->get();

        $netCash = (float) $entries->sum(fn($entry) => (float) $entry->debit - (float) $entry->credit);
        $cashIn = (float) $entries->sum('debit');
        $cashOut = (float) $entries->sum('credit');

        $operating = $this->sumByPrefix('4', 'REVENUE') - $this->sumByPrefix('5', 'EXPENSE');
        $investing = $this->sumByPrefix('13', 'ASSET') + $this->sumByPrefix('14', 'ASSET');
        $financing = $this->sumByPrefix('2', 'LIABILITY') + $this->sumByPrefix('3', 'EQUITY');

        return [
            ['section' => 'ArusKas', 'account' => 'Arus Kas Masuk', 'amount' => $cashIn],
            ['section' => 'ArusKas', 'account' => 'Arus Kas Keluar', 'amount' => $cashOut],
            ['section' => 'ArusKas', 'account' => 'Arus Kas Aktivitas Operasi (estimasi)', 'amount' => $operating],
            ['section' => 'ArusKas', 'account' => 'Arus Kas Aktivitas Investasi (estimasi)', 'amount' => $investing],
            ['section' => 'ArusKas', 'account' => 'Arus Kas Aktivitas Pendanaan (estimasi)', 'amount' => $financing],
            ['section' => 'ArusKas', 'account' => 'Kenaikan/(Penurunan) Kas Bersih', 'amount' => $netCash],
        ];
    }

    private function buildEquityChangeData(): array
    {
        $modalAwal = $this->sumByPrefix('311', 'EQUITY', true);
        $cadangan = $this->sumByPrefix('312', 'EQUITY', true);
        $shu = $this->sumByPrefix('314', 'EQUITY', true) + $this->sumByPrefix('315', 'EQUITY', true);
        $equityMov = $this->sumByType('EQUITY', true);
        $income = $this->buildIncomeStatementData();
        $netIncomeRow = collect($income)->firstWhere('account', 'LABA BERSIH');
        $netIncome = (float) ($netIncomeRow['amount'] ?? 0);

        return [
            ['section' => 'PerubahanEkuitas', 'account' => 'Modal (Pokok & Wajib)', 'amount' => $modalAwal],
            ['section' => 'PerubahanEkuitas', 'account' => 'Cadangan', 'amount' => $cadangan],
            ['section' => 'PerubahanEkuitas', 'account' => 'SHU/Laba Ditahan', 'amount' => $shu],
            ['section' => 'PerubahanEkuitas', 'account' => 'Mutasi Ekuitas (Jurnal)', 'amount' => $equityMov],
            ['section' => 'PerubahanEkuitas', 'account' => 'Laba/Rugi Periode', 'amount' => $netIncome],
            ['section' => 'PerubahanEkuitas', 'account' => 'Estimasi Ekuitas Akhir', 'amount' => $equityMov + $netIncome],
        ];
    }

    public function downloadReport()
    {
        if (empty($this->report_type)) {
            session()->flash('error', 'Silakan pilih jenis laporan terlebih dahulu.');
            return;
        }

        $this->logActivity('EXPORT', "Mengunduh Laporan {$this->report_type}");

        $dataQuery = $this->getReportData();
        if ($dataQuery instanceof EloquentBuilder || $dataQuery instanceof QueryBuilder) {
            $records = $dataQuery->get();
        } elseif ($dataQuery instanceof Collection) {
            $records = $dataQuery;
        } elseif (is_array($dataQuery)) {
            $records = collect($dataQuery);
        } else {
            $records = collect([]);
        }

        $filename = "Laporan_{$this->report_type}_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [];
        if (array_key_exists($this->report_type, $this->templateReports)) {
            $columns = $this->templateReportColumns();
        }

        if ($columns === []) {
            switch ($this->report_type) {
            case 'savings':
            case 'saving_nominative':
            case 'saving_opening':
            case 'saving_closing':
                $columns = ['NO. REKENING', 'NO. CIF', 'NAMA NASABAH', 'CABANG', 'PRODUK', 'SALDO', 'TGL STATUS', 'STATUS'];
                break;
            case 'loans':
            case 'loan_nominative':
                $columns = ['NO. PINJAMAN', 'NO. CIF', 'NAMA NASABAH', 'REK. AUTODEBET', 'CABANG', 'PRODUK', 'PLAFON', 'BAKI DEBET', 'STATUS', 'TGL BUAT'];
                break;
            case 'loan_repayment':
            case 'loan_disbursement':
            case 'loan_settlement':
                $columns = ['TANGGAL', 'NO. PINJAMAN', 'NO. CIF', 'NAMA NASABAH', 'CABANG', 'PRODUK', 'TIPE', 'POKOK', 'BUNGA', 'DENDA', 'TOTAL', 'CHANNEL'];
                break;
            case 'deposits':
            case 'deposit_nominative':
                $columns = ['NO. SIMPANAN BERJANGKA', 'NO. CIF', 'NAMA NASABAH', 'REK. PEMBAYARAN BUNGA', 'CABANG', 'PRODUK', 'NOMINAL', 'BUNGA/BULAN', 'TGL PENEMPATAN', 'TGL JATUH TEMPO', 'STATUS'];
                break;
            case 'deposit_interest_payment':
            case 'deposit_withdrawal':
            case 'deposit_placement':
                $columns = ['TANGGAL', 'NO. DEPOSITO', 'NO. CIF', 'NAMA NASABAH', 'CABANG', 'PRODUK', 'REK. PEMBAYARAN', 'TIPE', 'NOMINAL', 'CHANNEL'];
                break;
            case 'cifs':
                $columns = ['NO. CIF', 'NAMA LENGKAP', 'CABANG', 'NIK', 'STATUS', 'TGL REGISTRASI'];
                break;
            case 'assets':
                $columns = ['No.', 'Nomor Rekening/Seri', 'Nama Aktiva', 'Tanggal Perolehan', 'Usia Pakai', 'Tanggal Habis Buku', 'Nilai Perolehan', 'Nilai Buku Bulan lalu', 'Nilai Penyusutan', 'Akumulasi Penyusutan', 'Nilai Buku Bulan Sekarang'];
                break;
            case 'asset_rentals':
                $columns = ['NO. KONTRAK', 'KODE ASET', 'NAMA ASET', 'PENYEWA', 'CABANG', 'TGL MULAI', 'TGL SELESAI', 'TARIF BULANAN', 'PEMBAYARAN TERAKHIR', 'NOMINAL PEMBAYARAN', 'STATUS'];
                break;
            case 'audit':
                $columns = ['TANGGAL', 'WAKTU', 'PENGGUNA', 'AKSI', 'MODUL', 'DESKRIPSI', 'IP ADDRESS'];
                break;
            case 'balance_sheet':
                $columns = ['SECTION', 'AKUN', 'NOMINAL'];
                break;
            case 'income_statement':
                $columns = ['SECTION', 'AKUN', 'NOMINAL'];
                break;
            case 'cash_flow':
                $columns = ['SECTION', 'AKUN', 'NOMINAL'];
                break;
            case 'equity_change':
                $columns = ['SECTION', 'AKUN', 'NOMINAL'];
                break;
        }
        }

        $callback = function() use($records, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($records as $index => $row) {
                if (array_key_exists($this->report_type, $this->templateReports)) {
                    fputcsv($file, $this->templateReportRow($row, $index + 1));
                    continue;
                }

                switch ($this->report_type) {
                    case 'savings':
                    case 'saving_nominative':
                    case 'saving_opening':
                    case 'saving_closing':
                        $statusDate = $this->report_type === 'saving_closing' ? $row->closed_at : $row->opened_at;
                        fputcsv($file, [
                            strtoupper($row->account_no),
                            strtoupper($row->cif->cif_no ?? '-'),
                            strtoupper($row->cif->name ?? '-'),
                            strtoupper($row->branch->name ?? '-'),
                            strtoupper($row->product->name ?? '-'),
                            $row->balance,
                            $statusDate ? \Carbon\Carbon::parse($statusDate)->format('Y-m-d') : '-',
                            strtoupper($row->status),
                        ]);
                        break;
                    case 'loans':
                    case 'loan_nominative':
                        fputcsv($file, [
                            strtoupper($row->account_no),
                            strtoupper($row->cif->cif_no ?? '-'),
                            strtoupper($row->cif->name ?? '-'),
                            strtoupper($row->savingAccount->account_no ?? '-'),
                            strtoupper($row->branch->name ?? '-'),
                            strtoupper($row->product->name ?? '-'),
                            $row->principal_amount,
                            $row->outstanding_principal,
                            strtoupper($row->status),
                            $row->created_at->format('Y-m-d')
                        ]);
                        break;
                    case 'loan_repayment':
                    case 'loan_disbursement':
                    case 'loan_settlement':
                        fputcsv($file, [
                            $row->created_at?->format('Y-m-d') ?? '-',
                            strtoupper($row->loanAccount->account_no ?? '-'),
                            strtoupper($row->loanAccount->cif->cif_no ?? '-'),
                            strtoupper($row->loanAccount->cif->name ?? '-'),
                            strtoupper($row->loanAccount->branch->name ?? '-'),
                            strtoupper($row->loanAccount->product->name ?? '-'),
                            strtoupper($row->transaction_type),
                            $row->amount_principal,
                            $row->amount_interest,
                            $row->amount_penalty,
                            $row->total_amount,
                            strtoupper($row->channel ?? '-'),
                        ]);
                        break;
                    case 'deposits':
                    case 'deposit_nominative':
                        fputcsv($file, [
                            strtoupper($row->account_no),
                            strtoupper($row->cif->cif_no ?? '-'),
                            strtoupper($row->cif->name ?? '-'),
                            strtoupper($row->savingAccount->account_no ?? '-'),
                            strtoupper($row->branch->name ?? '-'),
                            strtoupper($row->product->name ?? '-'),
                            $row->amount,
                            $row->schedules->first()?->net_interest ?? 0,
                            $row->placement_date ? $row->placement_date->format('Y-m-d') : '-',
                            $row->maturity_date ? $row->maturity_date->format('Y-m-d') : '-',
                            strtoupper($row->status),
                        ]);
                        break;
                    case 'deposit_interest_payment':
                    case 'deposit_withdrawal':
                    case 'deposit_placement':
                        fputcsv($file, [
                            $row->transaction_date?->format('Y-m-d') ?? '-',
                            strtoupper($row->account->account_no ?? '-'),
                            strtoupper($row->account->cif->cif_no ?? '-'),
                            strtoupper($row->account->cif->name ?? '-'),
                            strtoupper($row->account->branch->name ?? '-'),
                            strtoupper($row->account->product->name ?? '-'),
                            strtoupper($row->account->savingAccount->account_no ?? '-'),
                            strtoupper($row->type),
                            $row->amount,
                            strtoupper($row->channel ?? '-'),
                        ]);
                        break;
                    case 'cifs':
                        fputcsv($file, [
                            strtoupper($row->cif_no),
                            strtoupper($row->name),
                            strtoupper($row->branch->name ?? '-'),
                            $row->nik,
                            strtoupper($row->status),
                            $row->created_at->format('Y-m-d')
                        ]);
                        break;
                    case 'assets':
                        fputcsv($file, $this->assetInventoryRow($row, $index + 1));
                        break;
                    case 'asset_rentals':
                        fputcsv($file, [
                            strtoupper($row->contract_no),
                            strtoupper($row->asset->asset_code ?? '-'),
                            strtoupper($row->asset->name ?? '-'),
                            strtoupper($row->rekanan->name ?? '-'),
                            strtoupper($row->branch->name ?? '-'),
                            $row->rental_start_date ? $row->rental_start_date->format('Y-m-d') : '-',
                            $row->rental_end_date ? $row->rental_end_date->format('Y-m-d') : '-',
                            $row->monthly_rate,
                            $row->latestPaidBilling?->paid_at?->format('Y-m-d') ?? '-',
                            $row->latestPaidBilling?->amount ?? 0,
                            strtoupper($row->status),
                        ]);
                        break;
                    case 'audit':
                        fputcsv($file, [
                            $row->created_at->format('Y-m-d'),
                            $row->created_at->format('H:i:s'),
                            strtoupper($row->user->name ?? 'SYSTEM'),
                            strtoupper($row->action),
                            strtoupper(class_basename($row->model_type)),
                            strtoupper($row->description ?? ''),
                            strtoupper($row->ip_address ?? '')
                        ]);
                        break;
                    case 'balance_sheet':
                    case 'income_statement':
                    case 'cash_flow':
                    case 'equity_change':
                        fputcsv($file, [
                            $row['section'] ?? '',
                            $row['account'] ?? '',
                            $row['amount'] ?? 0,
                        ]);
                        break;
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $query = $this->getReportData();
        $results = is_object($query) && method_exists($query, 'paginate')
            ? $query->paginate(20)
            : (is_object($query) && method_exists($query, 'values') ? $query->values() : []);

        return view('livewire.reports.index', [
            'results' => $results
        ])->layout('layouts.app');
    }
}
