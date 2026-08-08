<?php

namespace App\Filament\Resources\MitraBranchResource\Pages;

use App\Filament\Resources\MitraBranchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMitraBranch extends EditRecord
{
    protected static string $resource = MitraBranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
