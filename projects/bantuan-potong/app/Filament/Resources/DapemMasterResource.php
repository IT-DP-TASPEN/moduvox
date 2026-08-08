<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DapemMasterResource\Pages;
use App\Models\DapemMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DapemMasterResource extends Resource
{
    protected static ?string $model = DapemMaster::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Master Dapem';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make()
                    ->columns(1)
                    ->schema([
                        Forms\Components\TextInput::make('notas')
                            ->inlineLabel()
                            ->label('Notas')
                            ->prefixIcon('heroicon-o-building-library'),
                        Forms\Components\TextInput::make('nama_nasabah')
                            ->inlineLabel()
                            ->label('Nama Nasabah')
                            ->prefixIcon('heroicon-o-user'),
                        Forms\Components\TextInput::make('kantor_bayar')
                            ->inlineLabel()
                            ->label('Kantor Bayar')
                            ->prefixIcon('heroicon-o-building-office'),
                        Forms\Components\TextInput::make('jiwa')
                            ->inlineLabel()
                            ->label('Jiwa')
                            ->prefixIcon('heroicon-o-fire'),
                        Forms\Components\TextInput::make('jenis')
                            ->inlineLabel()
                            ->label('Jenis')
                            ->prefixIcon('heroicon-o-puzzle-piece'),
                        Forms\Components\TextInput::make('nominal_dapem')
                            ->inlineLabel()
                            ->label('Nominal Dapem')
                            ->prefix('Rp')
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : null) // tampil format
                            ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)), // simpan integer,
                        Forms\Components\TextInput::make('rek')
                            ->inlineLabel()
                            ->label('Rekening')
                            ->required()
                            ->live(onBlur: true)
                            ->prefixIcon('heroicon-o-credit-card'),
                        Forms\Components\TextInput::make('bulan_dapem')
                            ->inlineLabel()
                            ->label('Bulan Dapem')
                            ->prefixIcon('heroicon-o-calendar-days'),
                        Forms\Components\TextInput::make('code1')
                            ->label('Tanggal Dapem')
                            ->required()
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-calendar-days'),
                        Forms\Components\TextInput::make('code2')
                            ->label('Kode Otentifikasi')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-command-line'),
                        Forms\Components\TextInput::make('code3')
                            ->label('Kode 3')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-command-line'),
                        Forms\Components\TextInput::make('code4')
                            ->label('Aging')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-command-line'),
                        Forms\Components\Select::make('jenis_dapem')
                            ->options([
                                '0' => 'PENSIUN 13',
                                '1' => 'INDUK',
                                '2' => 'SUSULAN',
                                '4' => 'THR',
                                '7' => 'RAPEL',
                                '8' => 'DAPEM GABUNGAN SUSULAN',
                                '9' => 'DAPEM GABUNGAN INDUK',
                                '10' => 'MODUVOX',

                            ])
                            ->default('1')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-command-line'),
                        Forms\Components\DateTimePicker::make('tanggal_posting')
                            ->default(now())
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-calendar-days'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('notas')
                    ->label('Notas / NIK')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_nasabah')
                    ->searchable()
                    ->label('Nama Nasabah'),
                Tables\Columns\TextColumn::make('kantor_bayar')
                    ->hidden(),
                Tables\Columns\TextColumn::make('jiwa')
                    ->hidden(true),
                Tables\Columns\TextColumn::make('jenis')
                    ->hidden(true),
                Tables\Columns\TextColumn::make('nominal_dapem')
                    ->label('Nominal Dapem')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rek')
                    ->label('Rekening')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bulan_dapem')
                    ->label('Bulan Dapem'),
                Tables\Columns\TextColumn::make('code1')
                    ->hidden(),
                Tables\Columns\TextColumn::make('code2')
                    ->label('Kode Otentifikasi'),
                Tables\Columns\TextColumn::make('code3')
                    ->hidden(),
                Tables\Columns\TextColumn::make('code4')
                    ->hidden(),
                Tables\Columns\TextColumn::make('rek_validasi')
                    ->label('Rek Validasi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rek_validasi_alternate')
                    ->label('Rek Validasi Alternate')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rek_status_validasi')
                    ->label('Status Rekening')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_id')
                    ->label('Customer ID'),
                Tables\Columns\IconColumn::make('result_validasi')
                    ->label('Status Validasi')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        return (
                            ($record->rek_validasi === $record->rek && $record->rek_status_validasi === 'Active') ||
                            ($record->rek_validasi_alternate === $record->rek && $record->rek_status_validasi === 'Active')
                        );
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('jenis_dapem')
                    ->label('Jenis Dapem')
                    ->formatStateUsing(fn($state) => match ($state) {
                        '0' => 'PENSIUN 13',
                        '1' => 'INDUK',
                        '2' => 'SUSULAN',
                        '4' => 'THR',
                        '7' => 'RAPEL',
                        '8' => 'DAPEM GABUNGAN SUSULAN',
                        '9' => 'DAPEM GABUNGAN INDUK',
                        '10' => 'MODUVOX',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('tanggal_posting')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status Payroll')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        '1' => 'Processing',
                        '2' => 'Completed',
                        '3' => 'Failed',
                        default => $state,
                    })
                    ->color(fn($state) => match ($state) {
                        '1' => 'warning',   // kuning
                        '2' => 'success',   // hijau
                        '3' => 'danger',    // merah
                        default => 'gray',  // netral
                    }),
                Tables\Columns\TextColumn::make('created_by')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_by')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListDapemMasters::route('/'),
            'create' => Pages\CreateDapemMaster::route('/create'),
            'view' => Pages\ViewDapemMaster::route('/{record}'),
            'edit' => Pages\EditDapemMaster::route('/{record}/edit'),
        ];
    }
}
