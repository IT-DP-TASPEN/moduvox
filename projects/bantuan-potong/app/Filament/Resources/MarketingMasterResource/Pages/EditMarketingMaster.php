<?php

namespace App\Filament\Resources\MarketingMasterResource\Pages;

use App\Filament\Resources\MarketingMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketingMaster extends EditRecord
{
    protected static string $resource = MarketingMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
