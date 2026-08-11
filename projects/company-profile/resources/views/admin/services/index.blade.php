@extends('layouts.admin')
@section('title','Layanan')
@section('page-title','Manajemen Layanan')
@section('page-subtitle','Kelola daftar layanan perusahaan')

@section('content')
<div class="mt-2 flex items-center justify-between mb-6">
    <p class="text-slate-500 text-sm">Total <strong class="text-slate-700">{{ $services->total() }}</strong> layanan</p>
    <a href="{{ route('admin.services.create') }}" class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm">
        <span class="material-symbols-outlined text-lg">add</span> Tambah Layanan
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-6 py-4 text-left font-semibold text-slate-600 w-16">Icon</th>
                <th class="px-6 py-4 text-left font-semibold text-slate-600">Nama Layanan</th>
                <th class="px-6 py-4 text-left font-semibold text-slate-600">Deskripsi</th>
                <th class="px-6 py-4 text-right font-semibold text-slate-600 w-32">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($services as $service)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4">
                    <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-600">{{ $service->icon }}</span>
                    </div>
                </td>
                <td class="px-6 py-4 font-semibold text-slate-800">{{ $service->name }}</td>
                <td class="px-6 py-4 text-slate-500 text-xs max-w-md truncate">{{ $service->description }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.services.edit', $service) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                            <span class="material-symbols-outlined text-lg">edit</span>
                        </a>
                        <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="delete-form">
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
                <td colspan="4" class="px-6 py-16 text-center text-slate-400">
                    <span class="material-symbols-outlined text-4xl block mb-2 opacity-30">build</span>
                    Belum terdapat data layanan. <a href="{{ route('admin.services.create') }}" class="text-red-600 font-medium">Tambah Data</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($services->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">{{ $services->links() }}</div>
    @endif
</div>
@endsection

