<?php

namespace App\Filament\Resources\PermintaanEstimasiInternalResource\Pages;

use App\Filament\Resources\PermintaanEstimasiInternalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanEstimasiInternal extends EditRecord
{
    protected static string $resource = PermintaanEstimasiInternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
