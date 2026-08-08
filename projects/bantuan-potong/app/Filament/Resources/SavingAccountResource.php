<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\District;
use App\Models\MasterDati2;
use App\Models\SavingAccount;
use App\Models\Subdistrict;
use App\Models\NotasOwnership;
use App\Models\MasterProvince;
use Filament\Resources\Resource;
use function Laravel\Prompts\select;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SavingAccountResource\Pages;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use App\Filament\Resources\SavingAccountResource\RelationManagers;
use Filament\Tables\Enums\FiltersLayout;
use Joaopaulolndev\FilamentPdfViewer\Forms\Components\PdfViewerField;

class SavingAccountResource extends Resource
{
    protected static ?string $model = SavingAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Layanan';
    protected static ?string $navigationLabel = 'Pembuatan Rekening';
    protected static ?string $pluralModelLabel = 'Pembuatan Rekening';
    protected static ?string $modelLabel = 'Pembuatan Rekening';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $readonlyForApproval = $user->hasRole(['approval_mitra_cabang', 'staff_bosche']);

        return $form
            ->schema([
                Forms\Components\Section::make('Detail Saving Account')
                    ->schema([
                        Forms\Components\TextInput::make('wilayah')
                            ->required()
                            ->inlineLabel()
                            ->disabled(fn() => $readonlyForApproval)
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('notas')
                            ->label('Notas Nasabah')
                            ->rules(['regex:/^[A-Za-z0-9]+$/'])
                            ->validationMessages([
                                'regex' => 'Hanya Bisa Huruf dan Angka tanpa spasi',
                            ])
                            ->prefixIcon('heroicon-o-building-office')
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Nama Nasabah')
                            ->rules([
                                'regex:/^[A-Za-z\s]+$/u', // hanya huruf & spasi
                            ])
                            ->validationMessages([
                                'regex' => 'Hanya Bisa Huruf',
                            ])
                            ->prefixIcon('heroicon-o-identification')
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('customer_alias_name')
                            ->label('Nama Alias')
                            ->prefixIcon('heroicon-o-identification')
                            ->rules([
                                'regex:/^[A-Za-z\s]+$/u', // hanya huruf & spasi
                            ])
                            ->validationMessages([
                                'regex' => 'Hanya Bisa Huruf',
                            ])
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('identity_type')
                            ->label('Jenis Identitas')
                            ->prefixIcon('heroicon-o-credit-card')
                            ->preload()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->required()
                            ->inlineLabel()
                            ->options([
                                'KTP' => 'KTP',
                                'PASPORT' => 'PASPORT',
                                'SIM' => 'SIM',
                                'AKTA' => 'AKTA',
                                'KITAS' => 'KITAS',
                                'KK' => 'KK',
                                'Lain-Lain' => 'Lain-Lain'
                            ]),
                        Forms\Components\TextInput::make('national_id_number')
                            ->label('Nomor Indetitas')
                            ->prefixIcon('heroicon-o-credit-card')
                            ->required()
                            ->numeric()
                            ->rules('digits:16')
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('alternate_number')
                            ->maxLength(255)
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->prefixIcon('heroicon-o-credit-card')
                            ->hiddenOn(['create', 'view', 'edit'])
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('mobile_phone')
                            ->label('Nomor Handphone')
                            ->prefixIcon('heroicon-o-device-phone-mobile')
                            ->numeric()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->required(),
                        Forms\Components\TextInput::make('place_of_birth')
                            ->label('Tempat Lahir')
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->prefixIcon('heroicon-o-map-pin')
                            ->inlineLabel()
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Tanggal Lahir')
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->prefixIcon('heroicon-o-calendar')
                            ->default(now())
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->prefixIcon('heroicon-o-users')
                            ->preload()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)

                            ->required()
                            ->inlineLabel()
                            ->options([
                                '1' => 'LAKI - LAKI',
                                '2' => 'PEREMPUAN'
                            ]),
                        Forms\Components\Select::make('religion')
                            ->label('Agama')
                            ->prefixIcon('heroicon-o-users')
                            ->preload()

                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->required()
                            ->inlineLabel()
                            ->options([
                                '1' => 'ISLAM',
                                '2' => 'KATOLIK',
                                '3' => 'KRISTEN',
                                '4' => 'BUDDHA',
                                '5' => 'HINDU',
                                '9' => 'LAINNYA'
                            ]),
                        Forms\Components\TextInput::make('mother_maiden_name')
                            ->label('Nama Ibu Kandung')
                            ->prefixIcon('heroicon-o-user')
                            ->rules([
                                'regex:/^[A-Za-z\s]+$/u', // hanya huruf & spasi
                            ])
                            ->validationMessages([
                                'regex' => 'Hanya Bisa Huruf',
                            ])
                            ->required()
                            ->inlineLabel()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('last_edu')
                            ->label('Pendidikan Terakhir')
                            ->prefixIcon('heroicon-o-academic-cap')
                            ->preload()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->required()

                            ->options([
                                '00' => 'TANPA GELAR',
                                '01' => 'DIPLOMA 1',
                                '02' => 'DIPLOMA 2',
                                '03' => 'DIPLOMA 3',
                                '04' => 'STRATA 1',
                                '05' => 'STRATA 2',
                                '06' => 'STRATA 3',
                                '99' => 'LAINNYA',
                            ])
                            ->inlineLabel(),
                        Forms\Components\Select::make('province')
                            ->label('Provinsi')
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->options(MasterProvince::pluck('nama', 'id'))
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->reactive()
                            ->preload()

                            ->afterStateUpdated(function ($set, $state) {
                                $set('province_code', $state);
                                $set('dati2_code', null);
                                $set('dati2_name', null);
                                $set('sub_district', null);
                                $set('urban_village', null);
                                $set('postal_code', null);
                            }),

                        Forms\Components\Hidden::make('province_code')
                            ->dehydrated(false),
                        Forms\Components\Select::make('dati2_code_view')
                            ->relationship('dati2', 'nama')
                            ->label('Kota / Kabupaten')
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->hiddenOn(['create', 'edit'])
                            ->required()
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->dehydrated(false)
                            ->inlineLabel(),
                        Forms\Components\Select::make('dati2_code')
                            ->label('Kota / Kabupaten')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->reactive()
                            ->hiddenOn('view')
                            ->preload()

                            ->options(function ($get) {
                                $provinceCode = $get('province_code');
                                if ($provinceCode) {
                                    return \App\Models\MasterDati2::where('province_id', $provinceCode)
                                        ->pluck('nama', 'dati2');
                                }
                                return [];
                            })
                            ->disabled(function ($get) use ($readonlyForApproval) {
                                return !$get('province_code') || $readonlyForApproval;
                            })
                            ->dehydrated(true)
                            ->required()
                            ->afterStateUpdated(function ($set, $state) {
                                $set('sub_district', null);
                                $set('urban_village', null);
                                $set('postal_code', null);

                                // ambil nama dati2 dari kode yang dipilih
                                $dati2Name = MasterDati2::where('dati2', $state)->value('nama');
                                $set('dati2_name', $dati2Name);
                            }),

                        Forms\Components\Hidden::make('dati2_name')
                            ->dehydrated(true),


                        Forms\Components\Select::make('urban_village')
                            ->label('Kecamatan')
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->options(function ($get) {
                                $cityId = MasterDati2::where('dati2', $get('dati2_code'))->value('id');

                                return $cityId
                                    ? District::where('province_id', $get('province'))
                                    ->where('regency_id', $cityId)
                                    ->pluck('nama', 'nama')
                                    : [];
                            })
                            ->disabled(function ($get) use ($readonlyForApproval) {
                                return !$get('dati2_code') || $readonlyForApproval;
                            })
                            ->dehydrated(true)
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->preload()
                            ->inlineLabel()
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($set) {
                                $set('sub_district', null);
                                $set('postal_code', null);
                            }),

                        Forms\Components\Select::make('sub_district')
                            ->label('Kelurahan')
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->options(function ($get) {
                                $cityId = MasterDati2::where('dati2', $get('dati2_code'))->value('id');
                                $districtId = District::where('province_id', $get('province'))
                                    ->where('regency_id', $cityId)
                                    ->where('nama', $get('urban_village'))
                                    ->value('id');

                                return $districtId
                                    ? Subdistrict::where('province_id', $get('province'))
                                    ->where('regency_id', $cityId)
                                    ->where('district_id', $districtId)
                                    ->pluck('nama', 'nama')
                                    : [];
                            })
                            ->disabled(function ($get) use ($readonlyForApproval) {
                                return !$get('urban_village') || $readonlyForApproval;
                            })
                            ->dehydrated(true)
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->preload()
                            ->inlineLabel()
                            ->columnSpanFull()
                            ->afterStateUpdated(fn($set, $state) => $set('postal_code', null)),

                        Forms\Components\TextInput::make('postal_code')
                            ->label('Kode Pos')
                            ->prefixIcon('heroicon-o-globe-europe-africa')
                            ->reactive()
                            ->disabled(function ($get) use ($readonlyForApproval) {
                                return !$get('sub_district') || $readonlyForApproval;
                            })
                            ->dehydrated(true)
                            ->required()
                            ->inlineLabel()
                            ->maxLength(5)
                            ->rules(['digits:5']),
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('tax_id')
                            ->label('NPWP')
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->prefixIcon('heroicon-o-credit-card')
                            ->inlineLabel()
                            ->numeric()
                            ->maxLength(255),
                        Forms\Components\Hidden::make('sid_status')
                            ->inlineLabel()
                            ->disabled()
                            ->dehydrated(true)
                            ->default('0299'),
                        Forms\Components\TextInput::make('debtor_in_city_administrative')
                            ->inlineLabel()
                            ->hidden()
                            ->maxLength(255),
                        Forms\Components\Hidden::make('debtor_type_other')
                            ->inlineLabel()
                            ->disabled()
                            ->dehydrated(true)
                            ->default('875'),
                        Forms\Components\Hidden::make('debtor_type')
                            ->inlineLabel()
                            ->disabled()
                            ->dehydrated(true)
                            ->default('NU'),
                        Forms\Components\Select::make('marital_status')
                            ->preload()
                            ->prefixIcon('heroicon-o-users')
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->label('Status Nikah')
                            ->options([
                                '1' => 'BELUM MENIKAH',
                                '2' => 'MENIKAH',
                                '3' => 'CERAI',
                                '4' => 'CERAI MATI'
                            ])
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('nama_pasangan')
                            ->placeholder('Kosongkan Jika Belum menikah')
                            ->prefixIcon('heroicon-o-user')
                            ->rules([
                                'regex:/^[A-Za-z\s]+$/u', // hanya huruf & spasi
                            ])
                            ->validationMessages([
                                'regex' => 'Hanya Bisa Huruf',
                            ])
                            ->inlineLabel()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('nik_pasangan')
                            ->placeholder('Kosongkan Jika Belum menikah')
                            ->prefixIcon('heroicon-o-credit-card')
                            ->numeric()
                            ->inlineLabel()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('kontak_darurat')
                            ->inlineLabel()
                            ->numeric()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->prefixIcon('heroicon-o-device-phone-mobile')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama_ahli_waris')
                            ->prefixIcon('heroicon-o-user')
                            ->inlineLabel()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('hub_ahli_waris')
                            ->prefixIcon('heroicon-o-users')
                            ->preload()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->required()

                            ->options([
                                '01' => 'SUAMI/ISTRI',
                                '02' => 'BAPAK/IBU KANDUNG',
                                '03' => 'BAPAK/IBU MERTUA',
                                '04' => 'BAPAK/IBU TIRI',
                                '05' => 'BAPAK/IBU ANGKAT',
                                '06' => 'KAKEK/NENEK',
                                '07' => 'PAMAN/BIBI',
                                '08' => 'SAUDARA KANDUNG',
                                '09' => 'SAUDARA IPAR',
                                '10' => 'SAUDARA TIRI',
                                '11' => 'SAUDARA ANGKAT',
                                '12' => 'SEPUPU KANDUNG',
                                '13' => 'SEPUPU IPAR',
                                '14' => 'ANAK KANDUNG',
                                '15' => 'ANAK TIRI',
                                '16' => 'ANAK ANGKAT',
                                '17' => 'KEPONAKAN KANDUNG',
                                '18' => 'KEPONAKAN IPAR',
                                '19' => 'CUCU',
                                '20' => 'KERABAT LAINNYA',
                                '99' => 'BUKAN KERABAT',
                            ])
                            ->inlineLabel(),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Scan KTP,KK,Formulir Pembukaan Rekening,Surat Perintah Kuasa Transfer')
                    ->schema([
                        AdvancedFileUpload::make('form_buka_tab')
                            ->label('')
                            ->required()
                            ->rules([
                                'mimes:pdf',
                                'max:2048',
                            ])
                            ->validationMessages([
                                'mimes' => 'File harus berupa PDF.',
                                'max' => 'Ukuran file maksimal 2 MB.',
                            ])
                            ->acceptedFileTypes(['application/pdf'])
                            ->pdfPreviewHeight(400) // Customize preview height
                            ->pdfDisplayPage(1) // Set default page
                            ->pdfToolbar(true) // Enable toolbar
                            ->pdfZoomLevel(100) // Set zoom level
                            ->previewable(true)
                            ->disabled(fn() => $readonlyForApproval)
                            ->openable(true)
                            ->downloadable(true)
                            ->pdfFitType(PdfViewFit::FIT) // Set fit type
                            ->pdfNavPanes(false) // Enable navigation panes
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Keterangan')
                    ->schema([
                        Forms\Components\Textarea::make('keterangan')->label('')->columnSpanFull(),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Keterangan System')
                    ->schema([
                        Forms\Components\Textarea::make('keterangan_2')->label('')->columnSpanFull()->disabled()
                            ->dehydrated(true),
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
                                        'approved_mitra' => 'Approved Mitra',
                                        'rejected_mitra' => 'Rejected Mitra',
                                        'canceled' => 'Canceled',
                                        'on_process' => 'On Process',
                                        'success' => 'Success',
                                        'failed' => 'Failed',
                                        'complete' => 'Complete',
                                    ];
                                } elseif ($user->hasRole(['approval_mitra_cabang', 'approval_mitra_pusat'])) {
                                    $base = [
                                        'request' => 'Request',
                                        'approved_mitra' => 'Approved Mitra',
                                        'rejected_mitra' => 'Rejected Mitra',
                                    ];
                                } elseif ($user->hasRole(['maker_mitra_cabang', 'maker_mitra_pusat'])) {
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

                                if ($user->hasRole('approval_mitra_cabang')) {
                                    return false;
                                }

                                // Maker Mitra Pusat atau role lain tidak bisa ubah
                                return true;
                            }),
                        Forms\Components\TextInput::make('rek_tabungan')
                            ->inlineLabel()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->prefixIcon('heroicon-o-credit-card')
                            ->hiddenOn(['create'])
                            ->maxLength(255),
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn() => auth()->user()?->id)
                            ->disabled()
                            ->dehydrated(true)
                            ->inlineLabel(),

                        Forms\Components\TextInput::make('created_mitra')
                            ->label('Dibuat')
                            ->prefixIcon('heroicon-o-building-office')
                            ->default(fn() => auth()->user()?->mitraMaster?->nama_mitra)
                            ->disabled()
                            ->dehydrated(true) // akan ikut tersimpan
                            ->inlineLabel(),
                    ])
                    ->columns(1),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('wilayah')
                    ->label('Wilayah'),
                Tables\Columns\TextColumn::make('notas')
                    ->label('Notas')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Nama Nasabah')

                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('customer_alias_name')
                    ->label('Nama Alias')

                    ->limit(25),

                Tables\Columns\TextColumn::make('national_id_number')
                    ->label('NIK'),

                Tables\Columns\TextColumn::make('mobile_phone')
                    ->label('Nomor Handphone'),

                Tables\Columns\TextColumn::make('place_of_birth')
                    ->label('Tempat Lahir'),

                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('Tanggal Lahir')
                    ->date('d F Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn($state) => match ($state) {
                        '1' => 'LAKI - LAKI',
                        '2' => 'PEREMPUAN',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('religion')
                    ->label('Agama')
                    ->formatStateUsing(fn($state) => match ($state) {
                        '1' => 'ISLAM',
                        '2' => 'KATOLIK',
                        '3' => 'KRISTEN',
                        '4' => 'BUDDHA',
                        '5' => 'HINDU',
                        '9' => 'LAINNYA',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('sub_district')
                    ->label('Kecamatan')
                    ->limit(20),

                Tables\Columns\TextColumn::make('urban_village')
                    ->label('Kelurahan')
                    ->limit(20),

                Tables\Columns\TextColumn::make('postal_code')
                    ->label('Kode Pos'),

                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat Lengkap')

                    ->limit(40)
                    ->tooltip(fn($record) => $record->address),

                Tables\Columns\IconColumn::make('form_buka_tab')
                    ->label('Dokumen Persyaratan')
                    ->icon('heroicon-o-document-text')
                    ->url(fn($record) => Storage::url($record->form_buka_tab))
                    ->openUrlInNewTab()
                    ->tooltip('Dokumen Persyaratan')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('rek_tabungan')
                    ->label('Nomor Rekening')
                    ->placeholder('Belum tersedia'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'primary' => 'request',
                        'success' => 'approved_mitra',
                        'danger' => 'rejected_mitra',
                        'secondary' => 'canceled',
                        'info' => 'on_process',
                        'success' => 'success',
                        'danger' => 'failed',
                        'secondary' => 'complete',
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

                Tables\Columns\TextColumn::make('keterangan_2')
                    ->label('Keterangan System')
                    ->badge(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')

                    ->limit(50)
                    ->tooltip(fn($record) => $record->keterangan),

                Tables\Columns\TextColumn::make('creator.name')->label('Dibuat Oleh'),
                Tables\Columns\TextColumn::make('created_mitra')->label('Nama Mitra')
                    ->placeholder('Tidak diatur'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d F Y  H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d F Y  H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notas_admin')
                    ->label('Notas')
                    ->getStateUsing(fn($record) => $record->notas)
                    ->visible(fn() => Auth::user()->hasRole('super_admin')),

                Tables\Columns\TextColumn::make('notasOwnership.notas')
                    ->label('Notas Ownership')
                    ->getStateUsing(fn($record) => $record->notasOwnership?->notas)
                    ->placeholder('Belum tersedia')
                    ->visible(fn() => Auth::user()->hasRole('super_admin')),
                Tables\Columns\TextColumn::make('notasOwnership.rek_tabungan')
                    ->label('Rekening Ownership')
                    ->getStateUsing(fn($record) => $record->notasOwnership?->rek_tabungan)
                    ->placeholder('Belum tersedia')
                    ->visible(fn() => Auth::user()->hasRole('super_admin')),
            ])
            // ->filters([

            //     Tables\Filters\SelectFilter::make('created_mitra')
            //         ->label('Nama Mitra')
            //         ->multiple()
            //         ->options(
            //             SavingAccount::query()
            //                 ->select('created_mitra')
            //                 ->whereNotNull('created_mitra')
            //                 ->distinct()
            //                 ->orderBy('created_mitra')
            //                 ->pluck('created_mitra', 'created_mitra')
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
                Tables\Filters\Filter::make('customer_name')
                    ->form([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Nama Nasabah')
                            ->placeholder('Cari Nama Nasabah...')
                            ->debounce(500),
                    ])
                    ->query(
                        fn(Builder $query, array $data) =>
                        $query->when(
                            $data['customer_name'] ?? null,
                            fn($q, $v) => $q->where('customer_name', 'like', "%{$v}%")
                        )
                    ),
                // 1️⃣ Status Banpot
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
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
                        SavingAccount::query()
                            ->select('created_mitra')
                            ->whereNotNull('created_mitra')
                            ->distinct()
                            ->orderBy('created_mitra')
                            ->pluck('created_mitra', 'created_mitra')
                            ->toArray()
                    )
                    ->columnSpanFull()
                    ->visible(fn() => Auth::user()->hasRole(['staff_bosche', 'super_admin'])),
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

                        if ($user->hasRole(['maker_mitra_cabang', 'approval_mitra_cabang']) && $record->status === 'request') {
                            return true;
                        }
                        return false;
                    }),

                Tables\Actions\Action::make('continueRegisteredNotas')
                    ->label('Lanjutkan')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->visible(fn($record) => Auth::user()->hasRole('super_admin')
                        && $record->status === 'failed'
                        && $record->keterangan_2 === 'NOTAS SUDAH TERDAFTAR')
                    ->requiresConfirmation()
                    ->modalHeading('Lanjutkan proses?')
                    ->modalDescription(fn($record) => "Lanjutkan proses untuk NOTAS {$record->notas}?")
                    ->modalSubmitActionLabel('Ya, lanjutkan')
                    ->action(function ($record) {
                        $oldNotas = $record->notas;
                        $newNotas = \Illuminate\Support\Facades\DB::transaction(function () use ($record, $oldNotas) {
                            $ownership = NotasOwnership::where('notas', $oldNotas)->lockForUpdate()->first();

                            if (!$ownership) {
                                return null;
                            }

                            $baseNotas = "{$oldNotas}SWITCH";
                            $newNotas = $baseNotas;
                            $counter = 2;
                            $usedNotas = NotasOwnership::query()
                                ->where('notas', 'like', "{$oldNotas}SWITCH%")
                                ->orWhere('notas', 'like', "{$oldNotas}SIWTCH%")
                                ->lockForUpdate()
                                ->pluck('notas');

                            while ($usedNotas->contains($newNotas)) {
                                $newNotas = $baseNotas . $counter++;
                            }

                            $ownership->update(['notas' => $newNotas]);

                            $record->update([
                                'status' => 'approved_mitra',
                                'keterangan_2' => "NOTAS ownership {$oldNotas} diubah ke {$newNotas}. Proses dikembalikan ke Approved Mitra.",
                            ]);

                            return $newNotas;
                        });

                        if (!$newNotas) {
                            Notification::make()
                                ->title('Notas ownership belum tersedia')
                                ->body('Data NOTAS belum ditemukan di notas ownership.')
                                ->warning()
                                ->send();
                            return;
                        }

                        Notification::make()
                            ->title('Proses dilanjutkan')
                            ->body("NOTAS ownership diubah menjadi {$newNotas}.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('getCif')
                    ->label('Get CIF')
                    ->icon('heroicon-o-identification')
                    ->color('info')
                    ->visible(fn($record) => Auth::user()->hasRole(['super_admin'])
                        && $record->status === 'failed' && $record->status_cif === 'failed')
                    ->requiresConfirmation()
                    ->modalHeading('Get CIF')
                    ->modalDescription(fn($record) => "Ambil CIF untuk NIK: {$record->national_id_number}?")
                    ->modalSubmitActionLabel('Ya, Get CIF')
                    ->action(function ($record) {
                        $nik = $record->national_id_number;

                        if (!$nik) {
                            Notification::make()
                                ->title('NIK tidak ditemukan')
                                ->body('Data national_id_number kosong.')
                                ->warning()
                                ->send();
                            return;
                        }

                        try {
                            $baseUrl = config('services.api.inquiry_cif_url');

                            if (!$baseUrl) {
                                Notification::make()
                                    ->title('Konfigurasi tidak ditemukan')
                                    ->body('INQUIRY_CIF_URL belum diset di .env')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $response = \Illuminate\Support\Facades\Http::timeout(10)
                                ->withHeaders([
                                    'Accept' => 'application/json',
                                    'Content-Type' => 'application/json',
                                ])
                                ->get($baseUrl, [
                                    'nationalIdNo' => $nik,
                                ]);

                            if (!$response->successful()) {
                                Notification::make()
                                    ->title('Gagal menghubungi server CIF')
                                    ->body('HTTP Status: ' . $response->status())
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $json = $response->json();
                            $responseCode = $json['responseCode'] ?? null;

                            if ($responseCode !== '00') {
                                Notification::make()
                                    ->title('CIF tidak ditemukan')
                                    ->body('Response: ' . ($json['description'] ?? 'Unknown error'))
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $customerNo = $json['data']['customerNo'] ?? null;

                            if (!$customerNo) {
                                Notification::make()
                                    ->title('customerNo tidak ada di response')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $record->update([
                                'customer_id' => $customerNo,
                                'status_cif' => 'success',
                                'status' => 'on_process',
                            ]);

                            Notification::make()
                                ->title('CIF Berhasil Didapatkan')
                                ->body("CIF: {$customerNo} berhasil disimpan.")
                                ->success()
                                ->send();
                        } catch (\Illuminate\Http\Client\ConnectionException $e) {
                            Notification::make()
                                ->title('Koneksi ke server CIF gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Terjadi kesalahan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('print')
                    ->label('Bukti Pembukaan Rekening')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->visible(fn($record) => !empty($record->status) && in_array($record->status, ['success', 'complete']))
                    ->size(ActionSize::Small)
                    ->url(fn($record) => route('form_tab', $record))
                    ->openUrlInNewTab()
                    ->tooltip('Bukti Pembukaan Rekening'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(function () {
                        $user = Auth::user();

                        // Super admin dan staff bosche selalu bisa hapus
                        if ($user->hasRole(['super_admin', 'staff_bosche'])) {
                            return true;
                        }

                        // maker_mitra_pusat & approval_mitra_pusat juga bisa, tapi nanti validasi per record di action
                        if ($user->hasRole(['maker_mitra_cabang', 'approval_mitra_cabang'])) {
                            return true;
                        }

                        // selain itu gak boleh
                        return false;
                    })
                        ->action(function (\Illuminate\Database\Eloquent\Collection $query) {
                            $user = Auth::user();

                            // hanya hapus yang status request untuk role maker/approval
                            if ($user->hasRole(['maker_mitra_cabang', 'approval_mitra_cabang'])) {
                                $query = $query->filter(fn($record) => $record->status === 'request');
                            }

                            $count = $query->count();

                            if ($count > 0) {
                                $query->each->delete();

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
            'index' => Pages\ListSavingAccounts::route('/'),
            'create' => Pages\CreateSavingAccount::route('/create'),
            'view' => Pages\ViewSavingAccount::route('/{record}'),
            'edit' => Pages\EditSavingAccount::route('/{record}/edit'),
        ];
    }
}
