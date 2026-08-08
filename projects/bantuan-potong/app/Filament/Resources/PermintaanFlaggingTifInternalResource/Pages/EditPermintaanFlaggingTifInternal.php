<?php

namespace App\Filament\Resources\PermintaanFlaggingTifInternalResource\Pages;

use App\Filament\Resources\PermintaanFlaggingTifInternalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanFlaggingTifInternal extends EditRecord
{
    protected static string $resource = PermintaanFlaggingTifInternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
