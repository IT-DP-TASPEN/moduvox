@extends('layouts.admin')
@section('title', 'Manajemen Media Sosial')
@section('page-title', 'Media Sosial')
@section('page-subtitle', 'Kelola tautan media sosial resmi perusahaan')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.socials.create') }}" class="bg-red-700 hover:bg-red-800 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-colors inline-flex items-center gap-2 shadow-sm">
        <span class="material-symbols-outlined text-[20px]">add</span>
        Tambah Media Sosial
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-500 w-16">Urutan</th>
                <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-500">Ikon</th>
                <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-500">Nama</th>
                <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-500">URL</th>
                <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-500">Status</th>
                <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-500 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($socials as $social)
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-6 py-4 text-sm text-slate-400 font-medium">{{ $social->order }}</td>
                <td class="px-6 py-4">
                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600">
                        <span class="material-symbols-outlined text-[24px]">{{ $social->icon }}</span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-bold text-slate-800">{{ $social->name }}</div>
                </td>
                <td class="px-6 py-4">
                    <a href="{{ $social->url }}" target="_blank" class="text-sm text-blue-600 hover:underline truncate max-w-xs block">{{ $social->url }}</a>
                </td>
                <td class="px-6 py-4">
                    @if($social->is_active)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-green-100 text-green-700">
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-500">
                            Nonaktif
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.socials.edit', $social) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </a>
                        <form action="{{ route('admin.socials.destroy', $social) }}" method="POST" class="delete-form">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <span class="material-symbols-outlined text-4xl text-slate-200 block mb-2">share</span>
                    <p class="text-slate-400 text-sm">Belum ada data media sosial.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

