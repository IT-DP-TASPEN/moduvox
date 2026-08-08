<?php

namespace App\Filament\Resources\CekFlaggingNotasResource\Pages;

use App\Filament\Resources\CekFlaggingNotasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCekFlaggingNotas extends EditRecord
{
    protected static string $resource = CekFlaggingNotasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
