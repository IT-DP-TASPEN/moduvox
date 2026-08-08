<?php

namespace App\Filament\Resources\MitraBranchResource\Pages;

use App\Filament\Resources\MitraBranchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMitraBranches extends ListRecords
{
    protected static string $resource = MitraBranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
