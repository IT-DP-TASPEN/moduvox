<?php

namespace App\Filament\Resources\BanpotMasterResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Exports\BanpotMasterExport;
use App\Imports\BanpotMasterImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Resources\Components\Tab;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BanpotMasterResource;

class ListBanpotMasters extends ListRecords
{
    protected static string $resource = BanpotMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn() => Auth::user()->hasRole(['staff_bosche', 'maker_mitra_pusat', 'super_admin'])),
            Actions\Action::make('importExcel')
                ->label('Import Excel')
                ->visible(fn() => Auth::user()->hasRole(['staff_bosche', 'maker_mitra_pusat', 'super_admin']))
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file_excel')
                        ->label('File Excel')
                        ->required()
                        ->disk('local')
                        ->directory('private/excel')
                        ->preserveFilenames(),
                    Select::make('bulan')
                        ->label('Bulan')
                        ->options([
                            '01' => 'Januari',
                            '02' => 'Februari',
                            '03' => 'Maret',
                            '04' => 'April',
                            '05' => 'Mei',
                            '06' => 'Juni',
                            '07' => 'Juli',
                            '08' => 'Agustus',
                            '09' => 'September',
                            '10' => 'Oktober',
                            '11' => 'November',
                            '12' => 'Desember',
                        ])
                        ->required(),
                    Select::make('tahun')
                        ->label('Tahun')
                        ->options(function () {
                            $currentYear = date('Y');
                            return [
                                $currentYear - 1 => $currentYear - 1,
                                $currentYear => $currentYear,
                                $currentYear + 1 => $currentYear + 1,
                            ];
                        })
                        ->required()
                        ->default(date('Y')),
                ])
                ->action(function (array $data) {
                    set_time_limit(3600);
                    ini_set('memory_limit', '512M');

                    try {
                        // Ambil path file yang diupload
                        $path = Storage::disk('local')->path($data['file_excel']);

                        // Buat bulan_dapem dari input user
                        $bulanDapem = $data['tahun'] . str_pad($data['bulan'], 2, '0', STR_PAD_LEFT) . '01';

                        $nextDueDate = Carbon::createFromFormat('Ymd', $bulanDapem)
                            ->format('Y-m-d');

                        // Import Excel
                        Excel::import(new BanpotMasterImport($bulanDapem, $nextDueDate), $path);

                        Notification::make()
                            ->title('Import Selesai')
                            ->success()
                            ->body('Semua data berhasil diimport.')
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Import Excel')
                            ->danger()
                            ->body('Pesan error: ' . $e->getMessage())
                            ->send();
                    }
                }),

            Actions\Action::make('exportBanpot')
                ->label('Export Banpot Data')
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

                    $filename = 'banpot_' . now()->format('Ymd_His') . '.xlsx';

                    return Excel::download(new BanpotMasterExport($records), $filename);
                }),

        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        $user = Auth::user();
        $mitraName = $user?->mitraMaster?->nama_mitra;

        // 🔒 AUTO FILTER MITRA (non admin)
        if (! $user->hasRole(['super_admin', 'staff_bosche'])) {
            $query->where('created_mitra', $mitraName);
        }

        return $query;
    }




    // public function getTabs(): array
    // {
    //     $user = Auth::user();
    //     $mitraName = $user?->mitraMaster?->nama_mitra;

    //     // Hanya filter mitra kalau user bukan super_admin atau staff_bosche
    //     $shouldFilterByMitra = !($user->hasRole('super_admin') || $user->hasRole('staff_bosche'));

    //     return [
    //         'all' => Tab::make()
    //             ->label('All')
    //             ->modifyQueryUsing(function (Builder $query) use ($shouldFilterByMitra, $mitraName) {
    //                 if ($shouldFilterByMitra) {
    //                     $query->where('created_mitra', $mitraName);
    //                 }
    //             }),

    //         'request' => Tab::make()
    //             ->label('Request')
    //             ->modifyQueryUsing(function (Builder $query) use ($shouldFilterByMitra, $mitraName) {
    //                 $query->where('status_banpot', 'request');
    //                 if ($shouldFilterByMitra) {
    //                     $query->where('created_mitra', $mitraName);
    //                 }
    //             }),

    //         'need_approve' => Tab::make()
    //             ->label('Need Approve')
    //             ->modifyQueryUsing(function (Builder $query) use ($shouldFilterByMitra, $mitraName) {
    //                 $query->where('status_banpot', 'request');
    //                 if ($shouldFilterByMitra) {
    //                     $query->where('created_mitra', $mitraName);
    //                 }
    //             }),



    //         'need_proses' => Tab::make()
    //             ->label('Need Process')
    //             ->modifyQueryUsing(function (Builder $query) use ($shouldFilterByMitra, $mitraName) {
    //                 $query->where('status_banpot', 'approved_mitra');
    //                 if ($shouldFilterByMitra) {
    //                     $query->where('created_mitra', $mitraName);
    //                 }
    //             }),

    //         'rejected_mitra' => Tab::make()
    //             ->label('Rejected')
    //             ->modifyQueryUsing(function (Builder $query) use ($shouldFilterByMitra, $mitraName) {
    //                 $query->where('status_banpot', 'rejected_mitra');
    //                 if ($shouldFilterByMitra) {
    //                     $query->where('created_mitra', $mitraName);
    //                 }
    //             }),

    //         'canceled' => Tab::make()
    //             ->label('Canceled')
    //             ->modifyQueryUsing(function (Builder $query) use ($shouldFilterByMitra, $mitraName) {
    //                 $query->where('status_banpot', 'canceled');
    //                 if ($shouldFilterByMitra) {
    //                     $query->where('created_mitra', $mitraName);
    //                 }
    //             }),

    //         'on_process' => Tab::make()
    //             ->label('Processing')
    //             ->modifyQueryUsing(function (Builder $query) use ($shouldFilterByMitra, $mitraName) {
    //                 $query->where('status_banpot', 'on_process');
    //                 if ($shouldFilterByMitra) {
    //                     $query->where('created_mitra', $mitraName);
    //                 }
    //             }),

    //         'success' => Tab::make()
    //             ->label('Success')
    //             ->modifyQueryUsing(function (Builder $query) use ($shouldFilterByMitra, $mitraName) {
    //                 $query->where('status_banpot', 'success');
    //                 if ($shouldFilterByMitra) {
    //                     $query->where('created_mitra', $mitraName);
    //                 }
    //             }),

    //         'failed' => Tab::make()
    //             ->label('Failed')
    //             ->modifyQueryUsing(function (Builder $query) use ($shouldFilterByMitra, $mitraName) {
    //                 $query->where('status_banpot', 'failed');
    //                 if ($shouldFilterByMitra) {
    //                     $query->where('created_mitra', $mitraName);
    //                 }
    //             }),

    //         'completed' => Tab::make()
    //             ->label('Completed')
    //             ->modifyQueryUsing(function (Builder $query) use ($shouldFilterByMitra, $mitraName) {
    //                 $query->where('status_banpot', 'complete');
    //                 if ($shouldFilterByMitra) {
    //                     $query->where('created_mitra', $mitraName);
    //                 }
    //             }),
    //     ];
    // }
}
