<?php

namespace App\Filament\Resources\MasterDati2Resource\Pages;

use App\Filament\Resources\MasterDati2Resource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMasterDati2 extends ViewRecord
{
    protected static string $resource = MasterDati2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
