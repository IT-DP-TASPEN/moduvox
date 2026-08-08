<?php

namespace App\Filament\Resources\PermintaanFlaggingTifResource\Pages;

use App\Filament\Resources\PermintaanFlaggingTifResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanFlaggingTif extends EditRecord
{
    protected static string $resource = PermintaanFlaggingTifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
