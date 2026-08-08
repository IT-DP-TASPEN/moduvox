<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CekNotasResource\Pages;
use App\Models\CekNotasAll;
use App\Models\MitraMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CekNotasResource extends Resource
{
    protected static ?string $model = CekNotasAll::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass-circle';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?string $navigationLabel = 'Monitoring Notas';
    protected static ?string $pluralModelLabel = 'Monitoring Notas';
    protected static ?string $modelLabel = 'Monitoring Notas';
    protected static ?string $slug = 'cek-notas';
    protected static ?int $navigationSort = 99;

    /**
     * Logika akses:
     * - Tabel INTERNAL (source_type='internal'): filter by mitra_master_id
     * - Tabel MITRA (source_type='mitra'): filter by created_mitra = nama_mitra user
     * - super_admin/staff_bosche tanpa mitra → lihat semua
     */
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $isAdmin = $user?->hasRole(['super_admin', 'staff_bosche']);
        $mitraId = $user?->mitra_master_id;
        $mitraNama = $user?->mitraMaster?->nama_mitra;

        $query = CekNotasAll::query()->with(['mitraMaster', 'creator.mitraMaster']);

        // Super admin / staff_bosche tanpa mitra → lihat semua
        if ($isAdmin && !$mitraId) {
            return $query;
        }

        $query->where(function (Builder $q) use ($mitraId, $mitraNama) {
            // Internal records
            if ($mitraId) {
                $q->orWhere(function (Builder $q2) use ($mitraId) {
                    $q2->where('source_type', 'internal')
                        ->where('mitra_master_id', $mitraId);
                });
            }
            // Non-internal (mitra) records
            if ($mitraNama) {
                $q->orWhere(function (Builder $q2) use ($mitraNama) {
                    $q2->where('source_type', 'mitra')
                        ->where('created_mitra', $mitraNama);
                });
            }
        });

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\BadgeColumn::make('jenis_layanan')
                    ->label('Jenis Layanan')
                    ->colors([
                        'info' => 'Cek',
                        'success' => 'Estimasi',
                        'warning' => 'Flagging TIF',
                        'danger' => 'Flagging Mutasi',
                        'primary' => 'Open Flagging',
                    ]),

                Tables\Columns\TextColumn::make('notas')
                    ->label('Notas')
                    ->copyable(),

                Tables\Columns\TextColumn::make('nama_nasabah')
                    ->label('Nama Nasabah'),


                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'primary' => 'request',
                        'success' => fn($state) => in_array($state, ['approved', 'approved_mitra', 'success']),
                        'danger' => fn($state) => in_array($state, ['rejected', 'rejected_mitra', 'failed']),
                        'secondary' => fn($state) => in_array($state, ['canceled', 'complete']),
                        'info' => 'on_process',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'request' => 'Request',
                        'approved' => 'Approved',
                        'approved_mitra' => 'Approved Mitra',
                        'rejected' => 'Rejected',
                        'rejected_mitra' => 'Rejected Mitra',
                        'canceled' => 'Canceled',
                        'on_process' => 'On Process',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'complete' => 'Complete',
                        default => ucfirst(str_replace('_', ' ', $state ?? '')),
                    }),

                Tables\Columns\IconColumn::make('bukti_hasil')
                    ->label('Bukti Hasil Proses')
                    ->placeholder('Belum tersedia')
                    ->icon('heroicon-o-document-text')
                    ->url(
                        fn($record) => $record->bukti_hasil
                        ? Storage::url($record->bukti_hasil)
                        : null
                    )
                    ->openUrlInNewTab()
                    ->tooltip('Bukti Hasil')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('nama_mitra_display')
                    ->label('Mitra')
                    ->placeholder('Tidak diatur')
                    ->getStateUsing(function ($record) {
                        // Internal: ambil dari relasi mitra_master_id langsung
                        if ($record->source_type === 'internal') {
                            return $record->mitraMaster?->nama_mitra;
                        }
                        // Eksternal: ambil dari user pembuat (created_by) → mitra_master_id → nama_mitra
                        return $record->creator?->mitraMaster?->nama_mitra;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d F Y  H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d F Y  H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_layanan')
                    ->label('Jenis Layanan')
                    ->options([
                        'Cek' => 'Cek',
                        'Estimasi' => 'Estimasi',
                        'Flagging TIF' => 'Flagging TIF',
                        'Flagging Mutasi' => 'Flagging Mutasi',
                        'Open Flagging' => 'Open Flagging',
                    ])
                    ->multiple()
                    ->preload(),


                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'request' => 'Request',
                        'approved' => 'Approved',
                        'approved_mitra' => 'Approved Mitra',
                        'rejected' => 'Rejected',
                        'rejected_mitra' => 'Rejected Mitra',
                        'canceled' => 'Canceled',
                        'on_process' => 'On Process',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'complete' => 'Complete',
                    ])
                    ->preload(),

                Tables\Filters\Filter::make('notas')
                    ->form([
                        Forms\Components\TextInput::make('notas')
                            ->label('Nomor Notas')
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
                Tables\Filters\Filter::make('mitra_master_id')
                    ->form([
                        Forms\Components\Select::make('mitra_master_id')
                            ->label('Mitra')
                            ->visible(Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('staff_bosche') || Auth::user()->hasRole('admin_support') || Auth::user()->hasRole('open_table'))
                            ->options(
                                MitraMaster::where('is_sinergi', true)
                                    ->pluck('nama_mitra', 'id')
                            )
                            ->placeholder('Pilih Mitra')
                            ->searchable(),
                    ])
                    ->query(
                        fn(Builder $query, array $data) =>
                        $query->when(
                            $data['mitra_master_id'] ?? null,
                            fn($q, $v) => $q->where('mitra_master_id', $v)
                        )
                    ),
                // Tables\Filters\Filter::make('created_at')
                //     ->label('Tanggal')
                //     ->form([
                //         Forms\Components\Grid::make(2)->schema([
                //             Forms\Components\DatePicker::make('created_from')->label('Dari'),
                //             Forms\Components\DatePicker::make('created_until')->label('Sampai'),
                //         ]),
                //     ])
                //     ->query(
                //         fn(Builder $query, array $data) =>
                //         $query
                //             ->when($data['created_from'] ?? null, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
                //             ->when($data['created_until'] ?? null, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
                //     ),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(5)
            ->actions([])
            ->bulkActions([])
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCekNotas::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
    public static function canEdit($record): bool
    {
        return false;
    }
    public static function canDelete($record): bool
    {
        return false;
    }
}
