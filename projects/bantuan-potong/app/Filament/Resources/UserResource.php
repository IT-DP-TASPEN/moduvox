<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\BranchMaster;
use App\Models\User;
use App\Models\MitraMaster;
use App\Models\MitraBranch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Master User';
    protected static ?string $pluralModelLabel = 'Master User';
    protected static ?string $modelLabel = 'Master User';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun')
                    ->columns(1)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-user-group')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-user-group')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->label('Password')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-user-group')
                            ->maxLength(255),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-user-group')
                            ->default(now()),
                        Forms\Components\Select::make('mitra_master_id')
                            ->label('Mitra Pusat')
                            ->options(MitraMaster::pluck('nama_mitra', 'id'))
                            ->searchable()
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-user-group')
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('mitra_branch_id', null);
                                $set('branch_master_id', null);
                            }),

                        /* =======================
 * CABANG MITRA
 * ======================= */
                        Forms\Components\Select::make('mitra_branch_id')
                            ->label('Cabang')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-user-group')
                            ->options(function (callable $get) {
                                $masterId = $get('mitra_master_id');
                                if (!$masterId) {
                                    return [];
                                }

                                return MitraBranch::where('mitra_master_id', $masterId)
                                    ->pluck('nama_cabang', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\Select::make('branch_master_id')
                            ->label('Cabang PT Moduvox Tech ID')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-user-group')
                            ->options(function (callable $get) {
                                $masterId = $get('mitra_master_id');
                                if (!$masterId) {
                                    return [];
                                }

                                return BranchMaster::where('mitra_master_id', $masterId)
                                    ->pluck('branch_name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->nullable(),


                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->label('Role')
                            ->inlineLabel()
                            ->prefixIcon('heroicon-o-user-group')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('mitraMaster.nama_mitra')
                    ->label('Mitra Pusat')
                    ->sortable()
                    ->placeholder('Tidak diatur')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('mitraBranch.nama_cabang')
                    ->label('Cabang Mitra')
                    ->sortable()
                    ->placeholder('Tidak diatur')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('branchMaster.branch_name')
                    ->label('Cabang PT Moduvox Tech ID')
                    ->sortable()
                    ->placeholder('Tidak diatur')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Impersonate::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
