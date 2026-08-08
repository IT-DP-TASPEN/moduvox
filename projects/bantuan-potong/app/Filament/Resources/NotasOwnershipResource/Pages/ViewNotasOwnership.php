<?php

namespace App\Filament\Resources\NotasOwnershipResource\Pages;

use App\Filament\Resources\NotasOwnershipResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNotasOwnership extends ViewRecord
{
    protected static string $resource = NotasOwnershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
