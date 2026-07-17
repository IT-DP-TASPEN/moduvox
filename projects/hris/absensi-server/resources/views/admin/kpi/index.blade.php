@extends('admin.layout')

@section('header', 'Input KPI Staff')

@section('content')
<div class="admin-card">
    <div class="p-6 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-4">
            <div>
                <h4 class="font-bold text-slate-800 leading-none">Daftar Penilaian KPI</h4>
                <p class="text-[10px] text-slate-400 mt-1 font-medium uppercase tracking-wider">Periode: {{ Carbon\Carbon::create(null, $month)->translatedFormat('F') }} {{ $year }}</p>
            </div>
            <a href="{{ route('admin.kpi-indicators.index') }}" class="text-[10px] font-bold text-blue-500 hover:bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 transition-all uppercase tracking-tight">
                ⚙️ Pengaturan Indikator
            </a>
        </div>
        
        <form action="{{ route('admin.kpi.index') }}" method="GET" class="flex flex-wrap items-center gap-1.5">
            <!-- Search -->
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..." class="admin-input pl-11 w-36">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <select name="month" class="admin-input w-28">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
            <select name="year" class="admin-input w-24">
                @foreach(range(now()->year - 1, now()->year + 1) as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary">Filter</button>

            @if(request()->anyFilled(['search', 'month', 'year']))
                <a href="{{ route('admin.kpi.index') }}" class="bg-slate-100 text-slate-400 hover:text-red-500 transition-colors p-2 rounded-xl" title="Reset Filter">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </a>
            @endif
        </form>
    </div>

    <form action="{{ route('admin.kpi.store') }}" method="POST">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-[10px] font-black uppercase tracking-wider">
                        <th class="px-6 py-4">Karyawan</th>
                        <th class="px-6 py-4">Divisi</th>
                        <th class="px-6 py-4" style="width: 120px;">Skor (0-100)</th>
                        <th class="px-6 py-4">Grade</th>
                        <th class="px-6 py-4">Catatan Performa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($staff as $s)
                    @php $kpi = $kpiMap[$s->id] ?? null; @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-400">
                                    {{ substr($s->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $s->name }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $s->employee_id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs font-medium text-slate-600">
                            {{ $s->division_name }}
                        </td>
                        <td class="px-6 py-4">
                            <input type="number" step="0.01" min="0" max="100" 
                                name="kpi[{{ $s->id }}][score]" 
                                value="{{ $kpi ? $kpi->score : '' }}"
                                placeholder="0.00"
                                class="w-full bg-slate-50 border-none rounded-lg text-sm font-bold px-3 py-2 focus:ring-2 focus:ring-brand-blue text-center">
                        </td>
                        <td class="px-6 py-4">
                            @if($kpi)
                            <span class="px-3 py-1 text-[10px] font-black rounded-full 
                                {{ $kpi->grade == 'A' ? 'bg-green-100 text-green-700' : ($kpi->grade == 'B' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700') }}">
                                {{ $kpi->grade }}
                            </span>
                            @else
                            <span class="text-[10px] text-slate-300 italic font-bold">BELUM DIISI</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <textarea name="kpi[{{ $s->id }}][notes]" rows="1"
                                placeholder="Tambahkan catatan..."
                                class="w-full bg-slate-50 border-none rounded-lg text-xs px-3 py-2 focus:ring-2 focus:ring-brand-blue">{{ $kpi ? $kpi->notes : '' }}</textarea>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-slate-50">
            {{ $staff->links() }}
        </div>

        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end">
            <button type="submit" class="btn-primary px-10 py-4 text-sm shadow-xl shadow-blue-100">
                Simpan Semua KPI
            </button>
        </div>
    </form>
</div>
@endsection
