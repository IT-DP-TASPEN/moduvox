@extends('layouts.admin')
@section('title','Daftar Lowongan Kerja')
@section('page-title','Karir & Talenta')
@section('page-subtitle','Kelola lowongan pekerjaan untuk menarik talenta terbaik.')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.careers.create') }}" class="bg-red-700 hover:bg-red-800 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-all flex items-center gap-2 shadow-sm">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Tambah Lowongan
        </a>
        <a href="{{ route('admin.pages.career') }}" class="bg-white border border-slate-200 text-slate-700 px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
            <span class="material-symbols-outlined text-[20px]">settings</span>
            Pengaturan Halaman
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100">
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Posisi & Tipe</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Lokasi</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Status</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Tanggal Post</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($careers as $career)
            <tr class="hover:bg-slate-50/30 transition-colors group">
                <td class="px-6 py-4">
                    <div class="font-bold text-slate-800">{{ $career->title }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">{{ $career->type }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <span class="material-symbols-outlined text-[16px] text-slate-300">location_on</span>
                        {{ $career->location ?? 'Head Office' }}
                    </div>
                </td>
                <td class="px-6 py-4">
                    <form action="{{ route('admin.careers.toggle', $career) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest transition-all {{ $career->is_active ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $career->is_active ? 'bg-green-500 animate-pulse' : 'bg-slate-400' }}"></span>
                            {{ $career->is_active ? 'Aktif' : 'Non-aktif' }}
                        </button>
                    </form>
                </td>
                <td class="px-6 py-4 text-sm text-slate-500">
                    {{ $career->created_at->format('d M Y') }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.careers.edit', $career) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </a>
                        <form action="{{ route('admin.careers.destroy', $career) }}" method="POST" onsubmit="return confirm('Hapus lowongan ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-20 text-center">
                    <div class="flex flex-col items-center opacity-20">
                        <span class="material-symbols-outlined text-6xl">person_search</span>
                        <p class="mt-2 font-medium">Belum ada lowongan yang ditambahkan.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($careers->hasPages())
    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
        {{ $careers->links() }}
    </div>
    @endif
</div>
@endsection

