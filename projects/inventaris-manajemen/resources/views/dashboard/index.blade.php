@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Welcome Banner --}}
<div class="mb-8 bg-gradient-to-r from-primary-800 to-primary-600 rounded-2xl p-8 text-white shadow-sm flex items-center justify-between overflow-hidden relative">
    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white opacity-5 mix-blend-overlay"></div>
    <div class="absolute bottom-0 right-32 -mb-16 w-40 h-40 rounded-full bg-white opacity-5 mix-blend-overlay"></div>
    <div class="relative z-10">
        <h2 class="text-2xl sm:text-3xl font-bold mb-2 tracking-tight">Selamat datang, {{ auth()->user()->name }}!</h2>
        <p class="text-primary-100 text-sm sm:text-base font-medium max-w-xl leading-relaxed">
            @if(auth()->user()->hasRole('Super Admin'))
                Anda memiliki akses penuh ke seluruh data konsolidasi cabang.
            @else
                Berikut adalah ringkasan aset untuk cabang {{ auth()->user()->kantor->nama ?? 'Pusat' }}.
            @endif
        </p>
    </div>
    <div class="hidden md:flex w-20 h-20 rounded-full bg-white/10 items-center justify-center backdrop-blur-sm relative z-10 border border-white/20 shadow-inner">
        <i class="fa-solid fa-chart-pie text-4xl text-white"></i>
    </div>
</div>

{{-- Stats Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <div class="card-premium flex items-start gap-5 hover:-translate-y-1">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 shadow-inner border border-blue-100">
            <i class="fa-solid fa-boxes-stacked text-xl"></i>
        </div>
        <div>
            <p class="text-[13px] font-semibold text-gray-500 mb-1 uppercase tracking-wider">Total Aset Aktif</p>
            <h3 class="text-2xl font-bold text-gray-900 tracking-tight">{{ number_format($totalAset, 0, ',', '.') }}</h3>
        </div>
    </div>

    <div class="card-premium flex items-start gap-5 hover:-translate-y-1">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 shadow-inner border border-emerald-100">
            <i class="fa-solid fa-money-bill-trend-up text-xl"></i>
        </div>
        <div>
            <p class="text-[13px] font-semibold text-gray-500 mb-1 uppercase tracking-wider">Nilai Perolehan</p>
            <h3 class="text-xl font-bold text-gray-900 tracking-tight whitespace-nowrap">{{ \App\Helpers\FormatHelper::rupiah($totalPerolehan) }}</h3>
        </div>
    </div>

    <div class="card-premium flex items-start gap-5 hover:-translate-y-1">
        <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0 shadow-inner border border-red-100">
            <i class="fa-solid fa-chart-line text-xl"></i>
        </div>
        <div>
            <p class="text-[13px] font-semibold text-gray-500 mb-1 uppercase tracking-wider">Akumulasi Susut</p>
            <h3 class="text-xl font-bold text-gray-900 tracking-tight whitespace-nowrap">{{ \App\Helpers\FormatHelper::rupiah($totalAkumulasi) }}</h3>
        </div>
    </div>

    <div class="card-premium flex items-start gap-5 hover:-translate-y-1">
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 shadow-inner border border-indigo-100">
            <i class="fa-solid fa-wallet text-xl"></i>
        </div>
        <div>
            <p class="text-[13px] font-semibold text-gray-500 mb-1 uppercase tracking-wider">Nilai Buku Bersih</p>
            <h3 class="text-xl font-bold text-gray-900 tracking-tight whitespace-nowrap">{{ \App\Helpers\FormatHelper::rupiah($totalBuku) }}</h3>
        </div>
    </div>
</div>

{{-- Charts Section --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    
    {{-- Chart Cabang --}}
    <div class="card-premium">
        <h3 class="text-lg font-bold text-gray-900 mb-6 tracking-tight">Sebaran Aset per Cabang</h3>
        <div class="relative h-72">
            <canvas id="cabangChart"></canvas>
        </div>
    </div>

    {{-- Chart Golongan --}}
    <div class="card-premium">
        <h3 class="text-lg font-bold text-gray-900 mb-6 tracking-tight">Komposisi Golongan Aset</h3>
        <div class="relative h-72 flex justify-center">
            <canvas id="golonganChart"></canvas>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data for Cabang Chart
        const cabangLabels = {!! json_encode($chartCabang->pluck('kode')->toArray()) !!};
        const cabangData = {!! json_encode($chartCabang->pluck('inventaris_count')->toArray()) !!};
        
        // Data for Golongan Chart
        const golonganLabels = {!! json_encode($chartGolongan->pluck('nama')->toArray()) !!};
        const golonganData = {!! json_encode($chartGolongan->pluck('inventaris_count')->toArray()) !!};

        // Render Bar Chart (Cabang)
        if(document.getElementById('cabangChart')) {
            new Chart(document.getElementById('cabangChart'), {
                type: 'bar',
                data: {
                    labels: cabangLabels,
                    datasets: [{
                        label: 'Jumlah Aset',
                        data: cabangData,
                        backgroundColor: '#2563eb', // primary-600
                        borderRadius: 6,
                        hoverBackgroundColor: '#1d4ed8' // primary-700
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [2, 4], color: '#f3f4f6' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Render Doughnut Chart (Golongan)
        if(document.getElementById('golonganChart')) {
            new Chart(document.getElementById('golonganChart'), {
                type: 'doughnut',
                data: {
                    labels: golonganLabels,
                    datasets: [{
                        data: golonganData,
                        backgroundColor: [
                            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#64748b'
                        ],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' }
                    },
                    cutout: '65%'
                }
            });
        }
    });
</script>
@endpush
