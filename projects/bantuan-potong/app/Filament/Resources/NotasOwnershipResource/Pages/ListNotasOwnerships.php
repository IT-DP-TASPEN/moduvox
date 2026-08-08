<?php

namespace App\Filament\Resources\NotasOwnershipResource\Pages;

use App\Exports\NotasOwnershipExport;
use Filament\Actions;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\NotasOwnershipImport;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\NotasOwnershipResource;

class ListNotasOwnerships extends ListRecords
{
    protected static string $resource = NotasOwnershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    try {
                        $fileName = 'notas_ownerships_' . date('Y-m-d_His') . '.xlsx';

                        return Excel::download(new NotasOwnershipExport, $fileName);
                    } catch (\Throwable $e) {
                        Log::error('Gagal export Excel: ' . $e->getMessage(), ['exception' => $e]);

                        Notification::make()
                            ->title('Gagal Export Excel')
                            ->body('Pesan error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('importExcel')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file_excel')
                        ->label('File Excel')
                        ->required()
                        ->disk('local')
                        ->directory('private/excel')
                        ->preserveFilenames(),
                ])
                ->action(function (array $data) {
                    set_time_limit(3600);
                    ini_set('memory_limit', '512M');

                    try {
                        // Ambil full path file
                        $path = Storage::disk('local')->path($data['file_excel']);

                        // Import pakai Laravel Excel, beri path file
                        Excel::import(new NotasOwnershipImport, $path);

                        Notification::make()
                            ->title('Import Berhasil')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Log::error('Gagal import Excel: ' . $e->getMessage(), ['exception' => $e]);

                        Notification::make()
                            ->title('Gagal Import Excel')
                            ->body('Pesan error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
        ];
    }
}
