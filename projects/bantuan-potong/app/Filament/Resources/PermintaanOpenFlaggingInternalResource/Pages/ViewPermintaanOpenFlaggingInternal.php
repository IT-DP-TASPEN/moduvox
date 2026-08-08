<?php

namespace App\Filament\Resources\PermintaanOpenFlaggingInternalResource\Pages;

use App\Filament\Resources\PermintaanOpenFlaggingInternalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPermintaanOpenFlaggingInternal extends ViewRecord
{
    protected static string $resource = PermintaanOpenFlaggingInternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
