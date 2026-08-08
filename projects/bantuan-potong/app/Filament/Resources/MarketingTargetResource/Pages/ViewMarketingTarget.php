<?php

namespace App\Filament\Resources\MarketingTargetResource\Pages;

use App\Filament\Resources\MarketingTargetResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMarketingTarget extends ViewRecord
{
    protected static string $resource = MarketingTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
