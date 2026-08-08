<?php

namespace App\Filament\Resources\PermintaanOpenFlaggingInternalResource\Pages;

use App\Filament\Resources\PermintaanOpenFlaggingInternalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanOpenFlaggingInternal extends EditRecord
{
    protected static string $resource = PermintaanOpenFlaggingInternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
