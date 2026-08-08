<?php

namespace App\Filament\Resources;

use stdClass;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\BanpotMaster;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BanpotMasterResource\Pages;

class BanpotMasterResource extends Resource
{
    protected static ?string $model = BanpotMaster::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Layanan';
    protected static ?string $navigationLabel = 'Permintaan Bantuan Potong';
    protected static ?string $pluralModelLabel = 'Permintaan Bantuan Potong';
    protected static ?string $modelLabel = 'Permintaan Bantuan Potong';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $readonlyForApproval = $user->hasRole(['approval_mitra_pusat', 'staff_bosche']);

        return $form->schema([
            Forms\Components\Section::make('Informasi Nasabah')
                ->schema([
                    Forms\Components\TextInput::make('rek_tabungan')
                        ->label('Nomor Rekening Nasabah')
                        ->prefixIcon('heroicon-o-credit-card')
                        ->inlineLabel()
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true)
                        ->maxLength(255),
                    Forms\Components\TextInput::make('nama_nasabah')
                        ->label('Nama Nasabah')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true)
                        ->prefixIcon('heroicon-o-identification')
                        ->inlineLabel()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('notas')
                        ->label('Nomor Notas')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true)
                        ->prefixIcon('heroicon-o-building-office')
                        ->inlineLabel()
                        ->maxLength(255),
                    Forms\Components\Select::make('bulan')
                        ->label('Bulan')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true)
                        ->prefixIcon('heroicon-o-calendar')
                        ->options([
                            '01' => 'Januari',
                            '02' => 'Februari',
                            '03' => 'Maret',
                            '04' => 'April',
                            '05' => 'Mei',
                            '06' => 'Juni',
                            '07' => 'Juli',
                            '08' => 'Agustus',
                            '09' => 'September',
                            '10' => 'Oktober',
                            '11' => 'November',
                            '12' => 'Desember',
                        ])
                        ->default(now()->format('m'))
                        ->reactive()
                        ->afterStateUpdated(
                            fn(callable $set, $get) =>
                            $set('bulan_dapem', $get('tahun') . $get('bulan') . '01')
                        )
                        ->afterStateHydrated(function ($state, callable $set, $get) {
                            // ambil dari bulan_dapem kalau ada
                            if ($bulanDapem = $get('bulan_dapem')) {
                                $set('bulan', substr($bulanDapem, 4, 2)); // ambil 2 digit bulan
                            }
                        })
                        ->required()
                        ->dehydrated(false)
                        ->inlineLabel(),

