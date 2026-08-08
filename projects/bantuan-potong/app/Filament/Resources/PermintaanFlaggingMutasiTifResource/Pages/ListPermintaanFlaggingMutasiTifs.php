<?php

namespace App\Filament\Resources\PermintaanFlaggingMutasiTifResource\Pages;

use App\Exports\FlaggingMutasiExport;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Resources\Components\Tab;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PermintaanFlaggingMutasiTifResource;

class ListPermintaanFlaggingMutasiTifs extends ListRecords
{
    protected static string $resource = PermintaanFlaggingMutasiTifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn() => Auth::user()->hasRole(['staff_bosche', 'maker_mitra_cabang', 'super_admin'])),

            Actions\Action::make('exportFlaggingMutasi')
                ->label('Export Flagging Mutasi Data')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Export')
                ->modalSubheading('Data akan diexport.')
                ->action(function () {
                    $query = $this->getFilteredTableQuery();

                    $records = $query->get();

                    if ($records->isEmpty()) {
                        Notification::make()
                            ->title('Tidak ada data untuk diexport')
                            ->warning()
                            ->send();
                        return;
                    }

                    $filename = 'flagging_mutasi' . now()->format('Ymd_His') . '.xlsx';

                    return Excel::download(new FlaggingMutasiExport($records), $filename);
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();

        $query = parent::getTableQuery();

        // ===== ROLE CHECK =====
        $isSuperOrBosche = $user->hasRole(['super_admin', 'staff_bosche']);
        $isMitraPusat   = $user->mitra_master_id && !$user->mitra_branch_id;

        $branchId = $user->mitra_branch_id;
        $masterId = $user->mitra_master_id;

        // ===== SCOPE DATA =====
        if (! $isSuperOrBosche) {
            if ($branchId) {
                // CABANG → hanya data cabangnya
                $query->whereHas('creator', function ($q) use ($branchId) {
                    $q->where('mitra_branch_id', $branchId);
                });
            } elseif ($isMitraPusat && $masterId) {
                // MITRA PUSAT → semua cabang di bawahnya
                $query->whereHas('creator', function ($q) use ($masterId) {
                    $q->where('mitra_master_id', $masterId);
                });
            }
        }

        return $query;
    }

    // public function getTabs(): array
    // {
    //     $user = Auth::user();

    //     // --- Tentukan scope akses user ---
    //     $isSuperOrBosche = $user->hasRole(['super_admin', 'staff_bosche']);
    //     $isMitraPusat = $user->mitra_master_id && !$user->mitra_branch_id;

    //     $shouldFilterByBranch = !($isSuperOrBosche || $isMitraPusat);
    //     $branchId = $user->mitra_branch_id;
    //     $masterId = $user->mitra_master_id;

    //     // --- Fungsi reusable untuk filter cabang/pusat ---
    //     $filterByScope = function (Builder $query) use ($shouldFilterByBranch, $branchId, $isMitraPusat, $masterId) {
    //         if ($shouldFilterByBranch && $branchId) {
    //             // Cabang hanya bisa lihat data yang dibuat oleh cabangnya sendiri
    //             $query->whereHas('creator', function ($q) use ($branchId) {
    //                 $q->where('mitra_branch_id', $branchId);
    //             });
    //         } elseif ($isMitraPusat && $masterId) {
    //             // Mitra pusat bisa lihat semua cabang di bawahnya
    //             $query->whereHas('creator', function ($q) use ($masterId) {
    //                 $q->where('mitra_master_id', $masterId);
    //             });
    //         }
    //     };

    //     // --- Tabs ---
    //     return [
    //         'all' => Tab::make()
    //             ->label('All')
    //             ->modifyQueryUsing($filterByScope),

    //         'request' => Tab::make()
    //             ->label('Request')
    //             ->modifyQueryUsing(function (Builder $query) use ($filterByScope) {
    //                 $query->where('status', 'request');
    //                 $filterByScope($query);
    //             }),

    //         'need_approve' => Tab::make()
    //             ->label('Need Approve')
    //             ->modifyQueryUsing(function (Builder $query) use ($filterByScope) {
    //                 $query->where('status', 'request'); // typo "sxtatus" sudah dibenerin
    //                 $filterByScope($query);
    //             }),

    //         'approved_mitra' => Tab::make()
    //             ->label('Approved Mitra')
    //             ->modifyQueryUsing(function (Builder $query) use ($filterByScope) {
    //                 $query->where('status', 'approved_mitra');
    //                 $filterByScope($query);
    //             }),

    //         'rejected_mitra' => Tab::make()
    //             ->label('Rejected Mitra')
    //             ->modifyQueryUsing(function (Builder $query) use ($filterByScope) {
    //                 $query->where('status', 'rejected_mitra');
    //                 $filterByScope($query);
    //             }),

    //         'canceled' => Tab::make()
    //             ->label('Canceled')
    //             ->modifyQueryUsing(function (Builder $query) use ($filterByScope) {
    //                 $query->where('status', 'canceled');
    //                 $filterByScope($query);
    //             }),

    //         'on_process' => Tab::make()
    //             ->label('On Process')
    //             ->modifyQueryUsing(function (Builder $query) use ($filterByScope) {
    //                 $query->where('status', 'on_process');
    //                 $filterByScope($query);
    //             }),

    //         'success' => Tab::make()
    //             ->label('Success')
    //             ->modifyQueryUsing(function (Builder $query) use ($filterByScope) {
    //                 $query->where('status', 'success');
    //                 $filterByScope($query);
    //             }),

    //         'failed' => Tab::make()
    //             ->label('Failed')
    //             ->modifyQueryUsing(function (Builder $query) use ($filterByScope) {
    //                 $query->where('status', 'failed');
    //                 $filterByScope($query);
    //             }),

    //         'complete' => Tab::make()
    //             ->label('Complete')
    //             ->modifyQueryUsing(function (Builder $query) use ($filterByScope) {
    //                 $query->where('status', 'complete');
    //                 $filterByScope($query);
    //             }),
    //     ];
    // }
}
