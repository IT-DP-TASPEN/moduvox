@extends('layouts.admin')
@section('title','Klien')
@section('page-title','Manajemen Klien')
@section('page-subtitle','Kelola daftar klien dan mitra perusahaan')

@section('content')
<div class="mt-2 flex items-center justify-between mb-6">
    <p class="text-slate-500 text-sm">Total <strong class="text-slate-700">{{ $clients->total() }}</strong> klien</p>
    <a href="{{ route('admin.clients.create') }}" class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm">
        <span class="material-symbols-outlined text-lg">add</span> Tambah Klien
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
        @forelse($clients as $client)
        <div class="group relative border border-slate-100 rounded-xl p-4 flex flex-col items-center justify-center hover:border-red-200 hover:shadow-md transition-all bg-slate-50/50 h-32 overflow-hidden min-w-0">
            @if($client->logo)
                <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->name }}" class="max-h-16 max-w-full object-contain filter grayscale group-hover:grayscale-0 opacity-70 group-hover:opacity-100 transition-all">
            @else
                <div class="text-center w-full min-w-0">
                    <span class="material-symbols-outlined text-slate-300 text-3xl mb-1">domain</span>
                    <p class="font-bold text-sm text-slate-800 truncate w-full px-2" title="{{ $client->name }}">{{ $client->name }}</p>
                </div>
            @endif

            {{-- Hover Actions --}}
            <div class="absolute inset-x-0 bottom-0 top-0 bg-white/90 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 rounded-xl">
                <a href="{{ route('admin.clients.edit', $client) }}" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                </a>
                <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" class="delete-form">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-8 h-8 rounded-full bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </form>
            </div>
            {{-- Name tooltip fallback for logo --}}
            @if($client->logo)
            <div class="absolute -bottom-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">{{ $client->name }}</div>
            @endif
        </div>
        @empty
        <div class="col-span-6 text-center py-12 text-slate-400">Belum terdapat data klien.</div>
        @endforelse
    </div>

    @if($clients->hasPages())
    <div class="mt-8 border-t border-slate-100 pt-6">{{ $clients->links() }}</div>
    @endif
</div>
@endsection