                    Forms\Components\Select::make('tahun')
                        ->label('Tahun')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true)
                        ->prefixIcon('heroicon-o-calendar')
                        ->dehydrated(false)
                        ->options(function () {
                            $year = now()->year;
                            return [
                                $year - 1 => $year - 1,
                                $year => $year,
                                $year + 1 => $year + 1,
                            ];
                        })
                        ->default(now()->year)
                        ->reactive()
                        ->afterStateUpdated(
                            fn(callable $set, $get) =>
                            $set('bulan_dapem', $get('tahun') . $get('bulan') . '01')
                        )
                        ->afterStateHydrated(function ($state, callable $set, $get) {
                            if ($bulanDapem = $get('bulan_dapem')) {
                                $set('tahun', substr($bulanDapem, 0, 4)); // ambil 4 digit tahun
                            }
                        })
                        ->required()
                        ->inlineLabel(),

                    Forms\Components\Hidden::make('bulan_dapem')
                        ->label('Bulan Dapem')
                        ->disabled()
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true)
                        ->reactive()
                        ->inlineLabel()
                        ->default(now()->format('Ym01'))
                        ->required(),

                ])
                ->columns(1),

            Forms\Components\Section::make('Detail Kredit')
                ->schema([
                    Forms\Components\TextInput::make('rek_kredit')->label('Nomor Rekening Mitra')->inlineLabel()->maxLength(255)->prefixIcon('heroicon-o-credit-card')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true),
                    Forms\Components\TextInput::make('tenor')->label('Tenor')->inlineLabel()->maxLength(255)->prefixIcon('heroicon-o-calendar')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true),
                    Forms\Components\TextInput::make('angsuran_ke')->label('Angsuran Ke')->inlineLabel()->maxLength(255)->prefixIcon('heroicon-o-calendar')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true),
                    Forms\Components\DatePicker::make('tat_kredit')->label('TAT Kredit')->inlineLabel()->default(now())->prefixIcon('heroicon-o-calendar')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true),
                    Forms\Components\DatePicker::make('tmt_kredit')->label('TMT Kredit')->inlineLabel()->default(now())->prefixIcon('heroicon-o-calendar')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true),
                    self::rupiahInput('gaji_pensiun', 'Gaji Pensiun')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true),
                    self::rupiahInput('nominal_potongan', 'Nominal Potongan')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true),
                    self::rupiahInput('saldo_mengendap', 'Saldo Mengendap')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true)
                        ->hiddenOn('create')
                        ->disabledOn('edit'),
                    self::rupiahInput('jumlah_tertagih', 'Jumlah Tertagih')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true)
                        ->hiddenOn('create')
                        ->disabledOn('edit'),
                    Forms\Components\TextInput::make('bank_transfer')->label('Bank Transfer')->inlineLabel()->maxLength(255)->prefixIcon('heroicon-o-building-office')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true),
                    Forms\Components\TextInput::make('rek_transfer')->label('Rekening Transfer')->inlineLabel()->maxLength(255)->prefixIcon('heroicon-o-credit-card')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true),
                    self::rupiahInput('fee_banpot', 'Fee Banpot')
                        ->disabled(fn() => $readonlyForApproval)
                        ->dehydrated(true)
                        ->hiddenOn('create')
                        ->disabledOn('edit'),
                ])
                ->columns(1),

            Forms\Components\Section::make('Validasi')
                ->schema([
                    Forms\Components\Toggle::make('notas_valid')->label('Valid Notas')->disabled(),
                    Forms\Components\Toggle::make('rek_tabungan_valid')->label('Valid Rek Tabungan')->disabled(),
                    Forms\Components\Toggle::make('dapem_valid')->label('Valid Dapem')->disabled(),
                    Forms\Components\Toggle::make('oten_valid')->label('Valid Oten')->disabled(),
                    Forms\Components\Toggle::make('final_validasi_status')->label('Final Validasi Status')->disabled(),
                    Forms\Components\Textarea::make('keterangan_2')
                        ->label('')
                        ->disabled()
                        ->dehydrated(true)
                        ->columnSpanFull(),
                ])
                ->columns(5),
            Forms\Components\Section::make('Keterangan')
                ->schema([
                    Forms\Components\Textarea::make('keterangan')
                        ->label('')
                        ->columnSpanFull(),
                ])
                ->columns(1),
            Forms\Components\Section::make('Status')
                ->schema([
                    Forms\Components\Select::make('status_banpot')
                        ->label('Status Banpot')
                        ->inlineLabel()
                        ->prefixIcon('heroicon-o-bars-arrow-down')
                        ->dehydrated()
                        ->options(function (callable $get) {
                            $user = Auth::user();
                            $current = $get('status_banpot') ?? 'request';

                            if ($user->hasRole(['super_admin', 'staff_bosche'])) {
                                $base = [
                                    'request' => 'Request',
                                    'approved_mitra' => 'Approved Mitra',
                                    'rejected_mitra' => 'Rejected Mitra',
                                    'canceled' => 'Canceled',
                                    'on_process' => 'On Process',
                                    'success' => 'Success',
                                    'failed' => 'Failed',
                                    'complete' => 'Complete',
                                ];
                            } elseif ($user->hasRole('approval_mitra_pusat')) {
                                $base = [
                                    'request' => 'Request',
                                    'approved_mitra' => 'Approved Mitra',
                                    'rejected_mitra' => 'Rejected Mitra',
                                ];
                            } elseif ($user->hasRole('maker_mitra_pusat')) {
                                $base = ['request' => 'Request'];
                            } else {
                                $base = [];
                            }

                            // Tambahkan status saat ini jika tidak ada di base
                            if ($current && !array_key_exists($current, $base)) {
                                $base[$current] = ucfirst(str_replace('_', ' ', $current));
                            }

                            return $base;
                        })
                        ->default('request')
                        ->disabled(function (callable $get) {
                            $user = Auth::user();

                            if ($user->hasRole(['super_admin', 'staff_bosche'])) {
                                return false;
                            }

                            if ($user->hasRole('approval_mitra_pusat')) {
                                return false;
                            }

                            // Maker Mitra Pusat atau role lain tidak bisa ubah
                            return true;
                        }),

                    Forms\Components\Hidden::make('created_by')
                        ->default(fn() => auth()->user()?->id)
                        ->disabled()
                        ->dehydrated(true),

                    Forms\Components\TextInput::make('created_mitra')
                        ->label('Dibuat')
                        ->inlineLabel()
                        ->prefixIcon('heroicon-o-building-office')
                        ->default(fn() => auth()->user()?->mitraMaster?->nama_mitra)
                        ->disabled()
                        ->dehydrated(true),
                ])
                ->columns(1),

        ]);
    }

    protected static function rupiahInput(string $name, string $label): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make($name)
            ->label($label)
            ->inlineLabel()
            ->prefix('Rp')
            ->live(onBlur: false)
            ->afterStateUpdated(function ($state, callable $set) use ($name) {
                if (blank($state)) return;
                $numericValue = preg_replace('/[^0-9]/', '', $state);
                if ($numericValue !== '') {
                    $formattedValue = number_format((int)$numericValue, 0, ',', '.');
                    $set($name, $formattedValue);
                }
            })
            ->dehydrateStateUsing(fn($state) => preg_replace('/[^0-9]/', '', $state))
            ->formatStateUsing(fn($state) => is_numeric($state)
                ? number_format((int)$state, 0, ',', '.')
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
                if (blank($state)) return;
                $numeric = str_replace(',', '.', preg_replace('/[^0-9,\.]/', '', $state));
                $formatted = number_format((float)$numeric, 2, ',', '');
                $set($name, $formatted);
            })
            ->dehydrateStateUsing(function ($state) {
                return $state ? (float)str_replace(',', '.', preg_replace('/[^0-9,\.]/', '', $state)) : null;
            })
            ->rules(['between:0,100'])
            ->required();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rek_tabungan')
                    ->label('Nomor Rekening Nasabah'),
                Tables\Columns\TextColumn::make('nama_nasabah')->label('Nama Nasabah'),
                Tables\Columns\TextColumn::make('notas')->label('Notas'),
                Tables\Columns\TextColumn::make('rek_kredit')->label('Nomor Rekening Mitra'),
                Tables\Columns\TextColumn::make('tenor')->label('Tenor'),
                Tables\Columns\TextColumn::make('angsuran_ke')->label('Angsuran Ke'),
                Tables\Columns\TextColumn::make('tmt_kredit')->label('TMT Kredit')
                    ->dateTime('d F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tat_kredit')->label('TAT Kredit')->dateTime('d F Y')->sortable(),
                Tables\Columns\TextColumn::make('gaji_pensiun')->label('Gaji Pensiun')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('nominal_potongan')->label('Nominal Potongan')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('saldo_mengendap')->label('Saldo Mengendap')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('jumlah_tertagih')->label('Jumlah Tertagih')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('sisa_gaji')->label('Sisa Gaji')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('fee_banpot')->label('Fee Banpot')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('bank_transfer')->label('Bank Transfer'),
                Tables\Columns\TextColumn::make('rek_transfer')->label('Rekening Transfer'),
                Tables\Columns\IconColumn::make('notas_valid')->label('Valid Notas')->boolean(),
                Tables\Columns\IconColumn::make('rek_tabungan_valid')->label('Valid Rek Tabungan')->boolean(),
                Tables\Columns\IconColumn::make('dapem_valid')->label('Valid Dapem')->boolean(),
                Tables\Columns\IconColumn::make('oten_valid')->label('Valid Oten')->boolean(),
                Tables\Columns\IconColumn::make('final_validasi_status')->label('Final Validasi Status')->boolean(),
                Tables\Columns\TextColumn::make('bulan_dapem')
                    ->label('Bulan Dapem')
                    ->formatStateUsing(function ($state) {
                        try {
                            return strtoupper(
                                Carbon::createFromFormat('Ymd', $state)
                                    ->locale('id')               // 👈 paksa bahasa Indonesia di sini
                                    ->translatedFormat('F Y')    // Oktober 2025
                            );
                        } catch (\Exception $e) {
                            return $state;
                        }
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')->label('Keteragan'),
                Tables\Columns\TextColumn::make('keterangan_2')->label('Keteragan System')->badge(),
                Tables\Columns\BadgeColumn::make('status_banpot')
                    ->label('Status')
                    ->colors([
                        'primary' => 'request',
                        'success' => 'approved_mitra',
                        'danger' => 'rejected_mitra',
                        'secondary' => 'canceled',
                        'info' => 'on_process',
                        'success' => 'success',
                        'danger' => 'failed',
                        'success' => 'complete',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'request' => 'Request',
                        'approved_mitra' => 'Approved Mitra',
                        'rejected_mitra' => 'Rejected Mitra',
                        'canceled' => 'Canceled',
                        'on_process' => 'On Process',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'complete' => 'Complete',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('creator.name')->label('Dibuat Oleh'),
                Tables\Columns\TextColumn::make('created_mitra')->label('Nama Mitra')
                    ->placeholder('Tidak diatur'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d F Y  H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Diubah')
                    ->dateTime('d F Y  H:i')
                    ->sortable(),
            ])
            // ->filters([
            //     Tables\Filters\SelectFilter::make('created_mitra')
            //         ->label('Nama Mitra')
            //         ->multiple()
            //         ->options(
            //             BanpotMaster::query()
            //                 ->select('created_mitra')
            //                 ->whereNotNull('created_mitra')
            //                 ->distinct()
            //                 ->orderBy('created_mitra')
            //                 ->pluck('created_mitra', 'created_mitra')
            //                 ->toArray()
            //         )
            //         ->visible(fn() => Auth::user()->hasRole(['staff_bosche', 'super_admin'])),

            //     Tables\Filters\SelectFilter::make('final_validasi_status')
            //         ->label('Final Validasi Status')
            //         ->multiple()
            //         ->options([
            //             1 => 'Validasi Sesuai',
            //             0 => 'Validasi Tidak Sesuai',
            //         ]),
            //     Tables\Filters\Filter::make('created_at')
            //         ->form([
            //             Forms\Components\DatePicker::make('created_from')->label('Created From'),
            //             Forms\Components\DatePicker::make('created_until')->label('Created Until'),
            //         ])
            //         ->query(function (Builder $query, array $data) {
            //             return $query
            //                 ->when($data['created_from'], fn($query, $date) => $query->whereDate('created_at', '>=', $date))
            //                 ->when($data['created_until'], fn($query, $date) => $query->whereDate('created_at', '<=', $date));
            //         })->indicateUsing(function (array $data): array {
            //             $indicators = [];

            //             if ($data['created_from'] ?? null) {
            //                 $indicators[] = 'From: ' . \Carbon\Carbon::parse($data['created_from'])->format('d M Y');
            //             }

            //             if ($data['created_until'] ?? null) {
            //                 $indicators[] = 'Until: ' . \Carbon\Carbon::parse($data['created_until'])->format('d M Y');
            //             }

            //             return $indicators;
            //         }),

            // ])
            ->filters([
                Tables\Filters\Filter::make('notas')
                    ->form([
                        Forms\Components\TextInput::make('notas')
                            ->label('Cari')
                            ->placeholder('Cari NOTAS / Nama / Norek...')
                            ->debounce(500),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['notas'] ?? null,
                            function ($q, $v) {
                                $q->where(function ($q) use ($v) {
                                    $q->where('notas', 'like', "%{$v}%")
                                        ->orWhere('nama_nasabah', 'like', "%{$v}%")
                                        ->orWhere('rek_tabungan', 'like', "%{$v}%");
                                });
                            }
                        );
                    }),
                // 1️⃣ Status Banpot
                Tables\Filters\SelectFilter::make('status_banpot')
                    ->label('Status Banpot')
                    ->options([
                        'request' => 'Request',
                        'approved_mitra' => 'Approved Mitra',
                        'rejected_mitra' => 'Rejected Mitra',
                        'canceled' => 'Canceled',
                        'on_process' => 'On Process',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'complete' => 'Complete',
                    ])

                    ->preload(),

                // 2️⃣ Final Validasi
                Tables\Filters\SelectFilter::make('final_validasi_status')
                    ->label('Final Validasi')
                    ->options([
                        1 => 'Validasi Sesuai',
                        0 => 'Validasi Tidak Sesuai',
                    ]),

                // 3️⃣ Filter NOTAS


                // 4️⃣ Tanggal Created
                Tables\Filters\Filter::make('created_at')
                    ->label('Tanggal')
                    ->form([
                        Forms\Components\Grid::make(2)

                            ->schema([
                                Forms\Components\DatePicker::make('created_from')
                                    ->label('Dari'),

                                Forms\Components\DatePicker::make('created_until')
                                    ->label('Sampai'),
                            ]),
                    ])
                    ->query(
                        fn(Builder $query, array $data) =>
                        $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn($q, $d) => $q->whereDate('created_at', '>=', $d)
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn($q, $d) => $q->whereDate('created_at', '<=', $d)
                            )
                    )
                    ->columnSpan(1),
                Tables\Filters\SelectFilter::make('created_mitra')
                    ->label('Nama Mitra')
                    ->multiple()
                    ->options(
                        BanpotMaster::query()
                            ->select('created_mitra')
                            ->whereNotNull('created_mitra')
                            ->distinct()
                            ->orderBy('created_mitra')
                            ->pluck('created_mitra', 'created_mitra')
                            ->toArray()
                    )
                    ->columnSpanFull()
                    ->visible(fn() => Auth::user()->hasRole(['staff_bosche', 'super_admin'])),
                Tables\Filters\SelectFilter::make('bulan_dapem')
                    ->label('Bulan Dapem')
                    ->multiple()
                    ->options(function () {

                        $user = Auth::user();

                        $query = DB::table('banpot_masters as b')
                            ->join('users as u', 'u.id', '=', 'b.created_by')
                            ->whereNotNull('b.bulan_dapem');

                        // Kalau bukan super admin, batasi sesuai mitra dia
                        if (! $user->hasRole(['super_admin', 'staff_bosche'])) {
                            $query->where('u.mitra_master_id', $user->mitra_master_id);
                        }

                        return $query
                            ->distinct()
                            ->orderBy('b.bulan_dapem', 'desc')
                            ->pluck('b.bulan_dapem')
                            ->mapWithKeys(function ($value) {
                                try {
                                    $date  = Carbon::createFromFormat('Ymd', $value);
                                    $label = strtoupper($date->translatedFormat('F Y'));
                                    return [$value => $label];
                                } catch (\Exception $e) {
                                    return [$value => $value];
                                }
                            })
                            ->toArray();
                    })
                    ->columnSpanFull(),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(function ($record) {
                        $user = Auth::user();

                        // Admin selalu bisa edit
                        if ($user->hasRole(['staff_bosche', 'super_admin'])) {
                            return true;
                        }

                        if ($user->hasRole(['maker_mitra_pusat', 'approval_mitra_pusat']) && $record->status_banpot === 'request') {
                            return true;
                        }
                        return false;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(function () {
                            $user = Auth::user();

                            // Super admin dan staff bosche selalu bisa hapus
                            if ($user->hasRole(['super_admin', 'staff_bosche'])) {
                                return true;
                            }

                            // maker_mitra_pusat & approval_mitra_pusat juga bisa, tapi nanti validasi per record di action
                            if ($user->hasRole(['maker_mitra_pusat', 'approval_mitra_pusat'])) {
                                return true;
                            }

                            // selain itu gak boleh
                            return false;
                        })
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $user = Auth::user();

                            // hanya hapus yang status request untuk role maker/approval
                            if ($user->hasRole(['maker_mitra_pusat', 'approval_mitra_pusat'])) {
                                $records = $records->filter(fn($record) => $record->status_banpot === 'request');
                            }

                            $count = $records->count();

                            if ($count > 0) {
                                $records->each->delete();

                                Notification::make()
                                    ->title("Berhasil menghapus {$count} data (status request)")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Tidak ada data berstatus request yang dihapus')
                                    ->warning()
                                    ->send();
                            }
                        }),
                    Tables\Actions\BulkAction::make('edit_status_banpot')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-pencil-square')
                        ->visible(fn() => Auth::user()->hasRole(['staff_bosche', 'approval_mitra_pusat', 'super_admin']))
                        ->form(function () {
                            $user = Auth::user();

                            // Pilihan status tergantung role
                            $options = $user->hasRole(['staff_bosche', 'super_admin'])
                                ? [
                                    'request' => 'Request',
                                    'approved_mitra' => 'Approved Mitra',
                                    'rejected_mitra' => 'Rejected Mitra',
                                    'canceled' => 'Canceled',
                                    'on_process' => 'On Process',
                                    'success' => 'Success',
                                    'failed' => 'Failed',
                                    'complete' => 'Complete',
                                ]
                                : [
                                    'request' => 'Request',
                                    'approved_mitra' => 'Approved Mitra',
                                    'rejected_mitra' => 'Rejected Mitra',
                                ];

                            return [
                                Forms\Components\Select::make('status_banpot')
                                    ->label('Pilih Status Baru')
                                    ->options($options)
                                    ->required(),
                            ];
                        })
                        ->action(function (array $data, \Illuminate\Database\Eloquent\Collection $records) {
                            $user = Auth::user();
                            // Kalau bukan staff_bosche, hanya boleh ubah yang request
                            $filtered = $user->hasRole(['staff_bosche', 'super_admin'])
                                ? $records
                                : $records->filter(fn($record) => $record->status_banpot === 'request');

                            $count = $filtered->count();

                            if ($count === 0) {
                                Notification::make()
                                    ->title('Tidak ada data yang memenuhi syarat untuk diubah.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            // Lakukan update
                            $filtered->each->update([
                                'status_banpot' => $data['status_banpot'],
                            ]);

                            Notification::make()
                                ->title("Berhasil mengubah status {$count} data menjadi " . ucfirst(str_replace('_', ' ', $data['status_banpot'])))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListBanpotMasters::route('/'),
            'create' => Pages\CreateBanpotMaster::route('/create'),
            'view' => Pages\ViewBanpotMaster::route('/{record}'),
            'edit' => Pages\EditBanpotMaster::route('/{record}/edit'),
        ];
    }
}
