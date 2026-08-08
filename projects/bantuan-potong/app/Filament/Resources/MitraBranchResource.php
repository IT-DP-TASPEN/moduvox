<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MitraBranchResource\Pages;
use App\Models\MitraBranch;
use App\Models\MitraMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MitraBranchResource extends Resource
{
    protected static ?string $model = MitraBranch::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Master Branch Mitra';
    protected static ?string $pluralModelLabel = 'Master Branch Mitra';
    protected static ?string $modelLabel = 'Master Branch Mitra';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Cabang Mitra')
                ->schema([
                    Forms\Components\Select::make('mitra_master_id')
                        ->label('Mitra Induk')
                        ->inlineLabel()
                        ->prefixIcon('heroicon-o-building-office')
                        ->relationship('mitraMaster', 'nama_mitra')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('nama_cabang')
                        ->label('Nama Cabang')
                        ->prefixIcon('heroicon-o-building-office-2')
                        ->inlineLabel()
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('mitraMaster.nama_mitra')
                ->label('Mitra Induk')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('nama_cabang')
                ->label('Nama Cabang')
                ->searchable(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMitraBranches::route('/'),
            'create' => Pages\CreateMitraBranch::route('/create'),
            'view' => Pages\ViewMitraBranch::route('/{record}'),
            'edit' => Pages\EditMitraBranch::route('/{record}/edit'),
        ];
    }
}