<?php

namespace App\Filament\Resources\MarketingTargetResource\Pages;

use App\Filament\Resources\MarketingTargetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketingTarget extends EditRecord
{
    protected static string $resource = MarketingTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
