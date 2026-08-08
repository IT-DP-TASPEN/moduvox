<?php

namespace App\Filament\Resources\PermintaanCheckingInternalResource\Pages;

use App\Filament\Resources\PermintaanCheckingInternalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPermintaanCheckingInternal extends ViewRecord
{
    protected static string $resource = PermintaanCheckingInternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
