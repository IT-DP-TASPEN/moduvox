<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use App\Filament\Widgets\AccountWidget;

use App\Filament\Widgets\BanpotStatusStat;
use App\Filament\Widgets\BanpotStatusStats;
use App\Filament\Widgets\CheckingStatusStats;
use App\Filament\Widgets\EstimasiStatusStats;
use App\Filament\Widgets\FlaggingStatusStats;
use App\Filament\Widgets\MutasiTosStatusStats;
use App\Filament\Widgets\CheckingPerbulanChart;
use App\Filament\Widgets\EstimasiPerbulanChart;
use App\Filament\Widgets\FlaggingPerbulanChart;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Widgets\MutasiTosPerbulanChart;
use App\Filament\Widgets\OpenFlaggingStatusStats;
use App\Filament\Widgets\BanpotStatusPerbulanChart;
use App\Filament\Widgets\FlaggingMutasiStatusStats;
use App\Filament\Widgets\OpenFlaggingPerbulanChart;
use App\Filament\Widgets\FlaggingMutasiPerbulanChart;
use App\Filament\Widgets\PembukaanRekeningStatusStats;
use App\Filament\Widgets\RekeningTabunganPerbulanChart;

class WidgetSelector extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.widget-selector';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard Selector';
    protected static string $routePath = '/';
    protected static ?int $navigationSort = -3;

    public string $selectedWidget;

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->hasAnyRole([
            'super_admin',
            'staff_bosche',
            'approval_mitra_pusat',
            'maker_mitra_pusat',
        ])) {
            $this->selectedWidget = '1'; // Default ke Banpot
        } else {
            $this->selectedWidget = '2'; // Default ke Tabungan
        }
    }

    public function getFormSchema(): array
    {
        $user = Auth::user();

        // Default kosong
        $options = [];

        if ($user->hasAnyRole(['super_admin', 'staff_bosche', 'approval_mitra_pusat', 'maker_mitra_pusat'])) {
            $options = [
                '1' => 'Banpot',
                '2' => 'Savings Account',
                '3' => 'Open Flagging',
                '4' => 'Flagging TIF',
                '5' => 'Flagging and Mutasi TIF',
                // '6' => 'Mutasi TOS',
                '7' => 'Checking',
                '8' => 'Estimasi',
            ];
        } elseif ($user->hasAnyRole(['approval_mitra_pusat', 'maker_mitra_pusat'])) {
            $options = [
                '1' => 'Banpot',
            ];
        } elseif ($user->hasAnyRole(['approval_mitra_cabang', 'maker_mitra_cabang'])) {
            $options = [
                '2' => 'Savings Account',
                '3' => 'Open Flagging',
                '4' => 'Flagging TIF',
                '5' => 'Flagging and Mutasi TIF',
                // '6' => 'Mutasi TOS',
                '7' => 'Checking',
                '8' => 'Estimasi',
            ];
        } else {
            $options = [];
        }

        return [
            Select::make('selectedWidget')
                ->label('Pilih Dashboard')
                ->options($options)
                ->reactive()
                ->required(),
        ];
    }

    public function getRenderedWidget()
    {
        return match ($this->selectedWidget) {
            '1' => [
                BanpotStatusStat::class,
                BanpotStatusPerbulanChart::class,
            ],
            '2' => [
                PembukaanRekeningStatusStats::class,
                RekeningTabunganPerbulanChart::class,
            ],
            '3' => [
                OpenFlaggingStatusStats::class,
                OpenFlaggingPerbulanChart::class,
            ],
            '4' => [
                FlaggingStatusStats::class,
                FlaggingPerbulanChart::class,
            ],
            '5' => [
                FlaggingMutasiStatusStats::class,
                FlaggingMutasiPerbulanChart::class,
            ],
            // '6' => [
            //     MutasiTosStatusStats::class,
            //     MutasiTosPerbulanChart::class,
            // ],
            '7' => [
                CheckingStatusStats::class,
                CheckingPerbulanChart::class,
            ],
            '8' => [
                EstimasiStatusStats::class,
                EstimasiPerbulanChart::class,
            ],
            default => [],
        };
    }
}
