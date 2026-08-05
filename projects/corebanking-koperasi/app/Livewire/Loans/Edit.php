<?php

namespace App\Livewire\Loans;

use App\Models\ApprovalRequest;
use App\Models\LoanAccount;
use App\Models\LoanProduct;
use App\Models\SavingAccount;
use App\Services\LoanOperationService;
use Illuminate\Support\Facades\DB;

class Edit extends Origination
{
    public $approval_request_id;
    public $loan_id;
    public $selectedType = null;
    public $searchLoan = '';
    public $selectedLoan = null;

    public function mount($approvalRequest = null)
    {
        $this->disbursement_date = now()->format('Y-m-d');
        $this->branch_id = auth()->user()->branch_id ?? null;
        $this->logActivity('NAVIGATE', 'Perubahan Pengajuan Pinjaman');

        if ($approvalRequest) {
            $this->selectLoan('request', (int) $approvalRequest);
        }
    }

    public function selectLoan($type, $id): void
    {
        if ($type === 'request') {
            $request = $this->editableRequestQuery()->whereKey($id)->first();
            abort_unless($request, 403, 'Pengajuan pinjaman tidak ditemukan atau sudah diproses.');

            $this->approval_request_id = $request->id;
            $this->loan_id = null;
            $this->selectedType = 'request';
            $this->selectedLoan = $this->requestResult($request);
            $this->fillFromPayload($request->data_after ?? []);
        } else {
            $loan = $this->editableLoanQuery()->whereKey($id)->first();
            abort_unless($loan, 403, 'Pinjaman sudah approved dan/atau sudah dicairkan.');

            $this->approval_request_id = null;
            $this->loan_id = $loan->id;
            $this->selectedType = 'loan';
            $this->selectedLoan = $this->loanResult($loan);
            $this->fillFromLoan($loan);
        }

        $this->searchLoan = '';
    }

    public function clearSelection(): void
    {
        $this->resetExcept('searchLoan');
        $this->mount();
    }

    public function submit(LoanOperationService $service)
    {
        $this->validate();
        $after = $this->loanPayload();

        if ($this->selectedType === 'request') {
            $request = $this->editableRequestQuery()->findOrFail($this->approval_request_id);
            $before = $request->data_after ?? [];
            $request->update(['data_after' => $after]);

            $this->logActivity('UPDATE_LOAN_REQUEST', "Mengubah pengajuan pinjaman pending #{$request->id}", $request, [
                'before' => $before,
                'after' => $after,
            ]);

            session()->flash('success', 'Perubahan pengajuan pinjaman berhasil disimpan di antrean persetujuan.');
            return redirect()->route('approvals.inbox');
        }

        $loan = $this->editableLoanQuery()->findOrFail($this->loan_id);
        $before = $loan->toArray();
        $status = $this->interceptAction('loans.edit', 'UPDATE', $after, $loan->id, $before);

        $this->logActivity('UPDATE_LOAN_REQUEST', "Mengajukan perubahan pinjaman {$loan->account_no}", $loan, [
            'before' => $before,
            'after' => $after,
        ]);

        if ($status === 'PENDING') {
            session()->flash('success', 'Perubahan pinjaman berhasil dikirim ke antrean persetujuan.');
            return redirect()->route('approvals.inbox');
        }

        DB::transaction(fn () => $service->updateUndisbursedLoan($loan, $after));

        session()->flash('success', 'Perubahan pinjaman berhasil disimpan.');
        return redirect()->route('loans.inquiry');
    }

    public function render()
    {
        $term = mb_strtolower(trim($this->searchLoan));
        $allResults = $this->editableResults();
        $loanResults = $this->selectedLoan
            ? collect()
            : ($term === ''
                ? $allResults->take(10)->values()
                : $allResults->filter(fn ($item) => str_contains(mb_strtolower($item['search']), $term))->take(10)->values());

        return view('livewire.loans.origination', [
            'isEditMode' => true,
            'loanResults' => $loanResults,
            'pendingLoanCount' => $allResults->count(),
            'cifResults' => [],
            'loanProducts' => LoanProduct::where('is_active', true)->get(),
            'insuranceProducts' => \App\Models\InsuranceProduct::with('provider')->where('is_active', true)->get(),
            'branches' => \App\Models\Branch::where('is_active', true)->get(),
            'marketings' => \App\Models\MarketingMaster::orderBy('name')->get(),
        ]);
    }

    private function editableResults()
    {
        return $this->editableRequestQuery()->latest()->limit(100)->get()
            ->map(fn ($request) => $this->requestResult($request))
            ->concat($this->editableLoanQuery()->latest()->limit(100)->get()->map(fn ($loan) => $this->loanResult($loan)))
            ->sortByDesc('sort_at')
            ->values();
    }

    private function editableRequestQuery()
    {
        return ApprovalRequest::with('requester')
            ->where('module_key', 'loans.origination')
            ->whereIn('action', ['Originate', 'ORIGINATE'])
            ->where('status', 'PENDING')
            ->where('requested_by', auth()->id());
    }

