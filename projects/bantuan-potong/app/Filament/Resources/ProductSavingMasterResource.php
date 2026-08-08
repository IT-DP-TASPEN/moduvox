<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductSavingMasterResource\Pages;
use App\Filament\Resources\ProductSavingMasterResource\RelationManagers;
use App\Models\ProductSavingMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductSavingMasterResource extends Resource
{
    protected static ?string $model = ProductSavingMaster::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Master Product Saving';
    protected static ?string $pluralModelLabel = 'Master Product Saving';
    protected static ?string $modelLabel = 'Master Product Saving';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Saving Details')
                    ->schema([
                        Forms\Components\TextInput::make('product_code')
                            ->required()
                            ->prefixIcon('heroicon-o-wallet')
                            ->inlineLabel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('product_name')
                            ->required()
                            ->prefixIcon('heroicon-o-wallet')
                            ->inlineLabel()
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_name')
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
            'index' => Pages\ListProductSavingMasters::route('/'),
            'create' => Pages\CreateProductSavingMaster::route('/create'),
            'view' => Pages\ViewProductSavingMaster::route('/{record}'),
            'edit' => Pages\EditProductSavingMaster::route('/{record}/edit'),
        ];
    }
}
