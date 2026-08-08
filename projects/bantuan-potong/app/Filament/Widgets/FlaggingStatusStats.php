<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\PermintaanFlaggingTif;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class FlaggingStatusStats extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()->hasRole([
            'super_admin',
            'staff_bosche',
            'approval_mitra_pusat',
            'maker_mitra_pusat',
            'approval_mitra_cabang',
            'maker_mitra_cabang',
        ]);
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        $isSuperOrStaff = $user->hasRole(['super_admin', 'staff_bosche']);
        $isPusat = $user->hasRole(['approval_mitra_pusat', 'maker_mitra_pusat']);
        $isCabang = $user->hasRole(['approval_mitra_cabang', 'maker_mitra_cabang']);

        $months = collect(range(5, 0))
            ->map(fn($i) => Carbon::now()->subMonths($i)->format('Y-m'));

        $getMonthData = function ($status) use ($months, $isSuperOrStaff, $user, $isPusat, $isCabang) {
            return $months->map(function ($month) use ($status, $isSuperOrStaff, $user, $isPusat, $isCabang) {
                $query = PermintaanFlaggingTif::where('status', $status);

                if (!$isSuperOrStaff) {
                    // pusat => filter by mitra
                    if ($isPusat) {
                        $query->whereHas('creator', fn($q) => $q->where('mitra_master_id', $user->mitra_master_id));
                    }

                    // cabang => filter by branch + mitra
                    if ($isCabang) {
                        $query->whereHas(
                            'creator',
                            fn($q) =>
                            $q->where('mitra_branch_id', $user->mitra_branch_id)
                                ->where('mitra_master_id', $user->mitra_master_id)
                        );
                    }
                }

                return $query
                    ->whereYear('created_at', substr($month, 0, 4))
                    ->whereMonth('created_at', substr($month, 5, 2))
                    ->count();
            })->toArray();
        };

        $statusMap = [
            'request'        => 'Request',
            'approved_mitra' => 'Approved Mitra',
            'rejected_mitra' => 'Rejected Mitra',
            'canceled'       => 'Canceled',
            'on_process'     => 'On Process',
            'success'        => 'Success',
            'failed'         => 'Failed',
            'complete'       => 'Completed',
        ];

        if ($user->hasRole('approval_mitra_pusat')) {
            $statusMap['request'] = 'Need Approve';
        }
        if ($user->hasRole('staff_bosche')) {
            $statusMap['approved_mitra'] = 'Need Process';
        }

        $statuses = [
            ['request',        $statusMap['request'],        'primary'],
            ['approved_mitra', $statusMap['approved_mitra'], 'info'],
            ['rejected_mitra', $statusMap['rejected_mitra'], 'warning'],
            ['canceled',       $statusMap['canceled'],       'gray'],
            ['on_process',     $statusMap['on_process'],     'info'],
            ['success',        $statusMap['success'],        'success'],
            ['failed',         $statusMap['failed'],         'danger'],
            ['complete',       $statusMap['complete'],       'success'],
        ];

        $stats = collect($statuses)->map(function ($item) use ($getMonthData, $isSuperOrStaff, $user, $isPusat, $isCabang) {
            [$status, $label, $color] = $item;

            $query = PermintaanFlaggingTif::where('status', $status);

            if (!$isSuperOrStaff) {
                if ($isPusat) {
                    $query->whereHas('creator', fn($q) => $q->where('mitra_master_id', $user->mitra_master_id));
                }
                if ($isCabang) {
                    $query->whereHas(
                        'creator',
                        fn($q) =>
                        $q->where('mitra_branch_id', $user->mitra_branch_id)
                            ->where('mitra_master_id', $user->mitra_master_id)
                    );
                }
            }

            $count = $query->count();

            return Stat::make($label, $count)
                ->color($color)
                ->chart($getMonthData($status));
        })->toArray();

        // Total
        $totalQuery = PermintaanFlaggingTif::query();

        if (!$isSuperOrStaff) {
            if ($isPusat) {
                $totalQuery->whereHas('creator', fn($q) => $q->where('mitra_master_id', $user->mitra_master_id));
            }
            if ($isCabang) {
                $totalQuery->whereHas(
                    'creator',
                    fn($q) =>
                    $q->where('mitra_branch_id', $user->mitra_branch_id)
                        ->where('mitra_master_id', $user->mitra_master_id)
                );
            }
        }

        $stats[] = Stat::make('Total Data', $totalQuery->count())
            ->color('primary')
            ->chart(
                $months->map(function ($month) use ($totalQuery) {
                    return (clone $totalQuery)
                        ->whereYear('created_at', substr($month, 0, 4))
                        ->whereMonth('created_at', substr($month, 5, 2))
                        ->count();
                })->toArray()
            );

        return $stats;
    }
}
