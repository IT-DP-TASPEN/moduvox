<?php

namespace App\Filament\Resources\PermintaanFlaggingTifInternalResource\Pages;

use App\Exports\FlaggingInternalExport;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PermintaanFlaggingTifInternalResource;


class ListPermintaanFlaggingTifInternals extends ListRecords
{
    protected static string $resource = PermintaanFlaggingTifInternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn() => Auth::user()->hasRole(['staff_bosche', 'open_table', 'admin_support', 'super_admin'])),

            Actions\Action::make('exportFlaggingInternal')
                ->label('Export Flagging Internal Data')
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

                    $filename = 'flagging_' . now()->format('Ymd_His') . '.xlsx';

                    return Excel::download(new FlaggingInternalExport($records), $filename);
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();

        $query = parent::getTableQuery();

        // ===== ROLE CHECK =====
        $isSuperOrBosche = $user->hasRole(['super_admin', 'staff_bosche']);
        $isMitraPusat   = $user->mitra_master_id && !$user->branch_master_id;

        $branchId = $user->branch_master_id;
        $masterId = $user->mitra_master_id;

        // ===== SCOPE DATA =====
        if (! $isSuperOrBosche) {
            if ($branchId) {
                // CABANG → hanya data cabangnya
                $query->whereHas('creator', function ($q) use ($branchId) {
                    $q->where('branch_master_id', $branchId);
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
}
