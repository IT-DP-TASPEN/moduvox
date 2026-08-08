<?php

namespace App\Filament\Resources\DapemMasterResource\Pages;

use App\Filament\Resources\DapemMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDapemMaster extends EditRecord
{
    protected static string $resource = DapemMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
