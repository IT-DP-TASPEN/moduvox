<?php

namespace App\Filament\Resources\PermintaanFlaggingMutasiTifInternalResource\Pages;

use App\Filament\Resources\PermintaanFlaggingMutasiTifInternalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanFlaggingMutasiTifInternal extends EditRecord
{
    protected static string $resource = PermintaanFlaggingMutasiTifInternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
