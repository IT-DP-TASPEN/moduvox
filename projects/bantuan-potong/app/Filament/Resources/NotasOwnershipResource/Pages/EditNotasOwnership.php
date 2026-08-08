<?php

namespace App\Filament\Resources\NotasOwnershipResource\Pages;

use App\Filament\Resources\NotasOwnershipResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotasOwnership extends EditRecord
{
    protected static string $resource = NotasOwnershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
