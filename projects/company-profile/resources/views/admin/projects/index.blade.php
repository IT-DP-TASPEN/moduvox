@extends('layouts.admin')
@section('title','Proyek')
@section('page-title','Manajemen Proyek')
@section('page-subtitle','Kelola semua data proyek perusahaan')

@section('content')
<div class="mt-2 flex items-center justify-between mb-6">
    <p class="text-slate-500 text-sm">Total <strong class="text-slate-700">{{ $projects->total() }}</strong> proyek</p>
    <a href="{{ route('admin.projects.create') }}"
       class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm">
        <span class="material-symbols-outlined text-lg">add</span> Tambah Proyek
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-6 py-4 text-left font-semibold text-slate-600">Gambar</th>
                <th class="px-6 py-4 text-left font-semibold text-slate-600">Judul</th>
                <th class="px-6 py-4 text-left font-semibold text-slate-600">Kategori</th>
                <th class="px-6 py-4 text-left font-semibold text-slate-600">Featured</th>
                <th class="px-6 py-4 text-left font-semibold text-slate-600">Tanggal</th>
                <th class="px-6 py-4 text-right font-semibold text-slate-600">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($projects as $project)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4">
                    <div class="w-20 h-12 rounded-lg overflow-hidden bg-slate-100">
                        @if($project->image)
                            <img src="{{ asset('storage/' . $project->image) }}" class="w-full h-full object-cover" alt="{{ $project->title }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="material-symbols-outlined text-slate-300">image</span>
                            </div>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4">
                    <p class="font-semibold text-slate-800">{{ $project->title }}</p>
                    <p class="text-slate-400 text-xs line-clamp-1 mt-0.5">{{ $project->description }}</p>
                </td>
                <td class="px-6 py-4">
                    <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">{{ $project->category }}</span>
                </td>
                <td class="px-6 py-4">
                    @if($project->is_featured)
                        <span class="bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full">★ Unggulan</span>
                    @else
                        <span class="text-slate-300 text-xs">—</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-slate-400 text-xs">{{ $project->created_at->isoFormat('D MMM YYYY') }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.projects.edit', $project) }}"
                           class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                            <span class="material-symbols-outlined text-lg">edit</span>
                        </a>
                        <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="delete-form">

                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                    <span class="material-symbols-outlined text-4xl block mb-2 opacity-30">apartment</span>
                    Belum terdapat data proyek. <a href="{{ route('admin.projects.create') }}" class="text-red-600 font-medium">Tambah Data</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($projects->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">{{ $projects->links() }}</div>
    @endif
</div>
@endsection

