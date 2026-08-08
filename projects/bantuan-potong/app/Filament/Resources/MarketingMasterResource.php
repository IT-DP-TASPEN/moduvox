<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingMasterResource\Pages;
use App\Filament\Resources\MarketingMasterResource\RelationManagers;
use App\Models\MarketingMaster;
use App\Models\MarketingTarget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MarketingMasterResource extends Resource
{
    protected static ?string $model = MarketingMaster::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Marketing Master';
    protected static ?string $pluralModelLabel = 'Marketing Master';
    protected static ?string $modelLabel = 'Marketing Master';

    public static function jabatanPrefixes(): array
    {
        return [
            'Account Officer' => 'AO',
            'Marketing Staff' => 'MS',
            'Marketing Kontrak Khusus' => 'MKK',
            'Agent' => 'AGEN',
            'Lainnya' => 'OTHR',
        ];
    }

    public static function generateNip(string $jabatan): ?string
    {
        $prefix = self::jabatanPrefixes()[$jabatan] ?? null;

        if (! $prefix) {
            return null;
        }

        $lastNumber = MarketingMaster::query()
            ->where('nip', 'like', $prefix.'%')
            ->pluck('nip')
            ->map(fn ($nip) => preg_match('/^'.preg_quote($prefix, '/').'(\d{6})$/', $nip, $matches) ? (int) $matches[1] : 0)
            ->max();

        return $prefix.str_pad(((int) $lastNumber) + 1, 6, '0', STR_PAD_LEFT);
    }

    public static function isGeneratedNip(?string $nip): bool
    {
        $prefixes = implode('|', array_map(fn ($prefix) => preg_quote($prefix, '/'), self::jabatanPrefixes()));

        return (bool) preg_match('/^('.$prefixes.')\d{6}$/', (string) $nip);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Marketing Details')
                    ->schema([
                        Forms\Components\TextInput::make('nip')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-identification')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('marketing_name')
                            ->required()
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-user-group')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('jabatan')
                            ->required()
                            ->options(array_combine(array_keys(self::jabatanPrefixes()), array_keys(self::jabatanPrefixes())))
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($get, $set, $state) {
                                $nip = $get('nip');

                                if ($state && (! $nip || self::isGeneratedNip($nip))) {
                                    $set('nip', self::generateNip($state));
                                }
                            })
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-briefcase')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('branch_master_id')
                            ->label('Branch PT Moduvox Tech ID')
                            ->relationship('branchMaster', 'branch_name')
                            ->required()
                            ->inlineLabel()
                            ->preload()
                            ->searchable()
                            ->prefixIcon('heroicon-o-map-pin')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('marketing_target_id')
                            ->label('Marketing Target')
                            ->relationship('marketingTarget', 'bulan')
                            ->getOptionLabelFromRecordUsing(fn (MarketingTarget $record) => $record->label)
                            ->inlineLabel()
                            ->preload()
                            ->searchable()
                            ->prefixIcon('heroicon-o-chart-bar')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('lokasi_open_table')
                            ->required()
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-map-pin')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('jenis_marketing')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->options([
                                'Pegawai Moduvox' => 'Pegawai Moduvox',
                                'Pegawai PKS' => 'Pegawai PKS',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-document-text')
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
                Tables\Columns\TextColumn::make('nip')
                    ->searchable(),
                Tables\Columns\TextColumn::make('marketing_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('branchMaster.branch_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('marketingTarget.nominal_target')
                    ->label('Nominal Target')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lokasi_open_table')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_marketing')
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
                    Tables\Actions\BulkAction::make('assign_target')
                        ->label('Assign Target')
                        ->icon('heroicon-o-chart-bar')
                        ->form([
                            Forms\Components\Select::make('marketing_target_id')
                                ->label('Marketing Target')
                                ->options(fn () => MarketingTarget::query()
                                    ->orderByDesc('tahun')
                                    ->get()
                                    ->pluck('label', 'id'))
                                ->required()
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(fn ($records, array $data) => $records->each->update([
                            'marketing_target_id' => $data['marketing_target_id'],
                        ]))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Marketing target assigned'),
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
            'index' => Pages\ListMarketingMasters::route('/'),
            'create' => Pages\CreateMarketingMaster::route('/create'),
            'view' => Pages\ViewMarketingMaster::route('/{record}'),
            'edit' => Pages\EditMarketingMaster::route('/{record}/edit'),
        ];
    }
}
