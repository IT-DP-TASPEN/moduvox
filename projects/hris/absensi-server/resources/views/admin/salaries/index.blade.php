@extends('admin.layout')

@section('header', 'Manajemen Gaji (Payroll)')

@section('actions')
<div class="flex gap-3">
    <form action="{{ route('admin.salaries.generate-all') }}" method="POST" onsubmit="confirmAction(event, 'Sistem akan menghitung ulang gaji seluruh karyawan berdasarkan data KPI dan Lembur bulan ini. Lanjutkan?')">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">
        <button type="submit" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Generate Semua Gaji (Auto)
        </button>
    </form>
    <a href="{{ route('admin.salaries.create') }}" class="btn-secondary">
        Manual Entry
    </a>
</div>
@endsection

@section('content')
<!-- Payroll Summary Header -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
    <!-- Main KPI Card -->
    <div class="lg:col-span-1 bg-slate-900 p-6 rounded-3xl text-white shadow-xl shadow-slate-100 flex flex-col justify-between">
        <div>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Total Gaji (Net)</p>
            <h3 class="text-2xl font-black mt-1">Rp {{ number_format($summary['total_net'], 0, ',', '.') }}</h3>
        </div>
        <div class="mt-4 flex items-center gap-2 text-[10px] text-emerald-400 font-bold bg-emerald-400/10 px-2 py-1 rounded-lg w-fit">
            PERIODE: {{ Carbon\Carbon::create(null, $month)->translatedFormat('F') }} {{ $year }}
        </div>
    </div>

    <!-- Stats Row -->
    <div class="lg:col-span-1 admin-card p-6 flex flex-col justify-between border-none">
        <div>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Total Lembur</p>
            <h3 class="text-2xl font-black text-slate-800 mt-1">Rp {{ number_format($summary['total_overtime'], 0, ',', '.') }}</h3>
        </div>
        <p class="text-[9px] text-slate-400 mt-4">Akumulasi upah & makan lembur</p>
    </div>

    <div class="lg:col-span-1 admin-card p-6 flex flex-col justify-between border-none">
        <div>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Jumlah Slip Gaji</p>
            <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $summary['total_count'] }} Slip</h3>
        </div>
        <p class="text-[9px] text-slate-400 mt-4">Karyawan terproses periode ini</p>
    </div>

    <!-- History Trend Small -->
    <div class="lg:col-span-1 admin-card p-4 border-none">
        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-2">Trend 6 Bulan Terakhir</p>
        <div class="h-24">
            <canvas id="payrollTrendChart"></canvas>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="p-6 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
        <form action="{{ route('admin.salaries.index') }}" method="GET" class="flex flex-wrap gap-2">
            <select name="month" class="admin-input">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
            <select name="year" class="admin-input">
                @foreach(range(now()->year - 1, now()->year + 1) as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>

            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama/NIP..." class="admin-input pl-10 w-48">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-slate-400 absolute left-4 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <select name="status" class="admin-input">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
            </select>

            <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-slate-700 transition-all">Filter</button>
            
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.salaries.index', ['month' => $month, 'year' => $year]) }}" class="bg-slate-100 text-slate-400 hover:text-red-500 transition-colors p-2 rounded-xl" title="Reset Filter">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </a>
            @endif
        </form>

        <div class="flex gap-2">
            <form action="{{ route('admin.salaries.publish') }}" method="POST" onsubmit="confirmAction(event, 'Apakah Anda yakin ingin mempublikasikan semua slip gaji periode ini ke aplikasi mobile?')">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <button type="submit" class="bg-brand-orange text-white px-4 py-2 rounded-xl text-xs font-bold hover:opacity-90 transition-all shadow-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Publish ke Mobile
                </button>
            </form>

            <form action="{{ route('admin.salaries.disburse') }}" method="POST" onsubmit="confirmAction(event, 'Apakah Anda yakin ingin mencairkan (bayar) seluruh gaji periode ini? Status akan berubah menjadi PAID.')">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:opacity-90 transition-all shadow-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Cairkan Gaji (Bulk)
                </button>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="admin-table-thead">
                    <th class="px-6 py-4">Karyawan</th>
                    <th class="px-6 py-4 text-right">Gaji Pokok</th>
                    <th class="px-6 py-4 text-right">Total Pendapatan</th>
                    <th class="px-6 py-4 text-right">Take Home Pay</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($salaries as $s)
                <tr class="admin-table-row">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-400">
                                {{ substr($s->user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $s->user->name }}</p>
                                <p class="text-[10px] text-slate-500">{{ $s->user->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-600 text-right">
                        Rp {{ number_format($s->basic_salary, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-600 text-right">
                        Rp {{ number_format($s->total_earnings, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 text-sm font-bold text-brand-blue text-right">
                        Rp {{ number_format($s->net_salary, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 text-[10px] font-black rounded-lg {{ $s->status == 'paid' ? 'bg-emerald-100 text-emerald-700' : ($s->status == 'published' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500') }}">
                            {{ strtoupper($s->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.salaries.show', $s->id) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-600 px-3 py-1.5 rounded-xl text-[10px] font-bold hover:bg-slate-200 transition-all shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Lihat
                            </a>

                            @if(in_array($s->status, ['published', 'paid']))
                            <a href="{{ route('admin.salaries.download-slip', $s->id) }}" class="inline-flex items-center gap-1.5 bg-brand-blue text-white px-3 py-1.5 rounded-xl text-[10px] font-bold hover:opacity-90 transition-all shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Unduh
                            </a>
                            @else
                            <span class="text-[10px] text-slate-400 italic">Belum Publish</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-6 border-t border-slate-50">
        {{ $salaries->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('payrollTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($historyTrend, 'label')) !!},
            datasets: [{
                data: {!! json_encode(array_column($historyTrend, 'total')) !!},
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.05)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { display: false },
                y: { display: false }
            }
        }
    });
</script>
@endpush
