<?php

namespace App\Filament\Resources\AccountPerpouseMasterResource\Pages;

use App\Filament\Resources\AccountPerpouseMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountPerpouseMaster extends EditRecord
{
    protected static string $resource = AccountPerpouseMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
