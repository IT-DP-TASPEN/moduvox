<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MitraMasterResource\Pages;
use App\Models\MitraMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MitraMasterResource extends Resource
{
    protected static ?string $model = MitraMaster::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Master Mitra';
    protected static ?string $pluralModelLabel = 'Master Mitra';
    protected static ?string $modelLabel = 'Master Mitra';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Mitra')
                ->schema([
                    Forms\Components\TextInput::make('id')
                        ->label('Mitra ID')
                        ->inlineLabel()
                        ->hiddenOn('create')
                        ->disabledOn('edit')
                        ->dehydrated(true)
                        ->prefixIcon('heroicon-o-identification')
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('nama_mitra')
                        ->label('Nama Mitra')
                        ->inlineLabel()
                        ->prefixIcon('heroicon-o-identification')
                        ->required()
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Forms\Components\Section::make('Biaya Bantuan Potong')
                ->schema([
                    Forms\Components\Select::make('jenis_fee_banpot')
                        ->label('Jenis Fee Banpot')
                        ->inlineLabel()
                        ->prefixIcon('heroicon-o-bars-arrow-down')
                        ->options([
                            '1' => 'Dapem',
                            '2' => 'Tagihan',
                            '3' => 'Dapem-Saldo Mengendap',
                        ])
                        ->required(),
                    Forms\Components\Select::make('jenis_pinbuk')
                        ->label('Jenis Pinbuk')
                        ->inlineLabel()
                        ->prefixIcon('heroicon-o-bars-arrow-down')
                        ->options([
                            '1' => 'Keseluruhan Dapem',
                            '2' => 'Nominal Potongan Angsuran',
                        ])
                        ->required(),

                    self::percentInput('fee_banpot', 'Fee Banpot (%)'),

                    self::rupiahInput('saldo_mengendap', 'Saldo Mengendap (Rp)'),
                ])
                ->columns(1),

            Forms\Components\Section::make('Biaya Moduvox')
                ->schema([
                    self::rupiahInput('biaya_flagging_pensiun', 'Flagging Pensiun'),
                    self::rupiahInput('biaya_flagging_prapen', 'Flagging Prapensiun'),
                    self::rupiahInput('biaya_flagging_tht', 'Flagging THT'),
                    self::rupiahInput('biaya_flagging_prapen_tht', 'Flagging Prapen + THT'),
                    self::rupiahInput('biaya_flagging_mutasi_tif', 'Flagging Mutasi TIF'),
                    self::rupiahInput('biaya_flagging_mutasi_tos', 'Flagging Mutasi TOS'),
                    self::rupiahInput('biaya_checking', 'Biaya Checking'),
                    self::rupiahInput('biaya_check_estimasi', 'Biaya Check Estimasi'),
                ])
                ->columns(1),

            Forms\Components\Section::make('Pajak')
                ->schema([
                    self::percentInput('ppn', 'PPN (%)'),
                    self::percentInput('pph', 'PPH (%)'),
                ])
                ->columns(1),
            Forms\Components\Section::make('Mitra Sinergi')
                ->schema([
                    Forms\Components\Checkbox::make('is_sinergi')
                        ->label('Mitra Sinergi')
                        ->inlineLabel()
                        ->dehydrated(true)
                        ->default(false)
                        ->columnSpanFull(),
                ])
                ->columns(1),
        ]);
    }

    /**
     * Helper component untuk field nominal (Rupiah)
     */
    protected static function rupiahInput(string $name, string $label): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make($name)
            ->label($label)
            ->inlineLabel()
            ->prefix('Rp')
            ->live(onBlur: false)
            ->afterStateUpdated(function ($state, callable $set) use ($name) {
                if (blank($state))
                    return;
                $numericValue = preg_replace('/[^0-9]/', '', $state);
                if ($numericValue !== '') {
                    $formattedValue = number_format((int) $numericValue, 0, ',', '.');
                    $set($name, $formattedValue);
                }
            })
            ->dehydrateStateUsing(fn($state) => preg_replace('/[^0-9]/', '', $state))
            ->formatStateUsing(fn($state) => is_numeric($state)
                ? number_format((int) $state, 0, ',', '.')
                : $state)
            ->required();
    }

    /**
     * Helper component untuk field persentase (%)
     */
    protected static function percentInput(string $name, string $label): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make($name)
            ->label($label)
            ->inlineLabel()
            ->suffix('%')
            ->inputMode('decimal')
            ->live(onBlur: true)
            ->afterStateUpdated(function ($state, callable $set) use ($name) {
                if (blank($state))
                    return;
                $numeric = str_replace(',', '.', preg_replace('/[^0-9,\.]/', '', $state));
                $formatted = number_format((float) $numeric, 2, ',', '');
                $set($name, $formatted);
            })
            ->dehydrateStateUsing(function ($state) {
                return $state ? (float) str_replace(',', '.', preg_replace('/[^0-9,\.]/', '', $state)) : null;
            })
            ->rules(['between:0,100'])
            ->required();
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')
                ->label('Mitra ID')
                ->searchable(),
            Tables\Columns\TextColumn::make('nama_mitra')
                ->label('Nama Mitra')
                ->searchable(),

            Tables\Columns\TextColumn::make('jenis_fee_banpot')
                ->label('Jenis Fee Banpot')
                ->formatStateUsing(fn($state) => $state === '1' ? 'Dapem' : ($state === '2' ? 'Tagihan' : 'Dapem-Saldo Mengendap')),

            Tables\Columns\TextColumn::make('jenis_pinbuk')
                ->label('Jenis Pinbuk')
                ->formatStateUsing(fn($state) => $state === '1' ? 'Keselurahan Dapem' : 'Nominal Potongan Angsuran'),

            Tables\Columns\TextColumn::make('fee_banpot')
                ->label('Fee Banpot (%)')
                ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.') . ' %')
                ->sortable(),

            Tables\Columns\TextColumn::make('saldo_mengendap')
                ->label('Saldo Mengendap')
                ->money('IDR', locale: 'id')
                ->sortable(),

            Tables\Columns\TextColumn::make('biaya_checking')
                ->label('Biaya Checking')
                ->money('IDR', locale: 'id')
                ->sortable(),

            Tables\Columns\TextColumn::make('biaya_check_estimasi')
                ->label('Biaya Check Estimasi')
                ->money('IDR', locale: 'id')
                ->sortable(),

            Tables\Columns\TextColumn::make('biaya_flagging_pensiun')
                ->label('Flagging Pensiun')
                ->money('IDR', locale: 'id')
                ->sortable(),

            Tables\Columns\TextColumn::make('biaya_flagging_prapen')
                ->label('Flagging Prapensiun')
                ->money('IDR', locale: 'id')
                ->sortable(),

            Tables\Columns\TextColumn::make('biaya_flagging_tht')
                ->label('Flagging THT')
                ->money('IDR', locale: 'id')
                ->sortable(),

            Tables\Columns\TextColumn::make('biaya_flagging_prapen_tht')
                ->label('Flagging Prapen + THT')
                ->money('IDR', locale: 'id')
                ->sortable(),

            Tables\Columns\TextColumn::make('biaya_flagging_mutasi_tif')
                ->label('Flagging Mutasi TIF')
                ->money('IDR', locale: 'id')
                ->sortable(),

            Tables\Columns\TextColumn::make('biaya_flagging_mutasi_tos')
                ->label('Flagging Mutasi TOS')
                ->money('IDR', locale: 'id')
                ->sortable(),

            Tables\Columns\TextColumn::make('ppn')
                ->label('PPN (%)')
                ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.') . ' %')
                ->sortable(),

            Tables\Columns\TextColumn::make('pph')
                ->label('PPH (%)')
                ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.') . ' %')
                ->sortable(),

            Tables\Columns\IconColumn::make('is_sinergi')
                ->label('Mitra Sinergi')
                ->boolean()
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->trueColor('success')
                ->falseColor('danger')
                ->sortable(),
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
            'index' => Pages\ListMitraMasters::route('/'),
            'create' => Pages\CreateMitraMaster::route('/create'),
            'view' => Pages\ViewMitraMaster::route('/{record}'),
            'edit' => Pages\EditMitraMaster::route('/{record}/edit'),
        ];
    }
}
