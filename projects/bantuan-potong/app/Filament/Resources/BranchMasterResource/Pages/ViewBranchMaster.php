<?php

namespace App\Filament\Resources\BranchMasterResource\Pages;

use App\Filament\Resources\BranchMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBranchMaster extends ViewRecord
{
    protected static string $resource = BranchMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
