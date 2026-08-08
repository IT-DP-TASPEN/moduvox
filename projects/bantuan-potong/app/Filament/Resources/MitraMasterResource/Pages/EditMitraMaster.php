<?php

namespace App\Filament\Resources\MitraMasterResource\Pages;

use App\Filament\Resources\MitraMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMitraMaster extends EditRecord
{
    protected static string $resource = MitraMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
