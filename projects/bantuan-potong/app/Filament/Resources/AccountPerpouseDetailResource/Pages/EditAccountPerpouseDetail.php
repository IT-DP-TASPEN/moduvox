<?php

namespace App\Filament\Resources\AccountPerpouseDetailResource\Pages;

use App\Filament\Resources\AccountPerpouseDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountPerpouseDetail extends EditRecord
{
    protected static string $resource = AccountPerpouseDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
