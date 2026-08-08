<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountPerpouseMasterResource\Pages;
use App\Filament\Resources\AccountPerpouseMasterResource\RelationManagers;
use App\Models\AccountPerpouseMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountPerpouseMasterResource extends Resource
{
    protected static ?string $model = AccountPerpouseMaster::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Tujuan Pembukaan Rekening';
    protected static ?string $pluralModelLabel = 'Tujuan Pembukaan Rekening';
    protected static ?string $modelLabel = 'Tujuan Pembukaan Rekening';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Saving Perpouse')
                    ->schema([
                        Forms\Components\TextInput::make('perpouse_name')
                            ->label(label: 'Tujuan Pembukaan Rekening')
                            ->required()
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-clipboard-document-list')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('perpouse_name')
                    ->label(label: 'Tujuan Pembukaan Rekening')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountPerpouseMasters::route('/'),
            'create' => Pages\CreateAccountPerpouseMaster::route('/create'),
            'view' => Pages\ViewAccountPerpouseMaster::route('/{record}'),
            'edit' => Pages\EditAccountPerpouseMaster::route('/{record}/edit'),
        ];
    }
}
