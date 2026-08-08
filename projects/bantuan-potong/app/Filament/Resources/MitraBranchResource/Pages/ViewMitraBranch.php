<?php

namespace App\Filament\Resources\MitraBranchResource\Pages;

use App\Filament\Resources\MitraBranchResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMitraBranch extends ViewRecord
{
    protected static string $resource = MitraBranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
