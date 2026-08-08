<?php

namespace App\Filament\Resources\PermintaanCheckingInternalResource\Pages;

use App\Filament\Resources\PermintaanCheckingInternalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanCheckingInternal extends EditRecord
{
    protected static string $resource = PermintaanCheckingInternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
