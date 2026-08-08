<?php

namespace App\Filament\Resources\PermintaanEstimasiInternalResource\Pages;

use App\Filament\Resources\PermintaanEstimasiInternalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPermintaanEstimasiInternal extends ViewRecord
{
    protected static string $resource = PermintaanEstimasiInternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