    private function editableLoanQuery()
    {
        return LoanAccount::with(['cif', 'product', 'savingAccount'])
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->where('created_by', auth()->id())
            ->whereDoesntHave('transactions', fn ($q) => $q->where('transaction_type', 'DISBURSEMENT'));
    }

    private function requestResult(ApprovalRequest $request): array
    {
        $data = $request->data_after ?? [];
        $title = '#' . $request->id . ' - ' . $this->payloadCifLabel($data);
        $subtitle = implode(' | ', array_filter([
            'Pengajuan pending',
            $this->payloadProductLabel($data),
            'Rp ' . number_format((float) ($data['principal_amount'] ?? 0), 2, ',', '.'),
            ($data['tenor'] ?? '-') . ' bulan',
            $data['disbursement_date'] ?? null,
        ]));

        return [
            'type' => 'request',
            'id' => $request->id,
            'title' => $title,
            'subtitle' => $subtitle,
            'status' => 'MENUNGGU APPROVAL',
            'sort_at' => $request->created_at?->timestamp ?? 0,
            'search' => implode(' ', [$title, $subtitle, $request->requester?->name]),
        ];
    }

    private function loanResult(LoanAccount $loan): array
    {
        $title = $loan->account_no . ' - ' . ($loan->cif?->name ?? '-');
        $subtitle = implode(' | ', array_filter([
            'Belum dicairkan',
            $loan->pk_number,
            $loan->product?->name,
            'Rp ' . number_format((float) $loan->principal_amount, 2, ',', '.'),
            $loan->tenor . ' bulan',
        ]));

        return [
            'type' => 'loan',
            'id' => $loan->id,
            'title' => $title,
            'subtitle' => $subtitle,
            'status' => $loan->status,
            'sort_at' => $loan->created_at?->timestamp ?? 0,
            'search' => implode(' ', [$title, $subtitle, $loan->cif?->cif_no]),
        ];
    }

    private function fillFromPayload(array $data): void
    {
        foreach ([
            'cif_id', 'saving_account_id', 'loan_product_id', 'insurance_product_id',
            'insurance_rate', 'branch_id', 'marketing_id', 'tenor', 'tenor_type',
            'interest_rate', 'calculation_method', 'reason', 'applicant_purpose',
            'applicant_occupation', 'applicant_company_name', 'applicant_company_address',
            'collateral_type', 'collateral_description', 'collateral_certificate_no',
            'collateral_address', 'guarantor_name', 'guarantor_nik', 'guarantor_phone',
            'guarantor_address', 'guarantor_relation',
        ] as $field) {
            $this->{$field} = $data[$field] ?? $this->{$field} ?? null;
        }

        foreach ([
            'principal_amount', 'applicant_monthly_income', 'applicant_monthly_expense',
            'applicant_other_income', 'collateral_value', 'provision_fee', 'admin_fee',
            'insurance_fee', 'flagging_fee', 'stamp_duty_fee', 'prepaid_installment_amount',
            'blocked_savings_amount', 'diskonto_upfront_amount',
        ] as $field) {
            $this->{$field === 'prepaid_installment_amount' ? 'prepaid_amount' : ($field === 'blocked_savings_amount' ? 'blocked_amount' : $field)}
                = (float) ($data[$field] ?? 0);
        }

        $this->prepaid_count = (int) ($data['prepaid_installment_count'] ?? 0);
        $this->blocked_count = (int) ($data['blocked_savings_count'] ?? 0);
        $this->analyst_note = $data['analyst_notes'] ?? null;
        $this->analyst_recommendation = $data['analyst_recommendation'] ?? null;
        $this->disbursement_date = $data['disbursement_date'] ?? now()->format('Y-m-d');
        $this->is_using_insurance = filled($this->insurance_product_id);
        $this->is_diskonto_product = (bool) ($data['is_diskonto'] ?? false);
        $this->hydrateCifState();

        $principal = max((float) $this->principal_amount, 1);
        $this->provision_rate = round(((float) $this->provision_fee / $principal) * 100, 4);
        $this->admin_rate = round(((float) $this->admin_fee / $principal) * 100, 4);
    }

    private function fillFromLoan(LoanAccount $loan): void
    {
        $this->fillFromPayload($loan->toArray());
        $this->selectedCif = $loan->cif;
        $this->availableSavingAccounts = SavingAccount::where('cif_id', $loan->cif_id)->where('status', 'ACTIVE')->get();
    }

    private function hydrateCifState(): void
    {
        $this->selectedCif = $this->cif_id ? \App\Models\Cif::find($this->cif_id) : null;
        $this->availableSavingAccounts = $this->cif_id
            ? SavingAccount::where('cif_id', $this->cif_id)->where('status', 'ACTIVE')->get()
            : collect();
    }

    private function payloadCifLabel(array $data): string
    {
        $cif = isset($data['cif_id']) ? \App\Models\Cif::find($data['cif_id']) : null;
        return $cif ? "{$cif->cif_no} - {$cif->name}" : 'CIF belum dipilih';
    }

    private function payloadProductLabel(array $data): string
    {
        $product = isset($data['loan_product_id']) ? LoanProduct::find($data['loan_product_id']) : null;
        return $product ? "{$product->product_code} - {$product->name}" : 'Produk belum dipilih';
    }
}
