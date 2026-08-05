<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Cif;
use App\Models\MobileAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // ─────────────────────────────────────────────────
    //  GET /api/mobile/dashboard
    // ─────────────────────────────────────────────────
    /**
     * Dashboard utama nasabah:
     * ringkasan tabungan, kredit, dan deposito berdasarkan CIF.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\MobileAccess $mobileAccess */
        $mobileAccess = $request->attributes->get('mobile_access');

        $cif = Cif::with([
            // Tabungan AKTIF beserta produk
            'savingAccounts' => function ($q) {
                $q->whereIn('status', ['ACTIVE', 'DORMANT'])
                  ->with('product:id,name,product_code');
            },

            // Kredit ACTIVE/DISBURSED beserta angsuran yang belum lunas
            'loanAccounts' => function ($q) {
                $q->whereIn('status', ['ACTIVE', 'DISBURSED'])
                  ->with([
                      'product:id,name,product_code',
                      // hanya ambil schedule yang belum dibayar, terdekat dulu
                      'schedules' => function ($sq) {
                          $sq->whereIn('status', ['PENDING', 'OVERDUE'])
                             ->orderBy('due_date')
                             ->limit(1);
                      },
                  ]);
            },

            // Deposito ACTIVE beserta jadwal bunga yang belum dibayar
            'depositAccounts' => function ($q) {
                $q->where('status', 'ACTIVE')
                  ->with([
                      'product:id,name,product_code',
                      // jadwal bunga/pokok berikutnya
                      'schedules' => function ($sq) {
                          $sq->where('status', 'PENDING')
                             ->orderBy('schedule_date')
                             ->limit(1);
                      },
                  ]);
            },
        ])->findOrFail($mobileAccess->cif_id);

        return response()->json([
            'success' => true,
            'data'    => [
                'nasabah'   => $this->formatNasabah($cif),
                'tabungan'  => $this->formatTabungan($cif->savingAccounts),
                'kredit'    => $this->formatKredit($cif->loanAccounts),
                'deposito'  => $this->formatDeposito($cif->depositAccounts),
                'summary'   => $this->buildSummary($cif),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────
    //  GET /api/mobile/profile
    // ─────────────────────────────────────────────────
    /**
     * Data profil nasabah dari CIF.
     */
    public function profile(Request $request): JsonResponse
    {
        /** @var \App\Models\MobileAccess $mobileAccess */
        $mobileAccess = $request->attributes->get('mobile_access');

        $cif = Cif::with(['province', 'city', 'district', 'branch'])
            ->findOrFail($mobileAccess->cif_id);

        return response()->json([
            'success' => true,
            'data'    => [
                'cif_no'          => $cif->cif_no,
                'name'            => $cif->name,
                'nik'             => $cif->nik,
                'birth_date'      => $cif->birth_date?->format('Y-m-d'),
                'gender'          => $cif->gender,
                'marital_status'  => $cif->marital_status,
                'phone'           => $cif->phone,
                'email'           => $cif->email,
                'address'         => $cif->address,
                'city'            => $cif->city?->name,
                'province'        => $cif->province?->name,
                'occupation'      => $cif->occupation,
                'branch'          => $cif->branch?->name,
                // Info akun mobile
                'username'        => $mobileAccess->username,
                'last_login_at'   => $mobileAccess->last_login_at?->toIso8601String(),
                'wrong_pin_count' => $mobileAccess->wrong_pin_count,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────
    //  Private Formatters
    // ─────────────────────────────────────────────────

    private function formatNasabah(Cif $cif): array
    {
        return [
            'cif_no' => $cif->cif_no,
            'name'   => $cif->name,
            'phone'  => $cif->phone,
            'email'  => $cif->email,
        ];
    }

    private function formatTabungan(\Illuminate\Database\Eloquent\Collection $accounts): array
    {
        return $accounts->map(fn ($acc) => [
            'account_no'     => $acc->account_no,
            'product_name'   => $acc->product?->name,
            'product_code'   => $acc->product?->product_code,
            'balance'        => (float) $acc->balance,
            'blocked_balance' => (float) $acc->blocked_balance,
            'effective_balance' => (float) ($acc->balance - $acc->blocked_balance),
            'status'         => $acc->status,
            'opened_at'      => $acc->opened_at?->format('Y-m-d'),
        ])->values()->all();
    }

    private function formatKredit(\Illuminate\Database\Eloquent\Collection $accounts): array
    {
        return $accounts->map(function ($acc) {
            $nextSchedule = $acc->schedules->first();

            return [
                'account_no'            => $acc->account_no,
                'product_name'          => $acc->product?->name,
                'product_code'          => $acc->product?->product_code,
                'principal_amount'      => (float) $acc->principal_amount,
                'outstanding_principal' => (float) $acc->outstanding_principal,
                'outstanding_interest'  => (float) $acc->outstanding_interest,
                'outstanding_penalty'   => (float) $acc->outstanding_penalty,
                'outstanding_total'     => (float) ($acc->outstanding_principal + $acc->outstanding_interest + $acc->outstanding_penalty),
                'tenor'                 => $acc->tenor,
                'tenor_type'            => $acc->tenor_type,
                'disbursement_date'     => $acc->disbursement_date?->format('Y-m-d'),
                'status'                => $acc->status,
                // Angsuran terdekat
                'next_due_date'         => $nextSchedule?->due_date?->format('Y-m-d'),
                'next_installment_amount' => $nextSchedule
                    ? (float) ($nextSchedule->principal_amount + $nextSchedule->interest_amount + $nextSchedule->penalty_amount)
                    : null,
                'next_installment_number' => $nextSchedule?->installment_number,
            ];
        })->values()->all();
    }

    private function formatDeposito(\Illuminate\Database\Eloquent\Collection $accounts): array
    {
        return $accounts->map(function ($acc) {
            $nextSchedule = $acc->schedules->first();

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
                // Jadwal bunga/pokok berikutnya
                'next_due_date'       => $nextSchedule?->schedule_date?->format('Y-m-d'),
                'next_net_interest'   => $nextSchedule ? (float) $nextSchedule->net_interest : null,
                'next_gross_interest' => $nextSchedule ? (float) $nextSchedule->gross_interest : null,
            ];
        })->values()->all();
    }

    private function buildSummary(Cif $cif): array
    {
        $totalTabungan = $cif->savingAccounts->sum('balance');
        $totalKredit   = $cif->loanAccounts->sum(
            fn ($a) => $a->outstanding_principal + $a->outstanding_interest + $a->outstanding_penalty
        );
        $totalDeposito = $cif->depositAccounts->sum('amount');

        // Next due date paling dekat di antara semua kredit
        $nearestLoanDue = $cif->loanAccounts
            ->map(fn ($a) => $a->schedules->first()?->due_date)
            ->filter()
            ->sort()
            ->first();

        // Next due date paling dekat di antara semua deposito
        $nearestDepositDue = $cif->depositAccounts
            ->map(fn ($a) => $a->schedules->first()?->schedule_date)
            ->filter()
            ->sort()
            ->first();

        return [
            'total_tabungan'          => (float) $totalTabungan,
            'total_outstanding_kredit' => (float) $totalKredit,
            'total_deposito'          => (float) $totalDeposito,
            'nearest_loan_due_date'   => $nearestLoanDue?->format('Y-m-d'),
            'nearest_deposit_due_date' => $nearestDepositDue?->format('Y-m-d'),
            'jumlah_rekening_tabungan' => $cif->savingAccounts->count(),
            'jumlah_kredit_aktif'      => $cif->loanAccounts->count(),
            'jumlah_deposito_aktif'    => $cif->depositAccounts->count(),
        ];
    }
}
