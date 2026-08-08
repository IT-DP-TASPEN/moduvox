<?php

namespace App\Filament\Resources\PermintaanFlaggingMutasiTifResource\Pages;

use App\Filament\Resources\PermintaanFlaggingMutasiTifResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanFlaggingMutasiTif extends EditRecord
{
    protected static string $resource = PermintaanFlaggingMutasiTifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
