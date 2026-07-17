@extends('admin.layout')

@section('header', 'Manajemen Kantor')

@section('content')
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Daftar Kantor Pusat & Cabang</h3>
            <p class="text-slate-500 text-sm">Kelola lokasi dan radius absensi karyawan.</p>
        </div>
        <a href="{{ route('admin.offices.create') }}" class="bg-brand-blue text-white px-6 py-2.5 rounded-2xl text-sm font-bold hover:opacity-90 transition-all shadow-lg shadow-blue-100 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kantor
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50">
                    <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-wider">Kode</th>
                    <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Kantor</th>
                    <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-wider">Alamat</th>
                    <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-wider">Koordinat</th>
                    <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-wider">Radius</th>
                    <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($offices as $office)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-8 py-5">
                        <span class="text-xs font-bold text-brand-orange bg-orange-50 px-2 py-1 rounded-lg">{{ $office->code }}</span>
                    </td>
                    <td class="px-8 py-5">
                        <span class="font-bold text-slate-700">{{ $office->name }}</span>
                    </td>
                    <td class="px-8 py-5 text-slate-500 text-sm">{{ $office->address ?? '-' }}</td>
                    <td class="px-8 py-5">
                        <div class="text-xs font-mono text-slate-400 bg-slate-100 px-2 py-1 rounded-lg inline-block">
                            {{ $office->latitude }}, {{ $office->longitude }}
                        </div>
                    </td>
                    <td class="px-8 py-5">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-brand-blue">
                            {{ $office->radius }} Meter
                        </span>
                    </td>
                    <td class="px-8 py-5 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.offices.edit', $office) }}" class="p-2 text-slate-400 hover:text-brand-blue hover:bg-blue-50 rounded-xl transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('admin.offices.destroy', $office) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="confirmAction(event, 'Hapus kantor {{ $office->name }}?')" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
