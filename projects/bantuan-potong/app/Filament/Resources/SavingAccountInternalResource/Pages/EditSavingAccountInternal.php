<?php

namespace App\Filament\Resources\SavingAccountInternalResource\Pages;

use App\Filament\Resources\SavingAccountInternalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSavingAccountInternal extends EditRecord
{
    protected static string $resource = SavingAccountInternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
