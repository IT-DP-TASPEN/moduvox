<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\DepositAccount;
use App\Models\LoanAccount;
use App\Models\MobileAccess;
use App\Models\SavingAccount;
use App\Models\SavingTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    // ─────────────────────────────────────────────────
    //  GET /api/mobile/mutasi?account_no=...
    // ─────────────────────────────────────────────────
    /**
     * Endpoint kompatibilitas untuk aplikasi mobile lama.
     */
    public function mutasi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_no' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter account_no wajib diisi.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        return $this->savingTransactions($request, (string) $request->query('account_no'));
    }

    // ─────────────────────────────────────────────────
    //  GET /api/mobile/savings
    // ─────────────────────────────────────────────────
    /**
     * Daftar semua rekening tabungan nasabah.
     */
    public function savings(Request $request): JsonResponse
    {
        /** @var \App\Models\MobileAccess $mobileAccess */
        $mobileAccess = $request->attributes->get('mobile_access');

        $accounts = SavingAccount::with(['product:id,name,product_code,interest_rate'])
            ->where('cif_id', $mobileAccess->cif_id)
            ->whereIn('status', ['ACTIVE', 'DORMANT'])
            ->get()
            ->map(fn ($acc) => [
                'account_no'       => $acc->account_no,
                'product_name'     => $acc->product?->name,
                'product_code'     => $acc->product?->product_code,
                'balance'          => (float) $acc->balance,
                'blocked_balance'  => (float) $acc->blocked_balance,
                'effective_balance' => (float) ($acc->balance - $acc->blocked_balance),
                'interest_rate'    => (float) ($acc->product?->interest_rate ?? 0),
                'status'           => $acc->status,
                'opened_at'        => $acc->opened_at?->format('Y-m-d'),
            ]);

        return response()->json([
            'success' => true,
            'data'    => $accounts->values(),
        ]);
    }

    // ─────────────────────────────────────────────────
    //  GET /api/mobile/savings/{account_no}/transactions
    // ─────────────────────────────────────────────────
    /**
     * Mutasi rekening tabungan tertentu (20 transaksi terakhir).
     */
    public function savingTransactions(Request $request, string $accountNo): JsonResponse
    {
        /** @var \App\Models\MobileAccess $mobileAccess */
        $mobileAccess = $request->attributes->get('mobile_access');

        $account = SavingAccount::where('account_no', $accountNo)
            ->where('cif_id', $mobileAccess->cif_id)
            ->first();

        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        $perPage = min((int) $request->query('per_page', 20), 50);

        $transactions = SavingTransaction::where('saving_account_id', $account->id)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        $data = collect($transactions->items())->map(fn ($trx) => [
            'transaction_no'   => $trx->transaction_no,
            'transaction_date' => $trx->transaction_date?->format('Y-m-d'),
            'type'             => $trx->type,
            'amount'           => (float) $trx->amount,
            'balance_after'    => (float) ($trx->balance_after ?? 0),
            'description'      => $trx->description,
            'reference_no'     => $trx->reference_no,
        ]);

        return response()->json([
            'success'     => true,
            'account_no'  => $account->account_no,
            'balance'     => (float) $account->balance,
            'data'        => $data->values(),
            'pagination'  => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'per_page'     => $transactions->perPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────
    //  GET /api/mobile/loans
    // ─────────────────────────────────────────────────
    /**
     * Daftar semua kredit aktif nasabah beserta jadwal angsuran.
     */
    public function loans(Request $request): JsonResponse
    {
        /** @var \App\Models\MobileAccess $mobileAccess */
        $mobileAccess = $request->attributes->get('mobile_access');

        $accounts = LoanAccount::with([
            'product:id,name,product_code',
            'schedules' => fn ($q) => $q->orderBy('installment_number'),
        ])
            ->where('cif_id', $mobileAccess->cif_id)
            ->whereIn('status', ['ACTIVE', 'DISBURSED'])
            ->get()
            ->map(function ($acc) {
                $pendingSchedules = $acc->schedules->whereIn('status', ['PENDING', 'OVERDUE']);
                $nextSchedule     = $pendingSchedules->sortBy('due_date')->first();
                $paidCount        = $acc->schedules->where('status', 'PAID')->count();

                return [
                    'account_no'              => $acc->account_no,
                    'pk_number'               => $acc->pk_number,
                    'product_name'            => $acc->product?->name,
                    'product_code'            => $acc->product?->product_code,
                    'principal_amount'        => (float) $acc->principal_amount,
                    'outstanding_principal'   => (float) $acc->outstanding_principal,
                    'outstanding_interest'    => (float) $acc->outstanding_interest,
                    'outstanding_penalty'     => (float) $acc->outstanding_penalty,
                    'outstanding_total'       => (float) ($acc->outstanding_principal + $acc->outstanding_interest + $acc->outstanding_penalty),
                    'tenor'                   => $acc->tenor,
                    'tenor_type'              => $acc->tenor_type,
                    'installment_paid'        => $paidCount,
                    'installment_remaining'   => $acc->tenor - $paidCount,
                    'disbursement_date'       => $acc->disbursement_date?->format('Y-m-d'),
                    'status'                  => $acc->status,
                    'next_due_date'           => $nextSchedule?->due_date?->format('Y-m-d'),
                    'next_installment_number' => $nextSchedule?->installment_number,
                    'next_installment_amount' => $nextSchedule
                        ? (float) ($nextSchedule->principal_amount + $nextSchedule->interest_amount + $nextSchedule->penalty_amount)
                        : null,
                    'is_overdue'              => $nextSchedule?->status === 'OVERDUE',
                    // Daftar lengkap jadwal angsuran
                    'schedules'               => $acc->schedules->map(fn ($s) => [
                        'installment_number' => $s->installment_number,
                        'due_date'           => $s->due_date?->format('Y-m-d'),
                        'principal_amount'   => (float) $s->principal_amount,
                        'interest_amount'    => (float) $s->interest_amount,
                        'penalty_amount'     => (float) $s->penalty_amount,
                        'total_amount'       => (float) ($s->principal_amount + $s->interest_amount + $s->penalty_amount),
                        'principal_paid'     => (float) $s->principal_paid,
                        'interest_paid'      => (float) $s->interest_paid,
                        'status'             => $s->status,
                    ])->values(),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $accounts->values(),
        ]);
    }

    // ─────────────────────────────────────────────────
    //  GET /api/mobile/deposits
    // ─────────────────────────────────────────────────
    /**
     * Daftar semua deposito aktif nasabah beserta jadwal bunga.
     */
    public function deposits(Request $request): JsonResponse
    {
        /** @var \App\Models\MobileAccess $mobileAccess */
        $mobileAccess = $request->attributes->get('mobile_access');

        $accounts = DepositAccount::with([
            'product:id,name,product_code',
            'schedules' => fn ($q) => $q->orderBy('schedule_date'),
        ])
            ->where('cif_id', $mobileAccess->cif_id)
            ->where('status', 'ACTIVE')
            ->get()
            ->map(function ($acc) {
                $pendingSchedules = $acc->schedules->where('status', 'PENDING');
                $nextSchedule     = $pendingSchedules->sortBy('schedule_date')->first();

                return [
                    'account_no'          => $acc->account_no,
                    'product_name'        => $acc->product?->name,
                    'product_code'        => $acc->product?->product_code,
                    'amount'              => (float) $acc->amount,
                    'interest_rate'       => (float) $acc->interest_rate,
                    'tenor'               => $acc->tenor,
                    'placement_date'      => $acc->placement_date?->format('Y-m-d'),
                    'maturity_date'       => $acc->maturity_date?->format('Y-m-d'),
                    'rollover_type'       => $acc->rollover_type,
                    'status'              => $acc->status,
                    'next_due_date'       => $nextSchedule?->schedule_date?->format('Y-m-d'),
                    'next_gross_interest' => $nextSchedule ? (float) $nextSchedule->gross_interest : null,
                    'next_net_interest'   => $nextSchedule ? (float) $nextSchedule->net_interest : null,
                    'next_tax_amount'     => $nextSchedule ? (float) $nextSchedule->tax_amount : null,
                    // Semua jadwal bunga
                    'schedules'           => $acc->schedules->map(fn ($s) => [
                        'month_index'    => $s->month_index,
                        'schedule_date'  => $s->schedule_date?->format('Y-m-d'),
                        'gross_interest' => (float) $s->gross_interest,
                        'tax_amount'     => (float) $s->tax_amount,
                        'net_interest'   => (float) $s->net_interest,
                        'status'         => $s->status,
                        'payment_date'   => $s->payment_date?->format('Y-m-d'),
                    ])->values(),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $accounts->values(),
        ]);
    }
}
