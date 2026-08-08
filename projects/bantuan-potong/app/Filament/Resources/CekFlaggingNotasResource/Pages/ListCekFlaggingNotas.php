<?php

namespace App\Filament\Resources\CekFlaggingNotasResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\CekFlaggingNotasResource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;

class ListCekFlaggingNotas extends ListRecords
{
    protected static string $resource = CekFlaggingNotasResource::class;

    /**
     * ✅ HARUS PUBLIC
     */
    public ?string $notas = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('search')
                ->label('Search')
                ->icon('heroicon-o-magnifying-glass')
                ->modalHeading('Cari Berdasarkan NOTAS')
                ->form([
                    TextInput::make('notas')
                        ->label('Nomor NOTAS')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->notas = trim($data['notas']);
                }),

            Action::make('reset')
                ->label('Reset')
                ->color('gray')
                ->action(function () {
                    $this->notas = null;
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        if (!$this->notas) {
            return parent::getTableQuery()
                ->whereRaw('1 = 0');
        }

        return parent::getTableQuery()
            ->where('notas', 'like', "%{$this->notas}%");
    }
}
