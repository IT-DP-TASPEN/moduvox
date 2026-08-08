<?php

namespace App\Filament\Resources\PermintaanEstimasiInternalResource\Pages;

use App\Exports\EstimasiInternalExport;
use App\Filament\Resources\PermintaanEstimasiInternalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ListPermintaanEstimasiInternals extends ListRecords
{
    protected static string $resource = PermintaanEstimasiInternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn() => Auth::user()->hasRole(['staff_bosche', 'open_table', 'admin_support', 'super_admin'])),

            Actions\Action::make('exportEstimasiInternal')
                ->label('Export Estimasi Data')
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

                    $filename = 'estimasi_' . now()->format('Ymd_His') . '.xlsx';

                    return Excel::download(new EstimasiInternalExport($records), $filename);
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
