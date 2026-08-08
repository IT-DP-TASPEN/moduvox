<?php

namespace App\Filament\Resources\BanpotMasterResource\Pages;

use App\Filament\Resources\BanpotMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBanpotMaster extends EditRecord
{
    protected static string $resource = BanpotMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}