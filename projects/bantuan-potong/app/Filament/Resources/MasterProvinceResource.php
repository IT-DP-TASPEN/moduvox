<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterProvinceResource\Pages;
use App\Filament\Resources\MasterProvinceResource\RelationManagers;
use App\Models\MasterProvince;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterProvinceResource extends Resource
{
    protected static ?string $model = MasterProvince::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-europe-africa';

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Master Province';
    protected static ?string $pluralModelLabel = 'Master Province';
    protected static ?string $modelLabel = 'Master Province';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Province Details')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->required()
                            ->inlineLabel(),
                    ])
                    ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
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
            'index' => Pages\ListMasterProvinces::route('/'),
            'create' => Pages\CreateMasterProvince::route('/create'),
            'view' => Pages\ViewMasterProvince::route('/{record}'),
            'edit' => Pages\EditMasterProvince::route('/{record}/edit'),
        ];
    }
}
