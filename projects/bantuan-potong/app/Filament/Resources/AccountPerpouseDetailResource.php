<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountPerpouseDetailResource\Pages;
use App\Filament\Resources\AccountPerpouseDetailResource\RelationManagers;
use App\Models\AccountPerpouseDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountPerpouseDetailResource extends Resource
{
    protected static ?string $model = AccountPerpouseDetail::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Tujuan Pembukaan Rekening Detail';
    protected static ?string $pluralModelLabel = 'Tujuan Pembukaan Rekening Detail';
    protected static ?string $modelLabel = 'Tujuan Pembukaan Rekening Detail';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Saving Perpouse Details')
                    ->schema([
                        Forms\Components\Select::make('account_perpouse_master_id')
                            ->label(label: 'Tujuan Pembukaan Rekening')
                            ->required()
                            ->relationship('accountPerpouseMaster', 'perpouse_name')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-clipboard-document-list')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('detail_name')
                            ->label(label: 'Detail Tujuan Pembukaan Rekening')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-clipboard-document-list')
                            ->required()
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('accountPerpouseMaster.perpouse_name')
                    ->label(label: 'Tujuan Pembukaan Rekening')
                    ->searchable(),
                Tables\Columns\TextColumn::make('detail_name')
                    ->label(label: 'Detail Tujuan Pembukaan Rekening')
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
            'index' => Pages\ListAccountPerpouseDetails::route('/'),
            'create' => Pages\CreateAccountPerpouseDetail::route('/create'),
            'view' => Pages\ViewAccountPerpouseDetail::route('/{record}'),
            'edit' => Pages\EditAccountPerpouseDetail::route('/{record}/edit'),
        ];
    }
}
