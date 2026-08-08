<?php

namespace App\Filament\Resources\SavingAccountResource\Pages;

use App\Filament\Resources\SavingAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSavingAccount extends EditRecord
{
    protected static string $resource = SavingAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
