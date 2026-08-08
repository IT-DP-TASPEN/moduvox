<?php

namespace App\Filament\Resources\PermintaanOpenFlaggingTifResource\Pages;

use App\Filament\Resources\PermintaanOpenFlaggingTifResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanOpenFlaggingTif extends EditRecord
{
    protected static string $resource = PermintaanOpenFlaggingTifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
