@extends('admin.layout')

@section('header', 'Manajemen Karyawan')

@section('content')
<div class="space-y-8">
    <!-- STATS DASHBOARD -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <p class="text-slate-500 text-sm font-medium">Total Karyawan</p>
            <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ $stats['total'] }}</h3>
            <div class="mt-4 flex gap-4 text-xs">
                <span class="text-blue-600 font-bold">L: {{ $stats['male'] }}</span>
                <span class="text-pink-600 font-bold">P: {{ $stats['female'] }}</span>
            </div>
        </div>
        <div class="bg-emerald-50 p-6 rounded-3xl border border-emerald-100">
            <p class="text-emerald-700 text-sm font-medium">OJT</p>
            <h3 class="text-3xl font-bold text-emerald-800 mt-1">{{ $stats['ojt'] }}</h3>
        </div>
        <div class="bg-blue-50 p-6 rounded-3xl border border-blue-100">
            <p class="text-blue-700 text-sm font-medium">Kontrak</p>
            <h3 class="text-3xl font-bold text-blue-800 mt-1">{{ $stats['kontrak'] }}</h3>
        </div>
        <div class="bg-purple-50 p-6 rounded-3xl border border-purple-100">
            <p class="text-purple-700 text-sm font-medium">Tetap</p>
            <h3 class="text-3xl font-bold text-purple-800 mt-1">{{ $stats['tetap'] }}</h3>
            <p class="text-xs text-slate-400 mt-3">Nonaktif: <span class="font-bold text-slate-600">{{ $stats['inactive'] }}</span></p>
        </div>
    </div>

    <!-- EMPLOYEE TABLE -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/20">
            <h4 class="font-bold text-slate-800">Daftar Seluruh Karyawan</h4>
            <form action="{{ route('admin.employees.index') }}" method="GET" class="flex items-center gap-3">
                <!-- Filter Status -->
                <select name="status" class="px-4 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue bg-white">
                    <option value="">Semua Status</option>
                    <option value="OJT" {{ request('status') == 'OJT' ? 'selected' : '' }}>OJT</option>
                    <option value="Kontrak" {{ request('status') == 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                    <option value="Tetap" {{ request('status') == 'Tetap' ? 'selected' : '' }}>Tetap</option>
                    <option value="Nonaktif" {{ request('status') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                <!-- Filter Gender -->
                <select name="gender" class="px-4 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue bg-white">
                    <option value="">Semua Gender</option>
                    <option value="L" {{ request('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ request('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>

                <!-- Search -->
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama/NIP..." class="pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue w-64">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 absolute left-4 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <button type="submit" class="bg-brand-blue text-white px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-blue-100">Filter</button>
                
                @if(request()->anyFilled(['search', 'status', 'gender']))
                    <a href="{{ route('admin.employees.index') }}" class="text-slate-400 hover:text-red-500 transition-colors p-2" title="Reset Filter">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </a>
                @endif
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                    <tr>
                        <th class="px-6 py-4">Karyawan</th>
                        <th class="px-6 py-4">Gender / Umur</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Kantor</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($users as $user)
                    <tr class="hover:bg-slate-50 transition-all">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-200 overflow-hidden">
                                    <img src="{{ $user->photo_profile ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 {{ $user->trashed() ? 'line-through opacity-70' : '' }}">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $user->employee_id }}</p>
                                    @if($user->trashed())
                                        <p class="text-[10px] text-red-500 font-bold uppercase mt-0.5">Nonaktif ({{ $user->deletion_reason ?? '-' }})</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-slate-600">{{ $user->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                            <p class="text-xs text-slate-400">
                                @if($user->birth_date)
                                    {{ $user->birth_date->age }} Tahun
                                @else
                                    -
                                @endif
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase
                                @if($user->trashed()) bg-red-100 text-red-700
                                @elseif($user->employment_status == 'Tetap') bg-purple-100 text-purple-700
                                @elseif($user->employment_status == 'Kontrak') bg-blue-100 text-blue-700
                                @else bg-emerald-100 text-emerald-700 @endif">
                                {{ $user->trashed() ? 'Nonaktif' : $user->employment_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-slate-600 font-medium">{{ $user->office->name ?? 'Belum Diatur' }}</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.employees.show', $user) }}" class="text-brand-blue hover:underline text-sm font-bold">Detail Portal</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-6 border-t border-slate-50 bg-slate-50/10">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
