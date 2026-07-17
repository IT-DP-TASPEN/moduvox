@extends('admin.layout')

@section('header', 'Daftar Karyawan')

@section('content')
<div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-50 flex justify-between items-center">
        <h4 class="font-bold text-slate-800">Manajemen Data Karyawan</h4>
        <div class="flex gap-2">
            <input type="text" placeholder="Cari karyawan..." class="bg-slate-50 border-none rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-blue">
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider">
                    <th class="px-6 py-4">Karyawan</th>
                    <th class="px-6 py-4">Username</th>
                    <th class="px-6 py-4">Divisi / Jabatan</th>
                    <th class="px-6 py-4">Penempatan (Geofencing)</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($users as $user)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-slate-100 rounded-full overflow-hidden">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" alt="{{ $user->name }}">
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">
                        {{ $user->username }}
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-slate-800">{{ $user->division_name }}</p>
                        <p class="text-xs text-slate-500">{{ $user->title }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @if($user->office)
                            <span class="px-2 py-1 bg-blue-50 text-brand-blue text-[10px] font-bold rounded uppercase">
                                {{ $user->office->code }} - {{ $user->office->name }}
                            </span>
                        @else
                            <span class="px-2 py-1 bg-red-50 text-red-600 text-[10px] font-bold rounded uppercase">
                                Belum Diatur
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <button class="p-2 text-slate-400 hover:text-red-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-6 bg-slate-50 border-t border-slate-100">
        {{ $users->links() }}
    </div>
</div>
@endsection
