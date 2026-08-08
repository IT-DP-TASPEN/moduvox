<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotasOwnershipResource\Pages;
use App\Models\NotasOwnership;
use App\Models\MitraMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotasOwnershipResource extends Resource
{
    protected static ?string $model = NotasOwnership::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Master Notas Ownership';
    protected static ?string $pluralModelLabel = 'Master Notas Ownership';
    protected static ?string $modelLabel = 'Master Notas Ownership';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Mitra')
                ->schema([
                    Forms\Components\Select::make('mitra_master_id')
                        ->label('Mitra')
                        ->inlineLabel()
                        ->preload()
                        ->prefixIcon('heroicon-o-building-office')
                        ->relationship('mitra', 'nama_mitra')
                        ->searchable()
                        ->required(),
                ])
                ->columns(1),

            Forms\Components\Section::make('Detail Notas')
                ->schema([
                    Forms\Components\TextInput::make('notas')
                        ->label('Nomor Notas')
                        ->prefixIcon('heroicon-o-building-office')
                        ->inlineLabel()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('nama_nasabah')
                        ->label('Nama Nasabah')
                        ->prefixIcon('heroicon-o-identification')
                        ->inlineLabel()
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('rek_tabungan')
                        ->label('Rekening Tabungan')
                        ->prefixIcon('heroicon-o-credit-card')
                        ->inlineLabel()
                        ->required(),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('mitra.nama_mitra')
                ->label('Mitra')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('notas')
                ->label('Nomor Notas')
                ->copyable()
                ->searchable(),

            Tables\Columns\TextColumn::make('nama_nasabah')
                ->label('Nama Nasabah')
                ->searchable(),

            Tables\Columns\TextColumn::make('rek_tabungan')
                ->label('Rekening Tabungan')
                ->copyable()
                ->searchable(),

            Tables\Columns\TextColumn::make('rek_replace')
                ->label('Rekening Replace')
                ->copyable(),
        ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
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
            'index' => Pages\ListNotasOwnerships::route('/'),
            'create' => Pages\CreateNotasOwnership::route('/create'),
            'view' => Pages\ViewNotasOwnership::route('/{record}'),
            'edit' => Pages\EditNotasOwnership::route('/{record}/edit'),
        ];
    }
}