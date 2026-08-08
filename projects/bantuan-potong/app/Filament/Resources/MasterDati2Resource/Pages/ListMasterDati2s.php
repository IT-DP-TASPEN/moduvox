<?php

namespace App\Filament\Resources\MasterDati2Resource\Pages;

use App\Filament\Resources\MasterDati2Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterDati2s extends ListRecords
{
    protected static string $resource = MasterDati2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
