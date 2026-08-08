<?php

namespace App\Filament\Resources\MasterDati2Resource\Pages;

use App\Filament\Resources\MasterDati2Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterDati2 extends EditRecord
{
    protected static string $resource = MasterDati2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
