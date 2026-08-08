<?php

namespace App\Filament\Resources\MitraMasterResource\Pages;

use App\Filament\Resources\MitraMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMitraMaster extends ViewRecord
{
    protected static string $resource = MitraMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
