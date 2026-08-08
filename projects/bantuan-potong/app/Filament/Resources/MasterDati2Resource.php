<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterDati2Resource\Pages;
use App\Filament\Resources\MasterDati2Resource\RelationManagers;
use App\Models\MasterDati2;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterDati2Resource extends Resource
{
    protected static ?string $model = MasterDati2::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-asia-australia';

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Master City';
    protected static ?string $pluralModelLabel = 'Master City';
    protected static ?string $modelLabel = 'Master City';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Kota/Kabupaten Details')
                    ->schema([
                        Forms\Components\Select::make('province_id')
                            ->relationship('province', 'nama')
                            ->label('Provinsi')
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->preload()
                            ->inlineLabel()
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('dati2')
                            ->label('Kode Dati 2')
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->inlineLabel()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Kota/Kabupaten')
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->required()
                            ->inlineLabel()
                            ->maxLength(255),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dati2')
                    ->label('Kode Dati 2')
                    ->searchable(),
                Tables\Columns\TextColumn::make('province.nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Kota/Kanupaten')
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
            'index' => Pages\ListMasterDati2s::route('/'),
            'create' => Pages\CreateMasterDati2::route('/create'),
            'view' => Pages\ViewMasterDati2::route('/{record}'),
            'edit' => Pages\EditMasterDati2::route('/{record}/edit'),
        ];
    }
}
