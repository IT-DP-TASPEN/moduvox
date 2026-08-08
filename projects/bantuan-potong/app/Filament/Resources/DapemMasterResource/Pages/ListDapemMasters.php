<?php

namespace App\Filament\Resources\DapemMasterResource\Pages;

use App\Filament\Resources\DapemMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDapemMasters extends ListRecords
{
    protected static string $resource = DapemMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
