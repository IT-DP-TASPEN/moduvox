<?php

namespace App\Filament\Resources\BranchMasterResource\Pages;

use App\Filament\Resources\BranchMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBranchMaster extends EditRecord
{
    protected static string $resource = BranchMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
