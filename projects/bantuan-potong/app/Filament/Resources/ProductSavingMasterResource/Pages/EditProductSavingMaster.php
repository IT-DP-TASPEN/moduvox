<?php

namespace App\Filament\Resources\ProductSavingMasterResource\Pages;

use App\Filament\Resources\ProductSavingMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductSavingMaster extends EditRecord
{
    protected static string $resource = ProductSavingMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
