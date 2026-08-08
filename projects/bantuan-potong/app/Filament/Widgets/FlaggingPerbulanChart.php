<?php

namespace App\Filament\Widgets;

use App\Models\PermintaanFlaggingTif;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class FlaggingPerbulanChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Flagging Status';

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
        $user = auth()->user();

        $isSuperOrStaff = $user->hasRole(['super_admin', 'staff_bosche']);
        $isPusat = $user->hasRole(['approval_mitra_pusat', 'maker_mitra_pusat']);
        $isCabang = $user->hasRole(['approval_mitra_cabang', 'maker_mitra_cabang']);

        $months = collect(range(5, 0))
            ->map(fn($i) => Carbon::now()->subMonths($i)->format('Y-m'));

        $statuses = [
            'request'        => ['label' => 'Request',        'color' => '#6366F1'],
            'approved_mitra' => ['label' => 'Approved Mitra', 'color' => '#3B82F6'],
            'rejected_mitra' => ['label' => 'Rejected Mitra', 'color' => '#F59E0B'],
            'canceled'       => ['label' => 'Canceled',       'color' => '#6B7280'],
            'on_process'     => ['label' => 'On Process',     'color' => '#8B5CF6'],
            'success'        => ['label' => 'Success',        'color' => '#10B981'],
            'failed'         => ['label' => 'Failed',         'color' => '#EF4444'],
            'complete'       => ['label' => 'Completed',      'color' => '#059669'],
        ];

        $datasets = [];

        foreach ($statuses as $status => $meta) {
            $data = $months->map(function ($month) use ($status, $user, $isSuperOrStaff, $isPusat, $isCabang) {
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

                return $query
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
