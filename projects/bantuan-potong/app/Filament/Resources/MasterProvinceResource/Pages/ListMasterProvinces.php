<?php

namespace App\Filament\Resources\MasterProvinceResource\Pages;

use App\Filament\Resources\MasterProvinceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterProvinces extends ListRecords
{
    protected static string $resource = MasterProvinceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
