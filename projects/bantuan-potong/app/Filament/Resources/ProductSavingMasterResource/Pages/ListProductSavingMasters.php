<?php

namespace App\Filament\Resources\ProductSavingMasterResource\Pages;

use App\Filament\Resources\ProductSavingMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductSavingMasters extends ListRecords
{
    protected static string $resource = ProductSavingMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
