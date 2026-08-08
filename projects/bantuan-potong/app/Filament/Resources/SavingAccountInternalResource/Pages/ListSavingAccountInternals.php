<?php

namespace App\Filament\Resources\SavingAccountInternalResource\Pages;

use App\Exports\SavingAccountInternalExport;
use App\Filament\Resources\SavingAccountInternalResource;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Resources\Components\Tab;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SavingAccountResource;

class ListSavingAccountInternals extends ListRecords
{
    protected static string $resource = SavingAccountInternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn() => Auth::user()->hasRole(['staff_bosche', 'open_table', 'admin_support', 'super_admin'])),

            Actions\Action::make('exportSavingInternal')
                ->label('Export Saving Data')
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

                    $filename = 'saving_' . now()->format('Ymd_His') . '.xlsx';

                    return Excel::download(new SavingAccountInternalExport($records), $filename);
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();

        $query = parent::getTableQuery();

        // ===== ROLE CHECK =====
        $isSuperOrBosche = $user->hasRole(['super_admin', 'staff_bosche']);
        $isOpenTabel     = $user->hasRole('open_table');
        $isMitraPusat    = $user->mitra_master_id && !$user->branch_master_id;

        $branchId = $user->branch_master_id;
        $masterId = $user->mitra_master_id;

        // ===== SCOPE DATA =====
        if (! $isSuperOrBosche) {
            if ($isOpenTabel) {
                // OPEN TABEL → hanya data yang dibuat oleh user yang login
                $query->where('created_by', $user->id);
            } elseif ($branchId) {
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
