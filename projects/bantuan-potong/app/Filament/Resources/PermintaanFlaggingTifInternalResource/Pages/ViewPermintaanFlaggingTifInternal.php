<?php

namespace App\Filament\Resources\PermintaanFlaggingTifInternalResource\Pages;

use App\Filament\Resources\PermintaanFlaggingTifInternalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPermintaanFlaggingTifInternal extends ViewRecord
{
    protected static string $resource = PermintaanFlaggingTifInternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
