<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\MarketingMaster;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;

use Illuminate\Database\Eloquent\Builder;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use App\Models\PermintaanFlaggingTifInternal;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use App\Filament\Resources\PermintaanFlaggingTifInternalResource\Pages;
use App\Filament\Resources\PermintaanFlaggingTifInternalResource\RelationManagers;


class PermintaanFlaggingTifInternalResource extends Resource
{
    protected static ?string $model = PermintaanFlaggingTifInternal::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationGroup = 'Layanan Internal';
    protected static ?string $navigationLabel = 'Permintaan Flagging TIF';
    protected static ?string $pluralModelLabel = 'Permintaan Flagging TIF';
    protected static ?string $modelLabel = 'Permintaan Flagging TIF';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $readonlyForApproval = $user->hasRole(['abm', 'staff_bosche']);
        $readonlyForApprovalNew = $user->hasRole(['abm']);
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Flagging TIF')
                    ->schema([
                        Forms\Components\TextInput::make('wilayah')
                            ->label('Wilayah')
                            ->disabled(fn() => $readonlyForApproval)
                            ->required()
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('jenis_pensiun_added')
                            ->inlineLabel()
                            ->options([
                                'moduvox' => 'MODUVOX',
                                'asabri' => 'ASABRI',
                            ])
                            ->disabled(fn() => $readonlyForApproval)
                            ->prefixIcon('heroicon-o-identification')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('jenis_pensiun')
                            ->disabled(fn() => $readonlyForApproval)
                            ->required()
                            ->label('Jenis Pensiun')
                            ->prefixIcon('heroicon-o-bars-arrow-down')
                            ->inlineLabel()
                            ->options([
                                'pensiun' => 'PENSIUN',
                                'aktif' => 'AKTIF',
                            ]),
                        Forms\Components\Select::make('jenis_flagging')
                            ->required()
                            ->label('Jenis Flagging')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-bars-arrow-down')
                            ->disabled(fn() => $readonlyForApproval)
                            ->options([
                                'pensiun' => 'FLAGGING PENSIUN',
                                'prapen' => 'FLAGGING PRAPEN',
                                'tht' => 'FLAGGING THT',
                                // 'prapen_tht' => 'Flagging Prapen + THT',
                            ]),
                        Forms\Components\TextInput::make('nama_nasabah')
                            ->label('Nama Nasabah')
                            ->prefixIcon('heroicon-o-identification')
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('notas')
                            ->label('Notas Nasabah')
                            ->prefixIcon('heroicon-o-building-office')
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->prefixIcon('heroicon-o-credit-card')
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tempat_lahir')
                            ->disabled(fn() => $readonlyForApproval)
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->columnSpanFull()
                            ->inlineLabel(),
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->prefixIcon('heroicon-o-calendar')
                            ->default(now())
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->columnSpanFull()
                            ->disabled(fn() => $readonlyForApproval)
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('no_handphone')
                            ->label('Nomor Handphone')
                            ->tel()
                            ->disabled(fn() => $readonlyForApproval)
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-device-phone-mobile')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('rek_tabungan')
                            ->label('Nomor Rekening Tabungan')
                            ->prefixIcon('heroicon-o-credit-card')
                            ->required()
                            ->disabled(fn() => $readonlyForApprovalNew)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('rek_kredit')
                            ->label('Nomor Rekening Mitra')
                            ->prefixIcon('heroicon-o-credit-card')
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tmt_kredit')
                            ->label('TMT Kredit')
                            ->disabled(fn() => $readonlyForApprovalNew)
                            ->dehydrated(true)
                            ->prefixIcon('heroicon-o-calendar')
                            ->default(now())
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\DatePicker::make('tat_kredit')
                            ->label('TAT Kredit')
                            ->disabled(fn() => $readonlyForApprovalNew)
                            ->dehydrated(true)
                            ->prefixIcon('heroicon-o-calendar')
                            ->default(now())
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\DatePicker::make('tanggal_bup')
                            ->label('Tanggal BUP')
                            ->disabled(fn() => $readonlyForApprovalNew)
                            ->dehydrated(true)
                            ->prefixIcon('heroicon-o-calendar')
                            ->default(now())
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('selisih_prapen')
                            ->label('Selisih Prapen')
                            ->inlineLabel()
                            ->hiddenOn('create')
                            ->disabled(true)
                            ->dehydrated(true)
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->maxLength(255),
                        self::rupiahInput('fee', 'Fee')
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->hiddenOn('create')
                            ->disabledOn('edit'),
                        self::rupiahInput('fee_checking', 'Fee Checking')
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->hiddenOn('create')
                            ->disabledOn('edit'),
                    ])->columns(1),
                Forms\Components\Section::make('Surat Pernyataan Debitur Flagging')
                    ->schema([
                        AdvancedFileUpload::make('sp_deb_flagging')
                            ->label('')
                            ->disabled(fn() => $readonlyForApproval)
                            ->rules([
                                'mimes:pdf',
                                'max:1024',
                            ])
                            ->required()
                            ->validationMessages([
                                'mimes' => 'File harus berupa PDF.',
                                'max' => 'Ukuran file maksimal 1 MB.',
                            ])
                            ->acceptedFileTypes(['application/pdf'])
                            ->pdfPreviewHeight(400) // Customize preview height
                            ->pdfDisplayPage(1) // Set default page
                            ->pdfToolbar(true) // Enable toolbar
                            ->pdfZoomLevel(100) // Set zoom level
                            ->previewable(true)
                            ->openable(true)
                            ->downloadable(true)
                            ->pdfFitType(PdfViewFit::FIT) // Set fit type
                            ->pdfNavPanes(false) // Enable navigation panes

                    ])
                    ->columns(1),
                Forms\Components\Section::make('Keterangan')
                    ->schema([
                        Forms\Components\TextArea::make('keterangan')
                            ->label('')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Bukti Hasil')
                    ->schema([
                        Forms\Components\FileUpload::make('bukti_hasil')
                            ->label('')
                            ->placeholder('Belum tersedia')
                            ->disabled(fn() => !Auth::user()?->hasRole('staff_bosche'))
                            ->dehydrated(true)
                            ->downloadable(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-bars-arrow-down')
                            ->dehydrated()
                            ->options(function (callable $get) {
                                $user = Auth::user();
                                $current = $get('status') ?? 'request';

                                if ($user->hasRole(['super_admin', 'staff_bosche'])) {
                                    $base = [
                                        'request' => 'Request',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        'canceled' => 'Canceled',
                                        'on_process' => 'On Process',
                                        'success' => 'Success',
                                        'failed' => 'Failed',
                                        'complete' => 'Complete',
                                    ];
                                } elseif ($user->hasRole(['abm', 'approval_mitra_pusat'])) {
                                    $base = [
                                        'request' => 'Request',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                    ];
                                } elseif ($user->hasRole(['admin_support', 'maker_mitra_pusat'])) {
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

                                if ($user->hasRole('abm')) {
                                    return false;
                                }

                                // Maker Mitra Pusat atau role lain tidak bisa ubah
                                return true;
                            }),

                        Forms\Components\Hidden::make('created_by')
                            ->default(fn() => auth()->user()?->id)
                            ->disabled()
                            ->dehydrated(true)
                            ->inlineLabel(),

                        Forms\Components\TextInput::make('created_branch')
                            ->label('Dibuat')
                            ->prefixIcon('heroicon-o-building-office')
                            ->default(fn() => auth()->user()?->branchMaster?->branch_name)
                            ->disabled()
                            ->dehydrated(true) // akan ikut tersimpan
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('branch_code')
                            ->label('Kode Cabang')
                            ->prefixIcon('heroicon-o-building-office')
                            ->default(fn() => auth()->user()?->branchMaster?->branch_code)
                            ->disabled()
                            ->dehydrated(true) // akan ikut tersimpan
                            ->inlineLabel(),
                        Forms\Components\Select::make('marketing_id')
                            ->label('Marketing')
                            ->options(function (?PermintaanFlaggingTifInternal $record) {
                                $user = auth()->user();
                                $query = MarketingMaster::query();

                                // Jika bukan super_admin/staff_bosche, filter sesuai branch user
                                if (!$user->hasRole(['super_admin', 'staff_bosche'])) {
                                    $query->where('branch_master_id', $user->branch_master_id);
                                }

                                // Pastikan marketing yang sudah terpilih tetap muncul agar label ter-resolve
                                if ($record && $record->marketing_id) {
                                    $query->orWhere('id', $record->marketing_id);
                                }

                                return $query->pluck('marketing_name', 'id');
                            })
                            ->prefixIcon('heroicon-o-user-group')
                            ->required()
                            ->inlineLabel()
                            ->disabled(fn() => $readonlyForApproval)
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('reference')
                            ->label('Referensi')
                            ->options([
                                'Pegawai Moduvox' => 'Pegawai Moduvox',
                                'Pegawai PKS' => 'Pegawai PKS',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->prefixIcon('heroicon-o-user-group')
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('mitra_master_id')
                            ->label('Mitra')
                            ->relationship(
                                name: 'mitraMaster',
                                titleAttribute: 'nama_mitra',
                                modifyQueryUsing: fn($query) => $query->where('is_sinergi', true)
                            )
                            ->required()
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-building-office-2')
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->preload()
                            ->searchable(),
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
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_branch')->label('Nama Mitra')
                    ->placeholder('Tidak diatur'),
                Tables\Columns\TextColumn::make('wilayah')
                    ->label('Wilayah'),
                Tables\Columns\TextColumn::make('jenis_pensiun_added')
                    ->label('Jenis Pensiun')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'moduvox' => 'MODUVOX',
                        'asabri' => 'ASABRI',
                    }),
                Tables\Columns\TextColumn::make('jenis_pensiun')
                    ->label('Jenis Pensiun')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pensiun' => 'PENSIUN',
                        'aktif' => 'AKTIF',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('jenis_flagging')
                    ->label('Jenis Flagging')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pensiun' => 'FLAGGING PENSIUN',
                        'prapen' => 'FLAGGING PRAPEN',
                        'tht' => 'FLAGGING THT',
                        // 'prapen_tht' => 'Flagging Prapen + THT',,
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('notas')
                    ->label('Notas Nasabah'),
                Tables\Columns\TextColumn::make('nama_nasabah')
                    ->label('Nama Nasabah'),
                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK'),
                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->date('d F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_handphone')
                    ->label('Nomor Handphone'),
                Tables\Columns\TextColumn::make('rek_tabungan')
                    ->label('Nomor Rekening Nasabah'),
                Tables\Columns\TextColumn::make('rek_kredit')
                    ->label('Nomor Rekening Mitra'),
                Tables\Columns\TextColumn::make('tmt_kredit')
                    ->label('TMT Kredit')
                    ->date('d F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tat_kredit')
                    ->label('TAT Kredit')
                    ->date('d F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_bup')
                    ->label('Tanggal BUP')
                    ->date('d F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('selisih_prapen')
                    ->label('Selisih Prapen'),
                Tables\Columns\IconColumn::make('sp_deb_flagging')
                    ->label('Surat Pernyataan Debitur Flagging')
                    ->icon('heroicon-o-document-text')
                    ->url(fn($record) => Storage::url($record->sp_deb_flagging))
                    ->openUrlInNewTab()
                    ->tooltip('Surat Pernyataan Debitur Flagging')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('fee')
                    ->label('Fee Flagging')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fee_checking')
                    ->label('Fee Checking')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'primary' => 'request',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'secondary' => 'canceled',
                        'info' => 'on_process',
                        'success' => 'success',
                        'danger' => 'failed',
                        'secondary' => 'complete',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'request' => 'Request',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'canceled' => 'Canceled',
                        'on_process' => 'On Process',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'complete' => 'Complete',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('bukti_hasil')
                    ->label('Bukti Hasil Proses')
                    ->placeholder('Belum tersedia')
                    ->icon('heroicon-o-document-text')
                    ->url(fn($record) => Storage::url($record->bukti_hasil))
                    ->openUrlInNewTab()
                    ->tooltip('Bukti Hasil')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('keterangan'),
                Tables\Columns\TextColumn::make('creator.name')->label('Dibuat Oleh'),
                Tables\Columns\TextColumn::make('mitraMaster.nama_mitra')->label('Mitra')->placeholder('Tidak diatur'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d F Y  H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d F Y  H:i')
                    ->sortable(),
            ])
            // ->filters([
            //     Tables\Filters\SelectFilter::make('created_branch')
            //         ->label('Nama Mitra')
            //         ->multiple()
            //         ->options(
            //             PermintaanFlaggingTif::query()
            //                 ->select('created_branch')
            //                 ->whereNotNull('created_branch')
            //                 ->distinct()
            //                 ->orderBy('created_branch')
            //                 ->pluck('created_branch', 'created_branch')
            //                 ->toArray()
            //         )
            //         ->visible(fn() => Auth::user()->hasRole(['staff_bosche', 'super_admin'])),

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
                            ->label('Notas')
                            ->placeholder('Cari NOTAS...')
                            ->debounce(500),
                    ])
                    ->query(
                        fn(Builder $query, array $data) =>
                        $query->when(
                            $data['notas'] ?? null,
                            fn($q, $v) => $q->where('notas', 'like', "%{$v}%")
                        )
                    ),
                Tables\Filters\Filter::make('nama_nasabah')
                    ->form([
                        Forms\Components\TextInput::make('nama_nasabah')
                            ->label('Nama Nasabah')
                            ->placeholder('Cari Nama Nasabah...')
                            ->debounce(500),
                    ])
                    ->query(
                        fn(Builder $query, array $data) =>
                        $query->when(
                            $data['nama_nasabah'] ?? null,
                            fn($q, $v) => $q->where('nama_nasabah', 'like', "%{$v}%")
                        )
                    ),
                // 1️⃣ Status Banpot
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'request' => 'Request',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'canceled' => 'Canceled',
                        'on_process' => 'On Process',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'complete' => 'Complete',
                    ])

                    ->preload(),

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
                Tables\Filters\SelectFilter::make('created_branch')
                    ->label('Nama Cabang')
                    ->multiple()
                    ->options(
                        PermintaanFlaggingTifInternal::query()
                            ->select('created_branch')
                            ->whereNotNull('created_branch')
                            ->distinct()
                            ->orderBy('created_branch')
                            ->pluck('created_branch', 'created_branch')
                            ->toArray()
                    )
                    ->columnSpanFull()
                    ->visible(fn() => Auth::user()->hasRole(['staff_bosche', 'super_admin'])),
                Tables\Filters\SelectFilter::make('mitra_master_id')
                    ->label('Nama Mitra')
                    ->multiple()
                    ->options(
                        \App\Models\MitraMaster::query()
                            ->whereIn(
                                'id',
                                PermintaanFlaggingTifInternal::whereNotNull('mitra_master_id')
                                    ->select('mitra_master_id')
                                    ->distinct()
                            )
                            ->orderBy('nama_mitra')
                            ->pluck('nama_mitra', 'id')
                            ->toArray()
                    )
                    ->columnSpanFull()
                    ->visible(fn() => Auth::user()->hasRole(['staff_bosche', 'super_admin', 'admin_support', 'abm'])),
                Tables\Filters\SelectFilter::make('mitra_master_id')
                    ->label('Nama Mitra')
                    ->multiple()
                    ->options(
                        \App\Models\MitraMaster::query()
                            ->whereIn(
                                'id',
                                PermintaanFlaggingTifInternal::whereNotNull('mitra_master_id')
                                    ->select('mitra_master_id')
                                    ->distinct()
                            )
                            ->orderBy('nama_mitra')
                            ->pluck('nama_mitra', 'id')
                            ->toArray()
                    )
                    ->columnSpanFull()
                    ->visible(fn() => Auth::user()->hasRole(['staff_bosche', 'super_admin', 'admin_support', 'abm'])),
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

                        if ($user->hasRole(['admin_support', 'abm']) && $record->status === 'request') {
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
                            if ($user->hasRole(['admin_support', 'abm'])) {
                                return true;
                            }

                            // selain itu gak boleh
                            return false;
                        })
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $user = Auth::user();

                            // hanya hapus yang status request untuk role maker/approval
                            if ($user->hasRole(['admin_support', 'abm'])) {
                                $records = $records->filter(fn($record) => $record->status === 'request');
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
            'index' => Pages\ListPermintaanFlaggingTifInternals::route('/'),
            'create' => Pages\CreatePermintaanFlaggingTifInternal::route('/create'),
            'view' => Pages\ViewPermintaanFlaggingTifInternal::route('/{record}'),
            'edit' => Pages\EditPermintaanFlaggingTifInternal::route('/{record}/edit'),
        ];
    }
}
