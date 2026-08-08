<?php

namespace App\Filament\Resources\PermintaanEstimasiResource\Pages;

use App\Filament\Resources\PermintaanEstimasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanEstimasi extends EditRecord
{
    protected static string $resource = PermintaanEstimasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
