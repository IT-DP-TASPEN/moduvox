<?php

namespace App\Filament\Resources\PermintaanCheckingResource\Pages;

use App\Filament\Resources\PermintaanCheckingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanChecking extends EditRecord
{
    protected static string $resource = PermintaanCheckingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
