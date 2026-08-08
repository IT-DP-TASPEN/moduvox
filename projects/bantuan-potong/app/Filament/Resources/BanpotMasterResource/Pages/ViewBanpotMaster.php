<?php

namespace App\Filament\Resources\BanpotMasterResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\BanpotMasterResource;

class ViewBanpotMaster extends ViewRecord
{
    protected static string $resource = BanpotMasterResource::class;

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

                    if ($user->hasRole(['maker_mitra_pusat', 'approval_mitra_pusat']) && $record->status_banpot === 'request') {
                        return true;
                    }
                    return false;
                }),
        ];
    }
}