<?php

namespace App\Filament\Resources\MarketingMasterResource\Pages;

use App\Filament\Resources\MarketingMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMarketingMaster extends ViewRecord
{
    protected static string $resource = MarketingMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
