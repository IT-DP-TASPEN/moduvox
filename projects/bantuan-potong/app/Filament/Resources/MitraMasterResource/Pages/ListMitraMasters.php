<?php

namespace App\Filament\Resources\MitraMasterResource\Pages;

use App\Filament\Resources\MitraMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMitraMasters extends ListRecords
{
    protected static string $resource = MitraMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
