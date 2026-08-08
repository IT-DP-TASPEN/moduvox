<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\BanpotMaster;
use Carbon\Carbon;

class BanpotStatusStat extends BaseWidget
{

    public static function canView(): bool
    {
        return auth()->user()->hasRole([
            'super_admin',
            'staff_bosche',
            'approval_mitra_pusat',
            'maker_mitra_pusat',
        ]);
    }

    protected function getCards(): array
    {
        $user       = auth()->user();
        $canViewAll = $user->hasRole(['super_admin', 'staff_bosche']);

        $months = collect(range(5, 0))->map(
            fn($i) => Carbon::now()->subMonths($i)->format('Y-m')
        );

        $getMonthData = function ($status) use ($months, $canViewAll, $user) {
            return $months->map(function ($month) use ($status, $canViewAll, $user) {
                return BanpotMaster::where('status_banpot', $status)
                    ->when(
                        !$canViewAll,
                        fn($q) =>
                        $q->whereHas(
                            'creator',
                            fn($q2) =>
                            $q2->where('mitra_master_id', $user->mitra_master_id)
                        )
                    )
                    ->whereYear('created_at', substr($month, 0, 4))
                    ->whereMonth('created_at', substr($month, 5, 2))
                    ->count();
            })->toArray();
        };

        // default label map
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

        // override label if role specific
        if ($user->hasRole('approval_mitra_pusat')) {
            $statusMap['request'] = 'Need Approve';
        }

        if ($user->hasRole('staff_bosche')) {
            $statusMap['approved_mitra'] = 'Need Process';
        }

        // list of status card to display
        $rows = [
            ['request',        $statusMap['request'],        'primary'],
            ['approved_mitra', $statusMap['approved_mitra'], 'info'],
            ['rejected_mitra', $statusMap['rejected_mitra'], 'warning'],
            ['canceled',       $statusMap['canceled'],       'gray'],
            ['on_process',     $statusMap['on_process'],     'info'],
            ['success',        $statusMap['success'],        'success'],
            ['failed',         $statusMap['failed'],         'danger'],
            ['complete',       $statusMap['complete'],       'success'],
        ];

        // card from loop
        $cards = collect($rows)
            ->map(function ($item) use ($getMonthData, $canViewAll, $user) {

                [$status, $label, $color] = $item;

                $count = BanpotMaster::where('status_banpot', $status)
                    ->when(
                        !$canViewAll,
                        fn($q) =>
                        $q->whereHas(
                            'creator',
                            fn($q2) =>
                            $q2->where('mitra_master_id', $user->mitra_master_id)
                        )
                    )
                    ->count();

                return Card::make($label, $count)
                    ->color($color)
                    ->chart($getMonthData($status));
            })
            ->toArray();

        // push total card
        $cards[] = Card::make(
            'Total Data',
            BanpotMaster::when(
                !$canViewAll,
                fn($q) =>
                $q->whereHas(
                    'creator',
                    fn($q2) =>
                    $q2->where('mitra_master_id', $user->mitra_master_id)
                )
            )->count()
        )
            ->color('primary')
            ->chart(
                $months->map(function ($month) use ($canViewAll, $user) {
                    return BanpotMaster::when(
                        !$canViewAll,
                        fn($q) =>
                        $q->whereHas(
                            'creator',
                            fn($q2) =>
                            $q2->where('mitra_master_id', $user->mitra_master_id)
                        )
                    )
                        ->whereYear('created_at', substr($month, 0, 4))
                        ->whereMonth('created_at', substr($month, 5, 2))
                        ->count();
                })->toArray()
            );

        return $cards;
    }
}
