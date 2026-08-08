<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingTargetResource\Pages;
use App\Models\MarketingTarget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MarketingTargetResource extends Resource
{
    protected static ?string $model = MarketingTarget::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Marketing Target';
    protected static ?string $pluralModelLabel = 'Marketing Target';
    protected static ?string $modelLabel = 'Marketing Target';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Marketing Target Details')
                    ->schema([
                        Forms\Components\Select::make('bulan')
                            ->options([
                                '1' => 'Januari',
                                '2' => 'Februari',
                                '3' => 'Maret',
                                '4' => 'April',
                                '5' => 'Mei',
                                '6' => 'Juni',
                                '7' => 'Juli',
                                '8' => 'Agustus',
                                '9' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ])
                            ->required()
                            ->searchable()
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-calendar'),
                        Forms\Components\TextInput::make('tahun')
                            ->required()
                            ->numeric()
                            ->minValue(2000)
                            ->maxValue(2100)
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-calendar-days'),
                        Forms\Components\TextInput::make('jumlah_hari_kerja')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(31)
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-calendar'),
                        Forms\Components\TextInput::make('nominal_target')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-banknotes'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->withCount('marketingMasters'))
            ->columns([
                Tables\Columns\TextColumn::make('bulan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tahun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_hari_kerja')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nominal_target')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('marketing_masters_count')
                    ->label('Assigned Marketing')
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
            'index' => Pages\ListMarketingTargets::route('/'),
            'create' => Pages\CreateMarketingTarget::route('/create'),
            'view' => Pages\ViewMarketingTarget::route('/{record}'),
            'edit' => Pages\EditMarketingTarget::route('/{record}/edit'),
        ];
    }
}
