<?php

namespace App\Filament\Resources\CekFlaggingNotasResource\Pages;

use App\Filament\Resources\CekFlaggingNotasResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCekFlaggingNotas extends ViewRecord
{
    protected static string $resource = CekFlaggingNotasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
