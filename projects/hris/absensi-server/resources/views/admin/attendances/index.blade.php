@extends('admin.layout')

@section('header', 'Riwayat Kehadiran')

@section('content')
<div class="admin-card">
    <div class="p-6 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
        <h4 class="font-bold text-slate-800">Monitoring Kehadiran Pegawai</h4>
        
        <form action="{{ route('admin.attendances.index') }}" method="GET" class="flex flex-wrap items-center gap-1.5">
            <!-- Search -->
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..." class="admin-input pl-11 w-36">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Tipe -->
            <select name="type" class="admin-input w-24">
                <option value="">Tipe</option>
                <option value="masuk" {{ request('type') == 'masuk' ? 'selected' : '' }}>Masuk</option>
                <option value="pulang" {{ request('type') == 'pulang' ? 'selected' : '' }}>Pulang</option>
            </select>

            <!-- Bulan -->
            <select name="month" class="admin-input w-28">
                <option value="">Bulan</option>
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>

            <!-- Tahun -->
            <select name="year" class="admin-input w-24">
                <option value="">Tahun</option>
                @foreach(range(date('Y'), date('Y')-2) as $y)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>

            <!-- Tanggal -->
            <input type="date" name="date" value="{{ request('date') }}" class="admin-input w-36">

            <button type="submit" class="btn-primary">Filter</button>
            
            @if(request()->anyFilled(['search', 'type', 'date', 'month', 'year']))
                <a href="{{ route('admin.attendances.index') }}" class="bg-slate-100 text-slate-400 hover:text-red-500 transition-colors p-2 rounded-xl" title="Reset Filter">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </a>
            @endif
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="admin-table-thead">
                    <th class="px-6 py-4">Karyawan</th>
                    <th class="px-6 py-4 text-center">Tipe</th>
                    <th class="px-6 py-4 text-center">Waktu & Lokasi</th>
                    <th class="px-6 py-4 text-center">Bukti Foto</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($attendances as $attendance)
                <tr class="admin-table-row">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-[10px] font-bold text-slate-400 uppercase">
                                {{ substr($attendance->user->name, 0, 2) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800 leading-none">{{ $attendance->user->name }}</p>
                                <p class="text-[10px] text-slate-500 mt-1">{{ $attendance->user->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="badge {{ $attendance->type == 'masuk' ? 'badge-info' : 'badge-warning' }}">
                            {{ strtoupper($attendance->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <p class="text-sm font-medium text-slate-800 leading-none">{{ $attendance->created_at->format('d M Y, H:i') }}</p>
                        <div class="flex justify-center">
                            <a href="https://www.google.com/maps?q={{ $attendance->latitude }},{{ $attendance->longitude }}" target="_blank" class="text-[10px] text-indigo-600 hover:underline flex items-center gap-1 mt-1 font-black uppercase bg-indigo-50 px-2 py-0.5 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                MAPS
                            </a>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center">
                            @if($attendance->photo_path)
                            <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-100 overflow-hidden cursor-pointer hover:ring-2 hover:ring-indigo-500 transition-all shadow-sm group relative">
                                <img src="{{ asset('storage/' . $attendance->photo_path) }}" alt="Foto Absen" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                            </div>
                            @else
                            <span class="text-[10px] text-slate-400 italic font-medium">No Image</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button class="p-2 text-slate-400 hover:bg-slate-50 hover:text-indigo-600 rounded-xl transition-all border border-transparent hover:border-slate-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                            </svg>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-6 border-t border-slate-50">
        {{ $attendances->links() }}
    </div>
</div>
@endsection
