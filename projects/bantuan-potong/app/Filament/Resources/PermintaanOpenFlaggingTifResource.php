<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use App\Models\PermintaanOpenFlaggingTif;
use Illuminate\Database\Eloquent\Builder;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use App\Filament\Resources\PermintaanOpenFlaggingTifResource\Pages;
use Joaopaulolndev\FilamentPdfViewer\Forms\Components\PdfViewerField;
use App\Filament\Resources\PermintaanOpenFlaggingTifResource\RelationManagers;
use Filament\Tables\Enums\FiltersLayout;

class PermintaanOpenFlaggingTifResource extends Resource
{
    protected static ?string $model = PermintaanOpenFlaggingTif::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationGroup = 'Layanan';
    protected static ?string $navigationLabel = 'Permintaan Buka Flagging TIF';
    protected static ?string $pluralModelLabel = 'Permintaan Buka Flagging TIF';
    protected static ?string $modelLabel = 'Permintaan Buka Flagging TIF';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $readonlyForApproval = $user->hasRole(['approval_mitra_cabang', 'staff_bosche']);

        return $form
            ->schema([
                Forms\Components\Section::make('Detail Buka Flagging TIF')
                    ->schema([
                        Forms\Components\TextInput::make('wilayah')
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
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
                        Forms\Components\TextInput::make('notas')
                            ->label('Notas Nasabah')
                            ->prefixIcon('heroicon-o-building-office')
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('nama_nasabah')
                            ->label('Nama Nasabah')
                            ->prefixIcon('heroicon-o-identification')
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
                            ->label('Tempat Lahir')
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->prefixIcon('heroicon-o-map-pin')
                            ->inlineLabel()
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->prefixIcon('heroicon-o-calendar')
                            ->default(now())
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->inlineLabel()
                            ->columnSpanFull(),
                        self::rupiahInput('fee', 'Fee')
                            ->disabled(fn() => $readonlyForApproval)
                            ->dehydrated(true)
                            ->hiddenOn('create')
                            ->disabledOn('edit'),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Scan Surat Keterangan Lunas')
                    ->schema([
                        AdvancedFileUpload::make('sk_lunas')
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
            ->columns([
                Tables\Columns\TextColumn::make('wilayah'),
                Tables\Columns\TextColumn::make('jenis_pensiun_added')
                    ->label('Jenis Pensiun')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'moduvox' => 'MODUVOX',
                        'asabri' => 'ASABRI',
                    }),
                Tables\Columns\TextColumn::make('notas'),
                Tables\Columns\TextColumn::make('nama_nasabah'),
                Tables\Columns\TextColumn::make('nik'),
                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->date(' d F Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('sk_lunas')
                    ->label('Surat Keterangan Lunas')
                    ->icon('heroicon-o-document-text')
                    ->url(fn($record) => Storage::url($record->sk_lunas))
                    ->openUrlInNewTab()
                    ->tooltip('Surat Keteranga Lunas')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('fee')
                    ->label('Fee Open Flagging')
                    ->numeric()
                    ->sortable(),
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
            ])
            // ->filters([
            //     Tables\Filters\SelectFilter::make('created_mitra')
            //         ->label('Nama Mitra')
            //         ->multiple()
            //         ->options(
            //             PermintaanOpenFlaggingTif::query()
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
                        PermintaanOpenFlaggingTif::query()
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
                            if ($user->hasRole(['maker_mitra_cabang', 'approval_mitra_cabang'])) {
                                return true;
                            }

                            // selain itu gak boleh
                            return false;
                        })
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $user = Auth::user();

                            // hanya hapus yang status request untuk role maker/approval
                            if ($user->hasRole(['maker_mitra_cabang', 'approval_mitra_cabang'])) {
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
            'index' => Pages\ListPermintaanOpenFlaggingTifs::route('/'),
            'create' => Pages\CreatePermintaanOpenFlaggingTif::route('/create'),
            'view' => Pages\ViewPermintaanOpenFlaggingTif::route('/{record}'),
            'edit' => Pages\EditPermintaanOpenFlaggingTif::route('/{record}/edit'),
        ];
    }
}
