<?php

namespace App\Filament\Resources\MasterProvinceResource\Pages;

use App\Filament\Resources\MasterProvinceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMasterProvince extends ViewRecord
{
    protected static string $resource = MasterProvinceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
