<?php

namespace App\Filament\Resources\PermintaanOpenFlaggingTifResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\PermintaanOpenFlaggingTifResource;

class ViewPermintaanOpenFlaggingTif extends ViewRecord
{
    protected static string $resource = PermintaanOpenFlaggingTifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(function ($record) {
                    $user = Auth::user();

                    // Admin selalu bisa edit
                    if ($user->hasRole(['staff_bosche', 'super_admin'])) {
                        return true;
                    }

                    if ($user->hasRole(['maker_mitra_cabang', 'approval_mitra_cabang']) && $record->status === 'request') {
                        return true;
                    }
                    return false;
                }),
        ];
    }
}
