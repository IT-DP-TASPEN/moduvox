<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubdistrictResource\Pages;
use App\Models\District;
use App\Models\MasterDati2;
use App\Models\Subdistrict;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubdistrictResource extends Resource
{
    protected static ?string $model = Subdistrict::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Master Subdistrict';
    protected static ?string $pluralModelLabel = 'Master Subdistrict';
    protected static ?string $modelLabel = 'Master Subdistrict';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Subdistrict Details')
                    ->schema([
                        Forms\Components\Select::make('province_id')
                            ->relationship('province', 'nama')
                            ->label('Provinsi')
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->required()
                            ->inlineLabel()
                            ->afterStateUpdated(function ($set) {
                                $set('regency_id', null);
                                $set('district_id', null);
                            }),
                        Forms\Components\Select::make('regency_id')
                            ->label('Kota / Kabupaten')
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->options(function ($get) {
                                $provinceId = $get('province_id');

                                return $provinceId
                                    ? MasterDati2::where('province_id', $provinceId)->pluck('nama', 'id')
                                    : [];
                            })
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->required()
                            ->inlineLabel()
                            ->afterStateUpdated(fn ($set) => $set('district_id', null)),
                        Forms\Components\Select::make('district_id')
                            ->relationship('district', 'nama')
                            ->label('Kecamatan')
                            ->prefixIcon('heroicon-o-map')
                            ->options(function ($get) {
                                $provinceId = $get('province_id');
                                $regencyId = $get('regency_id');

                                return ($provinceId && $regencyId)
                                    ? District::where('province_id', $provinceId)
                                        ->where('regency_id', $regencyId)
                                        ->pluck('nama', 'id')
                                    : [];
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Kelurahan')
                            ->prefixIcon('heroicon-o-map-pin')
                            ->required()
                            ->inlineLabel()
                            ->maxLength(255),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('province.nama')
                    ->label('Provinsi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city.nama')
                    ->label('Kota / Kabupaten')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('district.nama')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Kelurahan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubdistricts::route('/'),
            'create' => Pages\CreateSubdistrict::route('/create'),
            'view' => Pages\ViewSubdistrict::route('/{record}'),
            'edit' => Pages\EditSubdistrict::route('/{record}/edit'),
        ];
    }
}
