<?php

namespace App\Livewire\Approvals;

use App\Models\ApprovalRequest;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Illuminate\Support\Str;

class Index extends Component
{
    use WithPagination, LogsActivity, WithLogout;

    public $search = '';
    public $statusFilter = 'PENDING';
    
    public $showRequestModal = false;
    public $selectedRequest = null;
    public $rejectionReason = '';

    public $user, $role;

    public function mount()
    {
        $this->user = Auth::user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->logActivity('NAVIGATE', 'Daftar Persetujuan');
    }

    public function viewRequest($id)
    {
        $this->selectedRequest = ApprovalRequest::with('requester')->findOrFail($id);
        $this->showRequestModal = true;
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $request = ApprovalRequest::whereKey($id)->lockForUpdate()->firstOrFail();

            if ($request->status !== 'PENDING') {
                DB::commit();
                session()->flash('success', 'Request ini sudah diproses sebelumnya.');
                return;
            }

            // Self-Approval Prevention (High Priority Check)
            if ($request->requested_by === Auth::id()) {
                DB::rollBack();
                session()->flash('error', 'Keamanan: Anda tidak diperbolehkan menyetujui pengajuan yang Anda buat sendiri.');
                return;
            }

            // Authorization Check with specific feedback
            $authResult = $this->checkAuthorization($request);
            if ($authResult !== true) {
                DB::rollBack();
                session()->flash('error', "Otorisasi Gagal: {$authResult}");
                return;
            }

            $this->applyAction($request);

            $approvalUpdate = [
                'status' => 'APPROVED',
                'processed_by' => Auth::id(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('approval_requests', 'approved_by')) {
                $approvalUpdate['approved_by'] = Auth::id();
            }
            if (Schema::hasColumn('approval_requests', 'approved_at')) {
                $approvalUpdate['approved_at'] = now();
            }

            $request->update($approvalUpdate);

            DB::commit();
            $this->logActivity('APPROVE', "Menyetujui request [{$request->module_key}] {$request->action}");
            $this->dispatch('approval-processed');
            $this->showRequestModal = false;
            $this->selectedRequest = null;
            session()->flash('success', 'Request berhasil disetujui dan diterapkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menerapkan perubahan: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        $this->validate(['rejectionReason' => 'required|min:5']);

        DB::beginTransaction();
        try {
            $request = ApprovalRequest::whereKey($id)->lockForUpdate()->firstOrFail();

            if ($request->status !== 'PENDING') {
                DB::commit();
                session()->flash('success', 'Request ini sudah diproses sebelumnya.');
                return;
            }

            // Authorization Check
            $authResult = $this->checkAuthorization($request);
            if ($authResult !== true) {
                DB::rollBack();
                session()->flash('error', "Otorisasi Gagal: {$authResult}");
                return;
            }

            // Self-Approval Prevention
            if ($request->requested_by === Auth::id()) {
                DB::rollBack();
                session()->flash('error', 'Anda tidak diperbolehkan memproses pengajuan Anda sendiri.');
                return;
            }

            $request->update([
                'status' => 'REJECTED',
                'processed_by' => Auth::id(),
                'reason' => $this->rejectionReason,
                'updated_at' => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menolak permohonan: ' . $e->getMessage());
            return;
        }

        $this->logActivity('REJECT', "Menolak request [{$request->module_key}] {$request->action}");
        $this->showRequestModal = false;
        $this->selectedRequest = null;
        $this->dispatch('approval-processed');
        session()->flash('success', 'Request telah ditolak.');
    }

    private function checkAuthorization($request)
    {
        $configKeys = $this->approvalConfigKeys($request->module_key, $request->action);
        $configs = \App\Models\ApprovalConfig::whereIn('module_key', $configKeys)
            ->where('action', $request->action)
            ->get()
            ->keyBy('module_key');
        $config = collect($configKeys)->map(fn($key) => $configs->get($key))->filter()->first();

        // If no config, allow everyone (standard behavior)
        if (!$config) {
            return true;
        }

        // If config is not active, allow it
        if (!$config->is_active) {
            return true;
        }

        // If no specific roles set, anyone with access to this menu can approve
        if (empty($config->authorized_roles)) {
            return true;
        }

        // Check if user has ANY of the authorized roles
        if (Auth::user()->hasAnyRole($config->authorized_roles)) {
            return true;
        }

        $rolesNeeded = implode(', ', $config->authorized_roles);
        return "Role Anda tidak memiliki wewenang. Roles yang diizinkan untuk [{$request->module_key} - {$request->action}]: {$rolesNeeded}";
    }

    private function approvalConfigKeys(string $moduleKey, string $action): array
    {
        if ($moduleKey === 'assets' && $action === 'CREATE') {
            return ['assets.create', 'assets'];
        }

        return [$moduleKey];
    }

    public function render()
    {
        $query = ApprovalRequest::with('requester');

        // Filter based on status
        $query->where('status', $this->statusFilter);

        // Filter Isolasi Data Cabang & Perusahaan (Kecuali Admin Pusat)
        if ($this->role !== 'Admin') {
            $query->whereHas('requester', function ($q) {
                $q->where('branch_id', $this->user->branch_id)
                  ->where('company_id', $this->user->company_id);
            });
        }

        return view('livewire.approvals.inbox', [
            'requests' => $query->orderBy('created_at', 'desc')->paginate(10)
        ])->layout('layouts.app');
    }

    public function approvalTitle(ApprovalRequest $request): string
    {
        $module = [
            'deposits.placement' => 'Penempatan Simpanan Berjangka',
            'deposits.withdrawal' => 'Pencairan Simpanan Berjangka',
            'deposits.interest-payment' => 'Pembayaran Bunga Simpanan Berjangka',
            'loans.edit' => 'Perubahan Pengajuan Pinjaman',
            'loans.repayment' => 'Pembayaran Angsuran Pinjaman',
            'loans.settlement' => 'Pelunasan Pinjaman',
            'savings.deposit' => 'Setoran Simpanan',
            'savings.withdrawal' => 'Penarikan Simpanan',
            'savings.transfer' => 'Transfer Antar Rekening',
            'savings.distribution' => 'Distribusi Dana Simpanan',
            'assets.create' => 'Tambah Aset Baru',
            'assets.update' => 'Perubahan Inventaris',
            'shu.distributions' => 'Distribusi SHU',
        ][$request->module_key] ?? Str::of($request->module_key)->replace(['.', '_', '-'], ' ')->title();

        $action = [
            'CREATE' => 'Buat',
            'UPDATE' => 'Ubah',
            'DELETE' => 'Hapus',
            'CLOSE' => 'Tutup/Cairkan',
            'PAY' => 'Bayar',
            'REPAYMENT' => 'Bayar',
            'Repayment' => 'Bayar',
            'SETTLEMENT' => 'Lunasi',
            'Settlement' => 'Lunasi',
            'DEPOSIT' => 'Setor',
            'WITHDRAWAL' => 'Tarik',
            'TRANSFER' => 'Transfer',
            'DISTRIBUTE' => 'Distribusi',
        ][$request->action] ?? Str::of($request->action)->replace('_', ' ')->title();

        return "{$action} {$module}";
    }

    public function approvalSummary(ApprovalRequest $request): array
    {
        $data = is_array($request->data_after) ? $request->data_after : (json_decode($request->data_after, true) ?: []);
        $module = $request->module_key;
        $action = $request->action;

        if ($module === 'deposits.placement') {
            $channel = $data['deposit_channel'] ?? null;
            $sourceValue = $channel === 'INTERNAL'
                ? $this->savingAccountNo($data['source_saving_account_id'] ?? $data['saving_account_id'] ?? null)
                : $this->coaName($data['coa_override_id'] ?? null);

            return [
                ['label' => 'Anggota', 'value' => $this->cifName($data['cif_id'] ?? null)],
                ['label' => 'Produk', 'value' => $this->depositProductName($data['deposit_product_id'] ?? null)],
                ['label' => 'Bilyet', 'value' => $this->depositBilyetName($data['deposit_bilyet_id'] ?? null)],
                ['label' => 'Nominal', 'value' => $this->money($data['amount'] ?? 0)],
                ['label' => 'Tenor', 'value' => ($data['tenor'] ?? '-') . ' bulan'],
                ['label' => 'Suku bunga', 'value' => $this->percent($data['interest_rate'] ?? null)],
                ['label' => 'Tgl penempatan', 'value' => $data['placement_date'] ?? '-'],
                ['label' => 'Sumber dana', 'value' => $this->channelLabel($channel)],
                ['label' => 'Rek./COA sumber', 'value' => $sourceValue],
                ['label' => 'Rek. pencairan', 'value' => $this->savingAccountNo($data['saving_account_id'] ?? null)],
                ['label' => 'ARO', 'value' => $this->rolloverLabel($data['rollover_type'] ?? null)],
            ];
        }

        if ($module === 'deposits.withdrawal') {
            return [
                ['label' => 'Deposito', 'value' => $this->depositAccountNo($data['deposit_account_id'] ?? $request->model_id)],
                ['label' => 'Channel', 'value' => $this->channelLabel($data['payout_channel'] ?? null)],
                ['label' => 'Tujuan simpanan', 'value' => $this->savingAccountNo($data['saving_account_id'] ?? null)],
                ['label' => 'Penalti', 'value' => $this->money($data['penalty_amount'] ?? 0)],
                ['label' => 'COA kas/bank', 'value' => $this->coaName($data['coa_override_id'] ?? null)],
            ];
        }

        if ($module === 'deposits.interest-payment') {
            $schedule = isset($data['deposit_schedule_id']) ? \App\Models\DepositSchedule::with('account')->find($data['deposit_schedule_id']) : null;
            return [
                ['label' => 'Deposito', 'value' => $schedule?->account?->account_no ?? '-'],
                ['label' => 'Bulan ke', 'value' => $schedule?->month_index ?? '-'],
                ['label' => 'Bunga bruto', 'value' => $this->money($schedule?->gross_interest ?? 0)],
                ['label' => 'Pajak', 'value' => $this->money($schedule?->tax_amount ?? 0)],
                ['label' => 'Bunga neto', 'value' => $this->money($schedule?->net_interest ?? 0)],
            ];
        }

        if ($module === 'loans.disbursement') {
            $loan = \App\Models\LoanAccount::with(['product', 'savingAccount', 'schedules'])
                ->find($data['loan_account_id'] ?? $request->model_id);
            $breakdown = $this->loanDisbursementBreakdown($loan);

            return [
                ['label' => 'Pinjaman', 'value' => $loan?->account_no ?? '-'],
                ['label' => 'Produk', 'value' => $loan?->product ? "{$loan->product->product_code} - {$loan->product->name}" : '-'],
                ['label' => 'Plafon', 'value' => $this->money($breakdown['principal'])],
                ['label' => 'Total potongan', 'value' => $this->money($breakdown['deductions'])],
                ['label' => 'Nominal cair bersih', 'value' => $this->money($breakdown['net_disbursed'])],
                ['label' => 'Dana diblokir', 'value' => $this->money($breakdown['blocked'])],
                ['label' => 'Masuk simpanan', 'value' => $this->money($breakdown['saving_credit'])],
                ['label' => 'Channel', 'value' => $this->channelLabel($data['channel'] ?? null)],
                ['label' => 'Rekening tujuan', 'value' => $loan?->savingAccount ? "{$loan->savingAccount->account_no} - {$loan->savingAccount->product?->name}" : '-'],
                ['label' => 'COA kas/bank', 'value' => $this->coaName($data['coa_override_id'] ?? null)],
            ];
        }

        if ($module === 'loans.settlement') {
            return [
                ['label' => 'Pinjaman', 'value' => $this->loanAccountNo($data['loan_account_id'] ?? $request->model_id)],
                ['label' => 'Pokok', 'value' => $this->money($data['principal_amount'] ?? 0)],
                ['label' => 'Kewajiban bunga', 'value' => $this->money($data['interest_amount'] ?? 0)],
                ['label' => 'Denda', 'value' => $this->money($data['penalty_amount'] ?? 0)],
                ['label' => 'Total pelunasan', 'value' => $this->money($data['amount'] ?? 0)],
            ];
        }

        if ($module === 'loans.repayment') {
            return [
                ['label' => 'Pinjaman', 'value' => $this->loanAccountNo($data['loan_account_id'] ?? $request->model_id)],
                ['label' => 'Nominal bayar', 'value' => $this->money($data['amount'] ?? 0)],
                ['label' => 'Channel', 'value' => $this->channelLabel($data['channel'] ?? null)],
                ['label' => 'COA kas/bank', 'value' => $this->coaName($data['coa_override_id'] ?? null)],
                ['label' => 'Rek./COA sumber', 'value' => $data['source_account'] ?? '-'],
                ['label' => 'Saldo sumber', 'value' => isset($data['source_balance']) ? $this->money($data['source_balance']) : '-'],
                ['label' => 'Status saldo', 'value' => $data['source_balance_status'] ?? '-'],
            ];
        }

        if ($module === 'loans.edit') {
            return [
                ['label' => 'Pinjaman', 'value' => $this->loanAccountNo($request->model_id)],
                ['label' => 'Anggota', 'value' => $this->cifName($data['cif_id'] ?? null)],
                ['label' => 'Produk', 'value' => $this->loanProductName($data['loan_product_id'] ?? null)],
                ['label' => 'Plafon', 'value' => $this->money($data['principal_amount'] ?? 0)],
                ['label' => 'Tenor', 'value' => ($data['tenor'] ?? '-') . ' bulan'],
                ['label' => 'Suku bunga', 'value' => $this->percent($data['interest_rate'] ?? null)],
                ['label' => 'Tgl realisasi', 'value' => $data['disbursement_date'] ?? '-'],
            ];
        }

        if ($module === 'asset-rentals.index' && !empty($data['bulk_payments'])) {
            $totals = $this->approvalBulkPaymentTotals($request);

            return [
                ['label' => 'Jenis', 'value' => 'Pembayaran Sewa Aset Masal'],
                ['label' => 'Jumlah tagihan', 'value' => number_format($totals['count'], 0, ',', '.')],
                ['label' => 'Total nominal', 'value' => $this->money($totals['amount'])],
                ['label' => 'COA debit', 'value' => $this->coaName($data['payment_debit_coa_id'] ?? null)],
                ['label' => 'COA kredit', 'value' => $this->coaName($data['payment_credit_coa_id'] ?? null)],
            ];
        }

        if (str_starts_with($module, 'savings')) {
            if ($module === 'savings.distribution') {
                $items = $data['items'] ?? [];
                return [
                    ['label' => 'Produk', 'value' => $this->savingProductName($data['saving_product_id'] ?? null)],
                    ['label' => 'Jenis', 'value' => ($data['distribution_type'] ?? null) === 'CREDIT' ? 'Kredit' : 'Debit'],
                    ['label' => 'Jumlah rekening', 'value' => number_format(count($items), 0, ',', '.')],
                    ['label' => 'Total nominal', 'value' => $this->money(collect($items)->sum(fn($item) => (float) ($item['amount'] ?? 0)))],
                    ['label' => 'Channel', 'value' => $this->channelLabel($data['channel'] ?? null)],
                    ['label' => 'COA transaksi', 'value' => $this->coaName($data['coa_override_id'] ?? null)],
                    ['label' => 'Keterangan', 'value' => $data['description'] ?? '-'],
                ];
            }

            return [
                ['label' => 'Rekening', 'value' => $this->savingAccountNo($data['saving_account_id'] ?? $data['from_account_id'] ?? null)],
                ['label' => 'Tujuan', 'value' => $this->savingAccountNo($data['to_account_id'] ?? null)],
                ['label' => 'Nominal', 'value' => $this->money($data['amount'] ?? 0)],
                ['label' => 'Channel', 'value' => $this->channelLabel($data['channel'] ?? null)],
                ['label' => 'Keterangan', 'value' => $data['description'] ?? '-'],
            ];
        }

        if ($module === 'journals') {
            $totals = $this->journalEntryTotals($data['entries'] ?? []);

            return [
                ['label' => 'Tanggal Transaksi', 'value' => $data['transaction_date'] ?? '-'],
                ['label' => 'No Referensi', 'value' => $data['reference_no'] ?? '-'],
                ['label' => 'Cabang', 'value' => $this->branchName($data['branch_id'] ?? null)],
                ['label' => 'Keterangan', 'value' => $data['description'] ?? '-'],
                ['label' => 'Total Debit', 'value' => $this->money($totals['debit'])],
                ['label' => 'Total Kredit', 'value' => $this->money($totals['credit'])],
                ['label' => 'Jumlah Baris', 'value' => $totals['count'] . ' baris jurnal'],
            ];
        }

        if (in_array($module, ['assets.create', 'assets.update'], true)) {
            return [
                ['label' => 'Kode aset', 'value' => $data['asset_code'] ?? $this->assetCode($request->model_id)],
                ['label' => 'Nama aset', 'value' => $data['name'] ?? '-'],
                ['label' => 'Golongan', 'value' => $this->assetCategoryName($data['asset_category_id'] ?? null)],
                ['label' => 'Cabang', 'value' => $this->branchName($data['branch_id'] ?? null)],
                ['label' => 'Harga perolehan', 'value' => $this->money($data['purchase_price'] ?? 0)],
                ['label' => 'Sumber bayar', 'value' => $data['asset_purchase_channel'] ?? '-'],
                ['label' => 'COA kredit', 'value' => $this->coaName($data['asset_purchase_credit_coa_id'] ?? null)],
                ['label' => 'Lokasi', 'value' => $data['location'] ?? '-'],
                ['label' => 'Kondisi', 'value' => $data['condition'] ?? '-'],
            ];
        }

        return collect($data)
            ->take(8)
            ->map(fn($value, $key) => ['label' => $this->humanLabel($key), 'value' => $this->humanValue($key, $value)])
            ->values()
            ->all();
    }

    public function compactSummary(ApprovalRequest $request): string
    {
        return collect($this->approvalSummary($request))
            ->filter(fn($row) => filled($row['value'] ?? null) && ($row['value'] ?? '-') !== '-')
            ->take(3)
            ->map(fn($row) => "{$row['label']}: {$row['value']}")
            ->implode(' | ');
    }

    public function approvalTechnicalRows(ApprovalRequest $request, string $side = 'after'): array
    {
        $source = $side === 'before' ? $request->data_before : $request->data_after;
        $data = is_array($source) ? $source : (json_decode($source, true) ?: []);

        return collect($data)
            ->map(fn($value, $key) => [
                'key' => $key,
                'label' => $this->humanLabel($key),
                'value' => $this->humanValue($key, $value),
            ])
            ->values()
            ->all();
    }

    public function approvalJournalEntries(ApprovalRequest $request): array
    {
        $data = is_array($request->data_after) ? $request->data_after : (json_decode($request->data_after, true) ?: []);
        return $this->journalEntryRows($data['entries'] ?? []);
    }

    public function approvalJournalTotals(ApprovalRequest $request): array
    {
        $data = is_array($request->data_after) ? $request->data_after : (json_decode($request->data_after, true) ?: []);
        return $this->journalEntryTotals($data['entries'] ?? []);
    }

    public function approvalBulkPaymentRows(ApprovalRequest $request): array
    {
        $data = is_array($request->data_after) ? $request->data_after : (json_decode($request->data_after, true) ?: []);

        return collect($data['bulk_payments'] ?? [])
            ->map(fn($item) => [
                'row' => $item['row'] ?? '-',
                'contract_no' => $item['contract_no'] ?? '-',
                'billing_period' => $item['billing_period'] ?? '-',
                'amount' => (float) ($item['amount'] ?? 0),
                'note' => $item['note'] ?? '',
            ])
            ->values()
            ->all();
    }

    public function approvalBulkPaymentTotals(ApprovalRequest $request): array
    {
        $rows = $this->approvalBulkPaymentRows($request);

        return [
            'count' => count($rows),
            'amount' => collect($rows)->sum('amount'),
        ];
    }

    public function approvalSavingDistributionRows(ApprovalRequest $request): array
    {
        $data = is_array($request->data_after) ? $request->data_after : (json_decode($request->data_after, true) ?: []);
        $items = collect($data['items'] ?? []);
        $accountCounts = $items
            ->pluck('account_no')
            ->map(fn($accountNo) => trim((string) $accountNo))
            ->countBy();

        $accounts = \App\Models\SavingAccount::with(['cif', 'product'])
            ->whereIn('account_no', $accountCounts->keys()->all())
            ->get()
            ->keyBy('account_no');

        return $items
            ->map(function ($item, $index) use ($accountCounts, $accounts) {
                $accountNo = trim((string) ($item['account_no'] ?? ''));
                $account = $accounts->get($accountNo);
                $amount = (float) ($item['amount'] ?? 0);

                return [
                    'no' => $index + 1,
                    'account_no' => $accountNo ?: '-',
                    'member' => $account?->cif?->name ?? '-',
                    'amount' => $amount,
                    'amount_display' => $this->money($amount),
                    'note' => trim((string) ($item['note'] ?? '')),
                    'status' => $account?->status ?? 'TIDAK DITEMUKAN',
                    'balance_display' => $account ? $this->money($account->balance) : '-',
                    'effective_balance_display' => $account ? $this->money($account->effective_balance) : '-',
                    'is_duplicate' => ($accountCounts[$accountNo] ?? 0) > 1,
                    'duplicate_count' => $accountCounts[$accountNo] ?? 0,
                    'is_missing' => ! $account,
                ];
            })
            ->values()
            ->all();
    }

    public function approvalSavingDistributionTotals(ApprovalRequest $request): array
    {
        $rows = $this->approvalSavingDistributionRows($request);

        return [
            'count' => count($rows),
            'unique_accounts' => collect($rows)->pluck('account_no')->unique()->count(),
            'duplicate_accounts' => collect($rows)->where('is_duplicate', true)->pluck('account_no')->unique()->count(),
            'missing_accounts' => collect($rows)->where('is_missing', true)->count(),
            'amount' => collect($rows)->sum('amount'),
        ];
    }

    private function money($value): string
    {
        return 'Rp ' . number_format((float) $value, 2, ',', '.');
    }

    private function loanDisbursementBreakdown(?\App\Models\LoanAccount $loan): array
    {
        if (! $loan) {
            return [
                'principal' => 0,
                'deductions' => 0,
                'net_disbursed' => 0,
                'blocked' => 0,
                'saving_credit' => 0,
            ];
        }

        $prepaid = $loan->prepaid_installment_count > 0
            ? $loan->schedules
                ->sortBy('installment_number')
                ->take($loan->prepaid_installment_count)
                ->sum(fn ($schedule) => (float) $schedule->principal_amount + (float) $schedule->interest_amount)
            : 0;

        $principal = (float) $loan->principal_amount;
        $blocked = (float) ($loan->blocked_savings_amount ?? 0);
        $deductions = (float) ($loan->provision_fee ?? 0)
            + (float) ($loan->admin_fee ?? 0)
            + (float) ($loan->insurance_fee ?? 0)
            + (float) ($loan->flagging_fee ?? 0)
            + (float) ($loan->stamp_duty_fee ?? 0)
            + (float) ($loan->diskonto_upfront_amount ?? 0)
            + $prepaid
            + $blocked;
        $netDisbursed = round($principal - $deductions, 2);

        return [
            'principal' => $principal,
            'deductions' => round($deductions, 2),
            'net_disbursed' => $netDisbursed,
            'blocked' => $blocked,
            'saving_credit' => round($netDisbursed + $blocked, 2),
        ];
    }

    private function percent($value): string
    {
        return $value === null ? '-' : number_format((float) $value, 2, ',', '.') . '%';
    }

    private function channelLabel($value): string
    {
        return [
            'INTERNAL' => 'Simpanan internal',
            'CASH' => 'Tunai / Kas',
            'ABA' => 'ABA / Bank',
            'COA' => 'COA Manual',
        ][(string) $value] ?? ($value ?: '-');
    }

    private function rolloverLabel($value): string
    {
        return [
            'NONE' => 'Non-ARO',
            'PRINCIPAL' => 'ARO Pokok',
            'PRINCIPAL_INTEREST' => 'ARO Pokok + Bunga',
        ][(string) $value] ?? ($value ?: '-');
    }

    private function cifName($id): string
    {
        $cif = $id ? \App\Models\Cif::find($id) : null;
        return $cif ? "{$cif->cif_no} - {$cif->name}" : '-';
    }

    private function savingAccountNo($id): string
    {
        $account = $id ? \App\Models\SavingAccount::with('product')->find($id) : null;
        return $account ? "{$account->account_no} - {$account->product?->name}" : '-';
    }

    private function savingProductName($id): string
    {
        $product = $id ? \App\Models\SavingProduct::find($id) : null;
        return $product ? "{$product->product_code} - {$product->name}" : '-';
    }

    private function depositAccountNo($id): string
    {
        return ($id ? \App\Models\DepositAccount::find($id)?->account_no : null) ?: '-';
    }

    private function depositProductName($id): string
    {
        $product = $id ? \App\Models\DepositProduct::find($id) : null;
        return $product ? "{$product->product_code} - {$product->name}" : '-';
    }

    private function loanProductName($id): string
    {
        $product = $id ? \App\Models\LoanProduct::find($id) : null;
        return $product ? "{$product->product_code} - {$product->name}" : '-';
    }

    private function depositBilyetName($id): string
    {
        $bilyet = $id ? \App\Models\DepositBilyet::withTrashed()->find($id) : null;
        return $bilyet ? ($bilyet->kode_bilyet ?: $bilyet->bilyet_number) : '-';
    }

    private function loanAccountNo($id): string
    {
        return ($id ? \App\Models\LoanAccount::find($id)?->account_no : null) ?: '-';
    }

    private function coaName($id): string
    {
        $coa = $id ? \App\Models\Coa::find($id) : null;
        return $coa ? "{$coa->coa_code} - {$coa->name}" : '-';
    }

    private function userName($id): string
    {
        return ($id ? \App\Models\User::find($id)?->name : null) ?: '-';
    }

    private function branchName($id): string
    {
        $branch = $id ? \App\Models\Branch::find($id) : null;
        return $branch ? "{$branch->branch_code} - {$branch->name}" : '-';
    }

    private function assetCode($id): string
    {
        return ($id ? \App\Models\Asset::find($id)?->asset_code : null) ?: '-';
    }

    private function assetCategoryName($id): string
    {
        $category = $id ? \App\Models\AssetCategory::find($id) : null;
        return $category ? "{$category->code} - {$category->name}" : '-';
    }

    private function marketingName($id): string
    {
        $marketing = $id ? \App\Models\MarketingMaster::find($id) : null;
        return $marketing ? "{$marketing->marketing_code} - {$marketing->name}" : '-';
    }

    private function humanLabel($key): string
    {
        return [
            'amount' => 'Nominal',
            'branch_id' => 'Cabang',
            'cif_id' => 'CIF / Anggota',
            'coa_override_id' => 'COA Kas / Bank',
            'created_by' => 'Dibuat Oleh',
            'description' => 'Keterangan',
            'deposit_account_id' => 'Rekening Deposito',
            'deposit_bilyet_id' => 'Bilyet Deposito',
            'deposit_channel' => 'Sumber Dana',
            'deposit_product_id' => 'Produk Deposito',
            'entries' => 'Rincian Jurnal',
            'interest_calculation_type' => 'Periode Hitung Bunga',
            'interest_rate' => 'Suku Bunga',
            'is_revision' => 'Revisi',
            'items' => 'Rincian Rekening',
            'loan_account_id' => 'Rekening Pinjaman',
            'loan_product_id' => 'Produk Pinjaman',
            'marketing_id' => 'Marketing',
            'payout_channel' => 'Channel Pencairan',
            'placement_date' => 'Tanggal Penempatan',
            'reference_no' => 'No Referensi',
            'rollover_type' => 'ARO',
            'saving_account_id' => 'Rekening Pencairan',
            'saving_product_id' => 'Produk Simpanan',
            'source_saving_account_id' => 'Rekening Sumber Dana',
            'target_saving_account_id' => 'Rekening Tujuan',
            'tenor' => 'Tenor',
            'transaction_date' => 'Tanggal Transaksi',
        ][$key] ?? Str::of($key)->replace('_', ' ')->title();
    }

    private function humanValue($key, $value): string
    {
        if (is_array($value)) {
            if ($key === 'entries') {
                $totals = $this->journalEntryTotals($value);
                return "{$totals['count']} baris | Debit {$this->money($totals['debit'])} | Kredit {$this->money($totals['credit'])}";
            }

            if ($key === 'items') {
                $total = collect($value)->sum(fn($item) => (float) ($item['amount'] ?? 0));
                $sample = collect($value)
                    ->take(5)
                    ->map(fn($item) => ($item['account_no'] ?? '-') . ' ' . $this->money($item['amount'] ?? 0))
                    ->implode(', ');
                $more = count($value) > 5 ? ' +' . (count($value) - 5) . ' lainnya' : '';

                return count($value) . " rekening | Total {$this->money($total)}" . ($sample ? " | {$sample}{$more}" : '');
            }

            if ($key === 'bulk_payments') {
                $total = collect($value)->sum(fn($item) => (float) ($item['amount'] ?? 0));
                $sample = collect($value)
                    ->take(3)
                    ->map(fn($item) => ($item['contract_no'] ?? '-') . ' ' . ($item['billing_period'] ?? '-') . ' ' . $this->money($item['amount'] ?? 0))
                    ->implode(', ');
                $more = count($value) > 3 ? ' +' . (count($value) - 3) . ' lainnya' : '';

                return count($value) . " tagihan | Total {$this->money($total)}" . ($sample ? " | {$sample}{$more}" : '');
            }

            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (!filled($value)) {
            return '-';
        }

        if (in_array($key, ['cif_id'], true)) {
            return $this->cifName($value);
        }

        if (in_array($key, ['saving_account_id', 'source_saving_account_id', 'target_saving_account_id', 'from_account_id', 'to_account_id'], true)) {
            return $this->savingAccountNo($value);
        }

        if ($key === 'deposit_account_id') {
            return $this->depositAccountNo($value);
        }

        if ($key === 'deposit_product_id') {
            return $this->depositProductName($value);
        }

        if ($key === 'saving_product_id') {
            return $this->savingProductName($value);
        }

        if ($key === 'deposit_bilyet_id') {
            return $this->depositBilyetName($value);
        }

        if ($key === 'loan_account_id') {
            return $this->loanAccountNo($value);
        }

        if ($key === 'loan_product_id') {
            return $this->loanProductName($value);
        }

        if (in_array($key, ['coa_override_id', 'bank_coa_id', 'cash_coa_id'], true) || str_ends_with($key, '_coa_id')) {
            return $this->coaName($value);
        }

        if ($key === 'branch_id') {
            return $this->branchName($value);
        }

        if ($key === 'created_by') {
            return $this->userName($value);
        }

        if ($key === 'marketing_id') {
            return $this->marketingName($value);
        }

        if (in_array($key, ['deposit_channel', 'payout_channel', 'channel'], true)) {
            return $this->channelLabel($value);
        }

        if ($key === 'rollover_type') {
            return $this->rolloverLabel($value);
        }

        if (str_contains((string) $key, 'amount') || str_contains((string) $key, 'nominal') || in_array($key, ['total_laba', 'shu', 'per_orang'], true)) {
            return $this->money($value);
        }

        if (str_contains((string) $key, 'rate') || str_contains((string) $key, 'percent') || str_contains((string) $key, 'persentase')) {
            return $this->percent($value);
        }

        return (string) $value;
    }

    private function journalEntryRows(array $entries): array
    {
        return collect($entries)
            ->map(function ($entry, $index) {
                $debit = (float) ($entry['debit'] ?? 0);
                $credit = (float) ($entry['credit'] ?? 0);

                return [
                    'no' => $index + 1,
                    'coa' => $this->coaName($entry['coa_id'] ?? null),
                    'debit' => $debit,
                    'credit' => $credit,
                    'debit_display' => $debit > 0 ? $this->money($debit) : '-',
                    'credit_display' => $credit > 0 ? $this->money($credit) : '-',
                ];
            })
            ->values()
            ->all();
    }

    private function journalEntryTotals(array $entries): array
    {
        return [
            'count' => count($entries),
            'debit' => collect($entries)->sum(fn($entry) => (float) ($entry['debit'] ?? 0)),
            'credit' => collect($entries)->sum(fn($entry) => (float) ($entry['credit'] ?? 0)),
        ];
    }

    private function applyAction($request)
    {
        $normalizedModuleKey = str_replace('_', '-', (string) $request->module_key);
        $modelClass = $this->getModelClass($request->module_key);
        $data = $request->data_after;
        if (is_array($data)) {
            $data['created_by'] = $data['created_by'] ?? $request->requested_by;
        }
        $action = $request->action;

        if ($request->module_key === 'shu.distributions') {
            if ($action === 'DISTRIBUTE') {
                app(\App\Services\ShuOperationService::class)->executeDistribution($data);
                return;
            }
        }

        if ($request->module_key === 'savings.distribution') {
            if ($action === 'DISTRIBUTE') {
                if (empty($data['distribution_no'])) {
                    $data['distribution_no'] = \App\Services\SavingDistributionService::generateDistributionNo();
                    $request->update(['data_after' => $data]);
                }

                app(\App\Services\SavingDistributionService::class)->executeDistribution($data);
                return;
            }
        }

        // Custom Logic for Financial/Saving Operations
        if (str_starts_with($request->module_key, 'saving') || str_starts_with($request->module_key, 'deposit') || str_starts_with($request->module_key, 'loan')) {
            if (str_starts_with($request->module_key, 'loan')) {
                $service = app(\App\Services\LoanOperationService::class);
            } else {
                $service = (str_starts_with($request->module_key, 'saving')) 
                    ? app(\App\Services\SavingOperationService::class)
                    : app(\App\Services\DepositOperationService::class);
            }
            
            switch ($action) {
                case 'CREATE':
                    if ($request->module_key === 'deposit_bilyets' || $request->module_key === 'deposit-bilyets') {
                        $service->registerBilyetRange($data);
                        return;
                    }
                    if (str_starts_with($request->module_key, 'saving') || str_starts_with($request->module_key, 'deposit')) {
                        // Ensure this is an Account Opening action (create, placement, open, or keyword 'account')
                        if (str_contains($request->module_key, 'account') || 
                            str_contains($request->module_key, 'create') || 
                            str_contains($request->module_key, 'placement') || 
                            in_array($request->module_key, ['savings', 'deposits'])) {
                            if ($request->module_key === 'deposits.placement') {
                                $this->assertDepositBilyetApprovalPriority($request);
                            }
                            $service->openAccount($data);
                            return;
                        }
                    }
                    // For anything else (like products), fall through to generic CRUD
                    break;
                
                case 'OPEN_ACCOUNT':
                    $service->openAccount($data);
                    return;

                case 'PAY':
                    if ($request->module_key === 'deposits.interest-payment') {
                        $schedule = \App\Models\DepositSchedule::findOrFail($data['deposit_schedule_id'] ?? $request->model_id);
                        $service->disbursePeriodInterest($schedule, true, $data['created_by'] ?? $request->requested_by);
                    }
                    return;
                
                case 'DEPOSIT':
                case 'WITHDRAWAL':
                    $account = \App\Models\SavingAccount::findOrFail($data['saving_account_id']);
                    $service->postTransaction(
                        $account,
                        $action,
                        $data['amount'],
                        $data['description'] ?? '',
                        $data['reference_no'] ?? null,
                        $data['channel'] ?? 'CASH',
                        $data['coa_override_id'] ?? null,
                        $data['created_by'] ?? $request->requested_by
                    );
                    return;

                case 'TRANSFER':
                    $fromAccount = \App\Models\SavingAccount::findOrFail($data['from_account_id']);
                    $toAccount = \App\Models\SavingAccount::findOrFail($data['to_account_id']);
                    $service->postTransfer($fromAccount, $toAccount, $data['amount'], $data['description'] ?? '', null, $data['created_by'] ?? $request->requested_by);
                    return;

                case 'REVERSAL':
                    $originalTrx = \App\Models\SavingTransaction::findOrFail($request->model_id);
                    $service->reverseTransaction($originalTrx, $data['description'] ?? '', $data['created_by'] ?? $request->requested_by);
                    return;
                
                case 'CLOSE':
                case 'DORMANT':
                case 'REACTIVE':
                case 'REACTIVATE':
                    if ($action === 'CLOSE' && str_starts_with($request->module_key, 'deposit')) {
                        $service->closeAccount($data);
                        return;
                    }

                    $account = str_starts_with($request->module_key, 'deposit')
                        ? \App\Models\DepositAccount::findOrFail($request->model_id ?? $data['deposit_account_id'])
                        : \App\Models\SavingAccount::findOrFail($request->model_id);
                    
                    $newStatus = match($action) {
                        'CLOSE' => 'CLOSED',
                        'DORMANT' => 'DORMANT',
                        'REACTIVE', 'REACTIVATE' => 'ACTIVE',
                    };
                    $account->update(['status' => $newStatus, 'closed_at' => $action === 'CLOSE' ? now() : $account->closed_at]);
                    return;

                case 'BLOCK':
                case 'BLOCK_BALANCE':
                    $account = \App\Models\SavingAccount::findOrFail($data['saving_account_id'] ?? $request->model_id);
                    $service->blockBalance($account, $data['amount'], $data['description'] ?? '', $data['reference_no'] ?? null, $data['created_by'] ?? $request->requested_by);
                    return;

                case 'UNBLOCK':
                case 'UNBLOCK_BALANCE':
                    $account = \App\Models\SavingAccount::findOrFail($data['saving_account_id'] ?? $request->model_id);
                    $service->unblockBalance($account, $data['amount'], $data['description'] ?? '', $data['block_id'] ?? null, $data['created_by'] ?? $request->requested_by);
                    return;
                case 'Originate':
                case 'ORIGINATE':
                    if (str_starts_with($request->module_key, 'loans')) {
                        $service->originateLoan($data);
                    }
                    return;

                case 'DISBURSEMENT':
                case 'Disbursement':
                    if (str_starts_with($request->module_key, 'loans')) {
                        $loan = \App\Models\LoanAccount::findOrFail($request->model_id);
                        $service->disburseLoan(
                            $loan,
                            $data['channel'] ?? 'INTERNAL',
                            $data['coa_override_id'] ?? null,
                            $data['created_by'] ?? $request->requested_by
                        );
                    }
                    return;

                case 'REPAYMENT':
                case 'Repayment':
                    if (str_starts_with($request->module_key, 'loans')) {
                        $loan = \App\Models\LoanAccount::findOrFail($request->model_id);
                        $channel = $data['channel'] ?? 'INTERNAL';
                        if ($channel === 'INTERNAL') {
                            $service->processManualRepaymentFromSavings($loan, (float) ($data['amount'] ?? 0), $data['created_by'] ?? $request->requested_by);
                        } else {
                            $service->processRepayment(
                                $loan,
                                (float) ($data['amount'] ?? 0),
                                'REPAYMENT_MANUAL',
                                $channel,
                                $data['coa_override_id'] ?? null,
                                $data['created_by'] ?? $request->requested_by
                            );
                        }
                    }
                    return;

                case 'SETTLEMENT':
                case 'Settlement':
                    if ($request->module_key === 'loans.settlement') {
                        $loan = \App\Models\LoanAccount::findOrFail($request->model_id);
                        $service->processSettlementFromSavings(
                            $loan,
                            (float) ($data['amount'] ?? 0),
                            array_key_exists('interest_amount', $data) ? (float) $data['interest_amount'] : null,
                            array_key_exists('penalty_amount', $data) ? (float) $data['penalty_amount'] : null,
                            $data['created_by'] ?? $request->requested_by
                        );
                    }
                    return;

                case 'REVERSAL':
                case 'Reversal':
                    if ($request->module_key === 'loans.reversal') {
                        $loan = \App\Models\LoanAccount::findOrFail($request->model_id);
                        $disbTx = \App\Models\LoanTransaction::where('loan_account_id', $loan->id)
                            ->where('transaction_type', 'DISBURSEMENT')
                            ->firstOrFail();
                        $service->reverseDisbursement($disbTx);
                    }
                    return;

                case 'UPDATE':
                    if ($request->module_key === 'loans.edit') {
                        $loan = \App\Models\LoanAccount::findOrFail($request->model_id);
                        $service->updateUndisbursedLoan($loan, $data);
                        return;
                    }
                    break;

            }
        }

        // Custom Logic for Assets
        if (in_array($request->module_key, ['assets', 'assets.create'], true) && $action === 'CREATE') {
            $creditCoaId = $data['asset_purchase_credit_coa_id'] ?? null;
            unset($data['asset_purchase_channel'], $data['asset_purchase_credit_coa_id']);

            $data['created_by'] = $data['created_by'] ?? $request->requested_by;
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
            $data['status'] = 'ACTIVE';
            $instance = \App\Models\Asset::create($data);
            
            // Posting jurnal pembelian aset jika baru disetujui
            app(\App\Services\AssetOperationService::class)->postAssetPurchaseJournal($instance, $creditCoaId ? (int) $creditCoaId : null);
            
            $instance->logActivity('APPROVE_CREATE', "Penyetujuan pendaftaran aset: {$instance->asset_code}", $instance);
            return;
        }

        if ($normalizedModuleKey === 'asset-rentals.index') {
            if ($action === 'CREATE') {
                DB::transaction(function () use ($data, $request) {
                    $prefix = 'KSW-' . now()->format('Ym') . '-';
                    $count = \App\Models\AssetRental::where('contract_no', 'like', $prefix . '%')->count() + 1;
                    $contractNo = $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);

                    $rental = \App\Models\AssetRental::create([
                        'contract_no' => $contractNo,
                        'asset_id' => $data['asset_id'],
                        'rekanan_id' => $data['rekanan_id'],
                        'branch_id' => $data['branch_id'],
                        'rental_start_date' => $data['rental_start_date'],
                        'rental_end_date' => $data['rental_end_date'],
                        'monthly_rate' => $data['monthly_rate'],
                        'payment_due_day' => $data['payment_due_day'],
                        'status' => 'ACTIVE',
                        'notes' => $data['notes'] ?? null,
                        'created_by' => $data['created_by'] ?? $request->requested_by,
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);

                    \App\Models\Asset::find($data['asset_id'])?->update([
                        'status' => 'RENTED',
                        'updated_by' => auth()->id(),
                    ]);

                    $start = \Carbon\Carbon::parse($rental->rental_start_date)->startOfMonth()->addMonth();
                    $end = \Carbon\Carbon::parse($rental->rental_end_date)->startOfMonth();
                    $current = $start->copy();
                    while ($current->lte($end)) {
                        \App\Models\AssetRentalBilling::create([
                            'asset_rental_id' => $rental->id,
                            'billing_period' => $current->format('Y-m'),
                            'billing_date' => now()->toDateString(),
                            'due_date' => $current->copy()->day($rental->payment_due_day)->toDateString(),
                            'amount' => $rental->monthly_rate,
                            'status' => 'UNPAID',
                            'created_by' => $data['created_by'] ?? $request->requested_by,
                        ]);
                        $current->addMonth();
                    }
                });
                return;
            }

            if ($action === 'UPDATE') {
                if (!empty($data['bulk_payments'])) {
                    app(\App\Services\AssetOperationService::class)->recognizeRentalRevenueBulk(
                        $data,
                        $data['created_by'] ?? $request->requested_by
                    );
                    return;
                }

                DB::transaction(function () use ($data, $request) {
                    $billing = \App\Models\AssetRentalBilling::with('rental.asset', 'rental.rekanan')->findOrFail($data['billing_id']);
                    $journal = app(\App\Services\AssetOperationService::class)->recognizeRentalRevenue(
                        $billing,
                        (int) ($data['payment_debit_coa_id'] ?? 0),
                        (int) ($data['payment_credit_coa_id'] ?? 0),
                        $data['created_by'] ?? $request->requested_by
                    );

                    $billing->update([
                        'status' => 'PAID',
                        'paid_at' => now(),
                        'payment_reference' => ($data['payment_reference'] ?? '') ?: $journal->reference_no,
                    ]);
                });
                return;
            }

            if ($action === 'DELETE') {
                DB::transaction(function () use ($request) {
                    $rental = \App\Models\AssetRental::findOrFail($request->model_id);
                    $rental->update(['status' => 'TERMINATED', 'updated_by' => auth()->id()]);
                    \App\Models\Asset::find($rental->asset_id)?->update(['status' => 'ACTIVE', 'updated_by' => auth()->id()]);
                    $rental->billings()->where('status', 'UNPAID')->update(['status' => 'OVERDUE']);
                });
                return;
            }
        }

        if (!$modelClass) throw new \Exception("Model untuk modul {$request->module_key} tidak ditemukan.");

        // Existing CRUD Logic
        $role = $data['role'] ?? null;
        unset($data['role']);
        if ($action === 'CREATE') {
            $data['created_by'] = $data['created_by'] ?? $request->requested_by;
        }
        $journalEntries = $request->module_key === 'journals' && $action === 'CREATE'
            ? ($data['entries'] ?? [])
            : [];
        if ($request->module_key === 'journals' && $action === 'CREATE') {
            $data['journal_type'] = $data['journal_type'] ?? \App\Models\Journal::TYPE_MANUAL;

            $coaIds = collect($journalEntries)->pluck('coa_id')->filter()->all();
            if (\App\Models\Coa::whereIn('id', $coaIds)->where('coa_code', '314000')->exists()) {
                throw new \Exception('COA SHU / LABA TAHUN BERJALAN (314000) dihitung otomatis dan tidak bisa dijurnal.');
            }
        }

        if ($request->module_key === 'cifs.create' && $action === 'CREATE') {
            $data['cif_no'] = $this->nextCifNo((int) ($data['branch_id'] ?? 0));
        }

        $data['updated_by'] = auth()->id();
        $data['approved_by'] = auth()->id();
        $data['approved_at'] = now();
        $data = $this->onlyModelColumns($modelClass, $data);

        switch ($action) {
            case 'CREATE':
                if ($request->module_key === 'journals') {
                    if (empty($journalEntries)) {
                        throw new \Exception('Rincian jurnal kosong. Buat ulang pengajuan jurnal.');
                    }

                    $data['status'] = 'APPROVED';
                    $instance = new $modelClass;
                    $instance->forceFill($data)->save();
                    foreach ($journalEntries as $entry) {
                        $instance->entries()->create($entry);
                    }
                    app(\App\Services\CoaMovementService::class)->syncForJournal($instance);
                } else {
                    $instance = new $modelClass;
                    $instance->forceFill($data)->save();
                    if ($request->module_key === 'coas' && $instance->parent_id) {
                        \App\Models\Coa::whereKey($instance->parent_id)->update(['is_leaf' => false]);
                    }
                    if ($request->module_key === 'users' && $role) {
                        $instance->assignRole($role);
                    }
                }
                break;
            case 'UPDATE':
            case 'MUTATION':
            case 'BLOCK':
            case 'INACTIVE':
            case 'REACTIVE':
                $instance = $modelClass::where($this->getPrimaryKey($request->module_key), $request->model_id)->first();
                if ($instance) {
                    $instance->forceFill($data)->save();
                    if ($request->module_key === 'coas' && $instance->parent_id) {
                        \App\Models\Coa::whereKey($instance->parent_id)->update(['is_leaf' => false]);
                    }
                    if ($request->module_key === 'users' && $role) {
                        $instance->syncRoles($role);
                    }
                }
                break;
            case 'DELETE':
                $instance = $modelClass::where($this->getPrimaryKey($request->module_key), $request->model_id)->first();
                if ($instance) $instance->delete();
                break;
        }
    }

    private function nextCifNo(int $branchId): string
    {
        $branch = \App\Models\Branch::find($branchId);
        $branchCode = $branch?->branch_code ?? 'XX';

        $count = \App\Models\Cif::where('branch_id', $branchId)->count() + 1;
        do {
            $code = $branchCode . str_pad($count, 7, '0', STR_PAD_LEFT);
            $count++;
        } while (\App\Models\Cif::whereRaw('BINARY cif_no = BINARY ?', [$code])->exists());

        return $code;
    }

    private function onlyModelColumns(string $modelClass, array $data): array
    {
        $model = new $modelClass;
        $table = $model->getTable();

        if (!Schema::hasTable($table)) {
            $fillable = $model->getFillable();

            return $fillable === []
                ? $data
                : array_intersect_key($data, array_flip($fillable));
        }

        return array_intersect_key($data, array_flip(Schema::getColumnListing($table)));
    }

    private function assertDepositBilyetApprovalPriority(ApprovalRequest $request): void
    {
        $bilyetId = (int) ($request->data_after['deposit_bilyet_id'] ?? 0);
        if (!$bilyetId) {
            return;
        }

        $olderRequest = ApprovalRequest::where('module_key', 'deposits.placement')
            ->where('action', 'CREATE')
            ->where('status', 'PENDING')
            ->where('id', '<', $request->id)
            ->orderBy('id')
            ->get()
            ->first(fn (ApprovalRequest $pending) => (int) ($pending->data_after['deposit_bilyet_id'] ?? 0) === $bilyetId);

        if ($olderRequest) {
            $bilyet = \App\Models\DepositBilyet::withTrashed()->find($bilyetId);
            $label = $bilyet ? ($bilyet->kode_bilyet ?: $bilyet->bilyet_number) : "ID {$bilyetId}";

            throw new \Exception("Bilyet {$label} sudah dikunci oleh permohonan penempatan #{$olderRequest->id}. Approve permohonan yang lebih lama dulu atau tolak salah satunya.");
        }
    }

    private function getModelClass($key)
    {
        $key = str_replace('_', '-', $key);
        if ($key === 'savings-distribution' || $key === 'savings.distribution') return \App\Models\SavingDistribution::class;
        if (str_starts_with($key, 'cifs')) return \App\Models\Cif::class;
        if (str_starts_with($key, 'savings')) return \App\Models\SavingAccount::class;
        if (str_starts_with($key, 'deposits')) return \App\Models\DepositAccount::class;
        if (str_starts_with($key, 'deposit-bilyets') || $key === 'deposit_bilyets') return \App\Models\DepositBilyet::class;
        if (str_starts_with($key, 'loans')) return \App\Models\LoanAccount::class;

        $map = [
            'provinces' => \App\Models\Province::class,
            'cities' => \App\Models\City::class,
            'districts' => \App\Models\District::class,
            'subdistricts' => \App\Models\Subdistrict::class,
            'users' => \App\Models\User::class,
            'roles' => \Spatie\Permission\Models\Role::class,
            'branches' => \App\Models\Branch::class,
            'companies' => \App\Models\Company::class,
            'menus' => \App\Models\Menu::class,
            'marketing-masters' => \App\Models\MarketingMaster::class,
            'saving_products' => \App\Models\SavingProduct::class,
            'saving-products' => \App\Models\SavingProduct::class,
            'deposit_products' => \App\Models\DepositProduct::class,
            'deposit-products' => \App\Models\DepositProduct::class,
            'loan_products' => \App\Models\LoanProduct::class,
            'loan-products' => \App\Models\LoanProduct::class,
            'coas' => \App\Models\Coa::class,
            'journals' => \App\Models\Journal::class,
            'ledger' => \App\Models\Journal::class,
            'trial-balance' => \App\Models\Journal::class,
            'asset-rentals.index' => \App\Models\AssetRental::class,
            'assets.categories' => \App\Models\AssetCategory::class,
            'rekanan.index' => \App\Models\Rekanan::class,
            'audit-logs' => \App\Models\ActivityLog::class,
            'reports.index' => \App\Models\ActivityLog::class,
            'approvals.settings' => \App\Models\ApprovalConfig::class,
            'approvals.inbox' => \App\Models\ApprovalRequest::class,
            // Fallback globals just in case
            'saving_accounts' => \App\Models\SavingAccount::class,
            'saving_transactions' => \App\Models\SavingTransaction::class,
            'deposit_accounts' => \App\Models\DepositAccount::class,
            'deposit_transactions' => \App\Models\DepositTransaction::class,
            'assets' => \App\Models\Asset::class,
        ];
        if (str_starts_with($key, 'assets')) return \App\Models\Asset::class;
        return $map[$key] ?? null;
    }

    private function getPrimaryKey($key)
    {
        $key = str_replace('_', '-', $key);
        $map = [
            'provinces' => 'id',
            'cities' => 'id',
            'districts' => 'id',
            'subdistricts' => 'id',
            'users' => 'id',
            'roles' => 'id',
            'branches' => 'id',
            'companies' => 'id',
            'menus' => 'id',
            'marketing-masters' => 'id',
            'cifs' => 'id',
            'saving_products' => 'id',
            'saving-products' => 'id',
            'deposit_products' => 'id',
            'deposit-products' => 'id',
            'loan_products' => 'id',
            'loan-products' => 'id',
            'coas' => 'id',
            'journals' => 'id',
            'ledger' => 'id',
            'trial-balance' => 'id',
            'asset-rentals.index' => 'id',
            'assets.categories' => 'id',
            'rekanan.index' => 'id',
            'audit-logs' => 'id',
            'reports.index' => 'id',
            'approvals.settings' => 'id',
            'approvals.inbox' => 'id',
            // Globals
            'saving_accounts' => 'account_no',
            'deposit_accounts' => 'account_no',
            'loans' => 'account_no',
            'assets' => 'id',
        ];
        if (str_starts_with($key, 'cifs')) return 'id';
        if (str_starts_with($key, 'savings')) return 'account_no';
        if (str_starts_with($key, 'deposits')) return 'account_no';
        if (str_starts_with($key, 'deposit-bilyets') || $key === 'deposit_bilyets') return 'id';
        if (str_starts_with($key, 'loans')) return 'account_no';
        if (str_starts_with($key, 'assets')) return 'id';

        return $map[$key] ?? 'id';
    }
}
