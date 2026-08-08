<?php

namespace App\Filament\Resources\ProductSavingMasterResource\Pages;

use App\Filament\Resources\ProductSavingMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductSavingMaster extends ViewRecord
{
    protected static string $resource = ProductSavingMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
