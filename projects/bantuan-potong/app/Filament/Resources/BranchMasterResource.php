<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchMasterResource\Pages;
use App\Filament\Resources\BranchMasterResource\RelationManagers;
use App\Models\BranchMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BranchMasterResource extends Resource
{
    protected static ?string $model = BranchMaster::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Master Branch PT Moduvox Tech ID';
    protected static ?string $pluralModelLabel = 'Master Branch PT Moduvox Tech ID';
    protected static ?string $modelLabel = 'Master Branch PT Moduvox Tech ID';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Branch Details')
                    ->schema([
                        Forms\Components\Select::make('mitra_master_id')
                            ->label('Mitra Induk')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-building-office')
                            ->relationship('mitraMaster', 'nama_mitra')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('branch_code')
                            ->required()
                            ->prefixIcon('heroicon-o-building-office-2')
                            ->inlineLabel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('branch_name')
                            ->required()
                            ->prefixIcon('heroicon-o-building-office-2')
                            ->inlineLabel()
                            ->columnSpanFull(),
                        Forms\Components\Checkbox::make('is_active')
                            ->label('Active')
                            ->inlineLabel()
                            ->default(true)
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mitraMaster.nama_mitra')
                    ->label('Mitra Induk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('branch_name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
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
            'index' => Pages\ListBranchMasters::route('/'),
            'create' => Pages\CreateBranchMaster::route('/create'),
            'view' => Pages\ViewBranchMaster::route('/{record}'),
            'edit' => Pages\EditBranchMaster::route('/{record}/edit'),
        ];
    }
}
