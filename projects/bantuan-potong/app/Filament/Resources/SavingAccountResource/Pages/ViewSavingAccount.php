<?php

namespace App\Filament\Resources\SavingAccountResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\SavingAccountResource;

class ViewSavingAccount extends ViewRecord
{
    protected static string $resource = SavingAccountResource::class;

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
