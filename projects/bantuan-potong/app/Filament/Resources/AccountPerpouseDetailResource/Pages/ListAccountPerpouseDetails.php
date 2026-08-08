<?php

namespace App\Filament\Resources\AccountPerpouseDetailResource\Pages;

use App\Filament\Resources\AccountPerpouseDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountPerpouseDetails extends ListRecords
{
    protected static string $resource = AccountPerpouseDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
