@extends('admin.layout')

@section('header', 'Dashboard Ringkasan')

@section('content')
<!-- Global Filter Section -->
<div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm mb-8 flex flex-wrap items-center gap-4">
    <div class="flex items-center gap-2 text-slate-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        <span class="text-xs font-bold uppercase tracking-wider">Filter Dashboard:</span>
    </div>
    <select class="bg-slate-50 border-none rounded-xl text-xs font-semibold px-4 py-2 focus:ring-2 focus:ring-brand-blue">
        <option>Semua Cabang</option>
    </select>
    <select class="bg-slate-50 border-none rounded-xl text-xs font-semibold px-4 py-2 focus:ring-2 focus:ring-brand-blue">
        <option>Semua Divisi</option>
    </select>
    <div class="flex-1 flex justify-end">
        <span class="text-xs text-slate-400 font-medium">Data Terakhir Diperbarui: {{ now()->format('d/m/Y H:i') }}</span>
    </div>
</div>

<!-- 1. Executive Summary (KPI Cards) -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm">
        <p class="text-slate-400 text-[10px] font-bold uppercase">Total Karyawan</p>
        <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $totalUsers }}</h3>
        <p class="text-[9px] text-blue-500 font-medium mt-1">Aktif di sistem</p>
    </div>
    <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm">
        <p class="text-slate-400 text-[10px] font-bold uppercase">Karyawan Baru</p>
        <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $newHiresMonth }}</h3>
        <p class="text-[9px] text-emerald-500 font-medium mt-1">Bulan berjalan</p>
    </div>
    <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm">
        <p class="text-slate-400 text-[10px] font-bold uppercase">Karyawan Resign</p>
        <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $resignMonth }}</h3>
        <p class="text-[9px] text-red-500 font-medium mt-1">Bulan berjalan</p>
    </div>
    <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm">
        <p class="text-slate-400 text-[10px] font-bold uppercase">Turnover Rate</p>
        <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $turnoverRate }}%</h3>
        <p class="text-[9px] text-orange-500 font-medium mt-1">Stabilitas SDM</p>
    </div>
    <div class="bg-indigo-600 p-4 rounded-3xl text-white shadow-lg shadow-indigo-100">
        <p class="text-indigo-200 text-[10px] font-bold uppercase">Hadir Hari Ini</p>
        <h3 class="text-2xl font-black mt-1">{{ $totalAttendanceToday }}</h3>
        <p class="text-[9px] text-indigo-100 font-medium mt-1">{{ $totalUsers > 0 ? round(($totalAttendanceToday/$totalUsers)*100) : 0 }}% Partisipasi</p>
    </div>
    <div class="bg-emerald-600 p-4 rounded-3xl text-white shadow-lg shadow-emerald-100">
        <p class="text-emerald-200 text-[10px] font-bold uppercase">Cuti & Izin</p>
        <h3 class="text-2xl font-black mt-1">{{ $onLeaveToday + $onPermitToday }}</h3>
        <p class="text-[9px] text-emerald-100 font-medium mt-1">Sedang Tidak Aktif</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- 2. Absensi & Kehadiran Insight -->
    <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h4 class="font-bold text-slate-800 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                Tren Kehadiran (30 Hari Terakhir)
            </h4>
            <div class="flex gap-4 text-[10px] font-bold">
                <div class="flex items-center gap-1 text-slate-500">
                    <span class="w-2 h-2 bg-indigo-500 rounded-full"></span> KEHADIRAN
                </div>
            </div>
        </div>
        <div class="relative" style="height: 300px;">
            <canvas id="attendanceTrendChart"></canvas>
        </div>
    </div>

    <div class="lg:col-span-1 space-y-6">
        <!-- Rata-rata Jam -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-slate-900 p-6 rounded-3xl text-white">
                <p class="text-slate-400 text-[10px] font-bold uppercase">Avg Check-In</p>
                <h4 class="text-2xl font-black mt-1">{{ $avgCheckIn }}</h4>
                <p class="text-[9px] text-slate-500 mt-2">Waktu rata-rata</p>
            </div>
            <div class="bg-slate-900 p-6 rounded-3xl text-white">
                <p class="text-slate-400 text-[10px] font-bold uppercase">Avg Check-Out</p>
                <h4 class="text-2xl font-black mt-1">{{ $avgCheckOut }}</h4>
                <p class="text-[9px] text-slate-500 mt-2">Waktu rata-rata</p>
            </div>
        </div>

        <!-- 8. Leaderboard & Behavioral Insight -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h4 class="font-bold text-slate-800 text-sm uppercase">Leaderboard Kedisiplinan</h4>
                <div class="flex gap-1">
                    <span class="w-1 h-1 bg-brand-blue rounded-full"></span>
                    <span class="w-1 h-1 bg-brand-blue/30 rounded-full"></span>
                </div>
            </div>
            
            <!-- Tabs/Toggle style -->
            <div class="space-y-6">
                <div>
                    <p class="text-[10px] font-black text-emerald-500 uppercase mb-3 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-7.714 2.143L11 21l-2.286-6.857L1 12l7.714-2.143L11 3z" />
                        </svg>
                        Paling Disiplin (Early Birds)
                    </p>
                    <div class="space-y-3">
                        @foreach($earlyBirds as $bird)
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-[9px] font-black border border-emerald-100">
                                {{ gmdate("H:i", $bird->avg_time) }}
                            </div>
                            <div class="flex-1">
                                <p class="text-[11px] font-bold text-slate-800">{{ $bird->user->name }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-50">
                    <p class="text-[10px] font-black text-red-500 uppercase mb-3 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Paling Sering Terlambat
                    </p>
                    <div class="space-y-3">
                        @foreach($topLatecomers as $late)
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-[9px] font-black border border-red-100">
                                {{ $late->total }}x
                            </div>
                            <div class="flex-1">
                                <p class="text-[11px] font-bold text-slate-800">{{ $late->user->name }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Divisi Paling Rajin Widget -->
        @if($divisionAttendance)
        <div class="bg-indigo-600 p-6 rounded-3xl text-white shadow-xl shadow-indigo-100 relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-indigo-200 text-[10px] font-bold uppercase tracking-widest">🏆 Divisi Paling Rajin</p>
                <h4 class="text-xl font-black mt-2">{{ strtoupper($divisionAttendance->division_name) }}</h4>
                <div class="mt-6">
                    <div class="flex justify-between items-end mb-2">
                        <span class="text-xs font-bold">{{ round($divisionAttendance->rate) }}% Kehadiran</span>
                        <span class="text-[10px] opacity-70">Target: 100%</span>
                    </div>
                    <div class="w-full h-2 bg-white/20 rounded-full overflow-hidden">
                        <div class="h-full bg-white rounded-full shadow-[0_0_10px_rgba(255,255,255,0.5)]" style="width: {{ $divisionAttendance->rate }}%"></div>
                    </div>
                </div>
            </div>
            <!-- Decorative Circle -->
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
        </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
    <!-- 3. Distribusi Karyawan & Cuti -->
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
        <h4 class="font-bold text-slate-800 text-sm mb-6 uppercase tracking-wider">Per Divisi</h4>
        <div class="relative" style="height: 200px;">
            <canvas id="divisionPie"></canvas>
        </div>
    </div>
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
        <h4 class="font-bold text-slate-800 text-sm mb-6 uppercase tracking-wider">Per Cabang</h4>
        <div class="relative" style="height: 200px;">
            <canvas id="officePie"></canvas>
        </div>
    </div>
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
        <h4 class="font-bold text-slate-800 text-sm mb-6 uppercase tracking-wider">Jenis Cuti</h4>
        <div class="relative" style="height: 200px;">
            <canvas id="leavePie"></canvas>
        </div>
    </div>
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
        <h4 class="font-bold text-slate-800 text-sm mb-6 uppercase tracking-wider">Gender</h4>
        <div class="relative" style="height: 200px;">
            <canvas id="genderPie"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- 7. Alerts & Operations -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <h4 class="font-bold text-slate-800 text-sm mb-6 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Alert Operasional
            </h4>
            <div class="space-y-3">
                @if($totalPending > 0)
                <div class="p-3 bg-red-50 rounded-2xl flex items-center gap-3">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <p class="text-[10px] font-bold text-red-700">{{ $totalPending }} Pengajuan menunggu approval</p>
                </div>
                @endif
                
                @foreach($expiringContracts as $contract)
                <div class="p-3 bg-orange-50 rounded-2xl flex items-center gap-3">
                    <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                    <p class="text-[10px] font-bold text-orange-700">Kontrak {{ $contract->user->name }} habis dalam {{ \Carbon\Carbon::parse($contract->contract_end_date)->diffForHumans() }}</p>
                </div>
                @endforeach
                
                <div class="p-3 bg-blue-50 rounded-2xl flex items-center gap-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    <p class="text-[10px] font-bold text-blue-700">{{ $birthdaysThisMonth->count() }} Karyawan ulang tahun bulan ini</p>
                </div>
            </div>
        </div>

        <!-- Expanded Payroll Insight Widget -->
        <div class="bg-slate-900 p-6 rounded-3xl text-white shadow-xl relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Total Gaji Bulan Ini</p>
                        <h4 class="text-2xl font-black mt-1">Rp {{ number_format($totalPayroll, 0, ',', '.') }}</h4>
                    </div>
                    <div class="bg-emerald-500/20 p-2 rounded-xl border border-emerald-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <div class="p-4 bg-white/5 rounded-2xl border border-white/10 mb-6">
                    <p class="text-slate-500 text-[10px] font-bold uppercase">Total Lembur</p>
                    <p class="text-lg font-bold text-amber-400 mt-1">Rp {{ number_format($totalOvertimePay, 0, ',', '.') }}</p>
                </div>

                <div class="space-y-3">
                    <p class="text-[10px] font-bold text-slate-500 uppercase mb-2">Rata-rata Gaji per Divisi</p>
                    <div class="max-h-[150px] overflow-y-auto pr-2 space-y-2">
                        @foreach($avgSalaryPerDivision as $avg)
                        <div class="flex justify-between items-center text-[11px]">
                            <span class="text-slate-400 truncate pr-4">{{ $avg->division_name }}</span>
                            <span class="font-bold text-slate-200">Rp {{ number_format($avg->avg_salary, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <a href="{{ route('admin.salaries.index') }}" class="mt-6 block w-full text-center py-3 bg-white text-slate-900 rounded-2xl text-[10px] font-black uppercase tracking-wider hover:bg-slate-100 transition-all">Manajemen Payroll</a>
            </div>
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl"></div>
        </div>
    </div>

    <!-- 9. Recent Activity Timeline -->
    <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
        <div class="flex justify-between items-center mb-8">
            <h4 class="font-bold text-slate-800">Timeline Aktivitas Terbaru</h4>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Live Log</span>
        </div>
        <div class="relative space-y-6">
            <div class="absolute left-4 top-2 bottom-0 w-0.5 bg-slate-50"></div>
            
            @foreach($recentActivities as $activity)
            <div class="relative flex items-center gap-6 pl-10">
                <div class="absolute left-3 w-2.5 h-2.5 rounded-full {{ $activity->type == 'masuk' ? 'bg-blue-500' : 'bg-orange-500' }} border-2 border-white shadow-sm"></div>
                <div class="flex items-center gap-3 flex-1">
                    <div class="w-8 h-8 rounded-full bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                        <img src="{{ $activity->user->photo_profile ?? 'https://ui-avatars.com/api/?name='.urlencode($activity->user->name) }}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-slate-800 font-bold">
                            {{ $activity->user->name }} 
                            <span class="font-medium text-slate-400">telah melakukan</span> 
                            {{ strtoupper($activity->type) }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-slate-800">{{ $activity->created_at->format('H:i') }}</p>
                    <p class="text-[9px] font-bold text-slate-400">{{ $activity->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Premium Chart.js Configuration
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = "#64748b";
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.9)';
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 12;
    Chart.defaults.plugins.tooltip.titleFont = { size: 13, weight: 'bold' };
    Chart.defaults.plugins.tooltip.bodyFont = { size: 12 };

    const ctxTrend = document.getElementById('attendanceTrendChart').getContext('2d');
    const gradientTrend = ctxTrend.createLinearGradient(0, 0, 0, 300);
    gradientTrend.addColorStop(0, 'rgba(99, 102, 241, 0.2)');
    gradientTrend.addColorStop(1, 'rgba(99, 102, 241, 0)');

    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($attendanceTrend, 'date')) !!},
            datasets: [{
                label: 'Kehadiran',
                data: {!! json_encode(array_column($attendanceTrend, 'count')) !!},
                borderColor: '#6366f1',
                backgroundColor: gradientTrend,
                fill: true,
                tension: 0.45,
                borderWidth: 4,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#6366f1',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#f1f5f9', drawBorder: false },
                    ticks: { padding: 10 }
                },
                x: { 
                    grid: { display: false },
                    ticks: { padding: 10 }
                }
            }
        }
    });

    // Division Chart with Vibrant Colors
    new Chart(document.getElementById('divisionPie'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($divisionStats->pluck('division_name')) !!},
            datasets: [{
                data: {!! json_encode($divisionStats->pluck('total')) !!},
                backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6', '#0ea5e9'],
                borderWidth: 5,
                borderColor: '#ffffff',
                hoverOffset: 15
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { legend: { display: false } }
        }
    });

    // Office Chart
    new Chart(document.getElementById('officePie'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($officeStats->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($officeStats->pluck('users_count')) !!},
                backgroundColor: ['#005596', '#3b82f6', '#93c5fd', '#dbeafe'],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    // Leave Types Chart
    new Chart(document.getElementById('leavePie'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($leaveTypes->pluck('type')) !!},
            datasets: [{
                data: {!! json_encode($leaveTypes->pluck('total')) !!},
                backgroundColor: ['#f43f5e', '#fb923c', '#fbbf24', '#2dd4bf'],
                borderWidth: 5,
                borderColor: '#ffffff'
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { legend: { display: false } }
        }
    });

    // Gender Chart
    new Chart(document.getElementById('genderPie'), {
        type: 'pie',
        data: {
            labels: ['Pria', 'Wanita'],
            datasets: [{
                data: [{{ $genderStats['L'] }}, {{ $genderStats['P'] }}],
                backgroundColor: ['#3b82f6', '#f472b6'],
                borderWidth: 5,
                borderColor: '#ffffff'
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
</script>
@endsection
