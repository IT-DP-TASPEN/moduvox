<?php

namespace App\Filament\Resources\CekNotasResource\Pages;

use App\Filament\Resources\CekNotasResource;
use Filament\Resources\Pages\ListRecords;

class ListCekNotas extends ListRecords
{
    protected static string $resource = CekNotasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada create action
        ];
    }
}
