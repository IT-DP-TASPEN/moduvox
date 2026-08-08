<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\BanpotMaster;
use Carbon\Carbon;

class BanpotStatusPerbulanChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Banpot Status';

    protected static ?int $sort = 2;

    public function getColumnSpan(): int|string|array
    {
        return 2; // Lebar 2 kolom
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $user       = auth()->user();
        $canViewAll = $user->hasRole(['super_admin', 'staff_bosche']);

        $months = collect(range(5, 0))
            ->map(fn($i) => Carbon::now()->subMonths($i)->format('Y-m'));

        $statuses = [
            'request'        => ['label' => 'Banpot Requested',      'color' => '#6366F1'],
            'approved_mitra' => ['label' => 'Banpot Approve Mitra', 'color' => '#3B82F6'],
            'rejected_mitra' => ['label' => 'Banpot Rejected Mitra', 'color' => '#F59E0B'],
            'canceled'       => ['label' => 'Banpot Canceled',      'color' => '#6B7280'],
            'on_process'     => ['label' => 'Banpot On Process',    'color' => '#8B5CF6'],
            'success'        => ['label' => 'Banpot Success',       'color' => '#10B981'],
            'failed'         => ['label' => 'Banpot Failed',        'color' => '#EF4444'],
            'complete'       => ['label' => 'Banpot Completed',     'color' => '#059669'],
        ];

        $datasets = [];

        foreach ($statuses as $status => $meta) {
            $data = $months->map(function ($month) use ($status, $user, $canViewAll) {
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

            $datasets[] = [
                'label' => $meta['label'],
                'data' => $data,
                'borderColor' => $meta['color'],
                'fill' => false,
            ];
        }

        return [
            'labels' => $months->map(fn($m) => Carbon::parse($m . '-01')->format('M Y'))->toArray(),
            'datasets' => $datasets,
        ];
    }
}
