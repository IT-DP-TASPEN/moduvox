<?php

namespace App\Filament\Resources\SavingAccountInternalResource\Pages;

use App\Filament\Resources\SavingAccountInternalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewSavingAccountInternal extends ViewRecord
{
    protected static string $resource = SavingAccountInternalResource::class;

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

                    if ($user->hasRole(['open_table', 'admin_support', 'abm']) && $record->status === 'request') {
                        return true;
                    }
                    return false;
                }),
        ];
    }
}
