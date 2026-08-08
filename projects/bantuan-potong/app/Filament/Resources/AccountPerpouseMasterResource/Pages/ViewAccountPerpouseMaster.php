<?php

namespace App\Filament\Resources\AccountPerpouseMasterResource\Pages;

use App\Filament\Resources\AccountPerpouseMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAccountPerpouseMaster extends ViewRecord
{
    protected static string $resource = AccountPerpouseMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
