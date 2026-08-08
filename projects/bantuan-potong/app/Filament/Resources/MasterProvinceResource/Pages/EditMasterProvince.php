<?php

namespace App\Filament\Resources\MasterProvinceResource\Pages;

use App\Filament\Resources\MasterProvinceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterProvince extends EditRecord
{
    protected static string $resource = MasterProvinceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
