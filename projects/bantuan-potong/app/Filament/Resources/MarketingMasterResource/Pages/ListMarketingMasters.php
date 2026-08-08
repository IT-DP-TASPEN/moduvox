<?php

namespace App\Filament\Resources\MarketingMasterResource\Pages;

use App\Filament\Resources\MarketingMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingMasters extends ListRecords
{
    protected static string $resource = MarketingMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
