<?php

namespace App\Filament\Resources\PermintaanFlaggingMutasiTifInternalResource\Pages;

use App\Filament\Resources\PermintaanFlaggingMutasiTifInternalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPermintaanFlaggingMutasiTifInternal extends ViewRecord
{
    protected static string $resource = PermintaanFlaggingMutasiTifInternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
