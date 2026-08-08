<?php

namespace App\Filament\Resources\BranchMasterResource\Pages;

use App\Filament\Resources\BranchMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBranchMasters extends ListRecords
{
    protected static string $resource = BranchMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
