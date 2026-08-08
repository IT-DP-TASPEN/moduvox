<?php

namespace App\Filament\Resources\DapemMasterResource\Pages;

use App\Filament\Resources\DapemMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDapemMaster extends ViewRecord
{
    protected static string $resource = DapemMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
