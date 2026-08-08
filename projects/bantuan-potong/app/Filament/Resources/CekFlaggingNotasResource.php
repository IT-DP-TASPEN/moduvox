<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CekFlaggingNotasResource\Pages;
use App\Models\CekFlaggingNotas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CekFlaggingNotasResource extends Resource
{
    protected static ?string $model = CekFlaggingNotas::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationGroup = 'Layanan';
    protected static ?string $navigationLabel = 'Cek Notas Ownership';
    protected static ?string $pluralModelLabel = 'Cek Notas Ownership';
    protected static ?string $modelLabel = 'Cek Notas Ownership';




    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mitra_master_id')
                ->relationship('mitra', 'nama_mitra')
                ->required(),

            Forms\Components\TextInput::make('notas')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('nama_nasabah')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('rek_tabungan')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('rek_replace')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mitra.nama_mitra')
                    ->label('Mitra')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notas')
                    ->label('Nomor Notas')
                    ->copyable(),

                Tables\Columns\TextColumn::make('nama_nasabah')
                    ->label('Nama Nasabah'),

                Tables\Columns\TextColumn::make('rek_tabungan')
                    ->label('Rekening Tabungan')
                    ->copyable(),

                Tables\Columns\TextColumn::make('rek_replace')
                    ->label('Rekening Replace')
                    ->copyable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCekFlaggingNotas::route('/'),
            'create' => Pages\CreateCekFlaggingNotas::route('/create'),
            'view' => Pages\ViewCekFlaggingNotas::route('/{record}'),
            'edit' => Pages\EditCekFlaggingNotas::route('/{record}/edit'),
        ];
    }
}
