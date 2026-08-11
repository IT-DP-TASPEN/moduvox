@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Selamat datang di Admin Panel PT Moduvox Tech ID')

@section('content')
{{-- Stats Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-2">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5">
        <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center">
            <span class="material-symbols-outlined text-blue-600 text-2xl">apartment</span>
        </div>
        <div>
            <p class="text-slate-400 text-sm font-medium">Total Proyek</p>
            <p class="text-3xl font-bold text-slate-800">{{ $totalProjects }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5">
        <div class="w-14 h-14 rounded-xl bg-green-100 flex items-center justify-center">
            <span class="material-symbols-outlined text-green-600 text-2xl">build</span>
        </div>
        <div>
            <p class="text-slate-400 text-sm font-medium">Total Layanan</p>
            <p class="text-3xl font-bold text-slate-800">{{ $totalServices }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5">
        <div class="w-14 h-14 rounded-xl bg-purple-100 flex items-center justify-center">
            <span class="material-symbols-outlined text-purple-600 text-2xl">handshake</span>
        </div>
        <div>
            <p class="text-slate-400 text-sm font-medium">Total Klien</p>
            <p class="text-3xl font-bold text-slate-800">{{ $totalClients }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5">
        <div class="w-14 h-14 rounded-xl bg-red-100 flex items-center justify-center">
            <span class="material-symbols-outlined text-red-600 text-2xl">mail</span>
        </div>
        <div>
            <p class="text-slate-400 text-sm font-medium">Pesan Masuk</p>
            <p class="text-3xl font-bold text-slate-800">{{ $totalMessages }}</p>
            @if($unreadMessages > 0)
            <p class="text-red-500 text-xs font-medium">{{ $unreadMessages }} Pesan belum dibaca</p>
            @endif
        </div>
    </div>
</div>

{{-- Weekly Trend --}}
<div class="mt-8 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <div class="flex items-center justify-between mb-8">
        <h2 class="font-bold text-slate-800">Tren Pesan Masuk (7 Hari Terakhir)</h2>
        <span class="text-xs text-slate-400 font-medium uppercase tracking-widest">Update Real-time</span>
    </div>
    <div class="flex items-end justify-between gap-2 h-40">
        @foreach($messageTrend as $trend)
        <div class="flex-1 flex flex-col items-center gap-3 h-full justify-end group">
            <div class="w-full bg-slate-50 rounded-t-lg relative h-full flex flex-col justify-end overflow-hidden">
                @php
                    $max = collect($messageTrend)->max('count') ?: 1;
                    $height = ($trend['count'] / $max) * 100;
                @endphp
                <div class="bg-red-600/20 group-hover:bg-red-600/40 transition-all rounded-t-lg w-full flex items-center justify-center text-[10px] font-bold text-red-700" 
                     style="height: {{ $height }}%">
                    @if($trend['count'] > 0) {{ $trend['count'] }} @endif
                </div>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ $trend['day'] }}</span>
        </div>
        @endforeach
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
    {{-- Recent Projects --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800">Proyek Terbaru</h2>
            <a href="{{ route('admin.projects.index') }}" class="text-red-600 text-sm font-medium hover:text-red-800">Lihat Semua Data</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentProjects as $p)
            <div class="px-6 py-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-blue-500 text-lg">apartment</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $p->title }}</p>
                    <p class="text-xs text-slate-400">{{ $p->category }} · {{ $p->created_at->diffForHumans() }}</p>
                </div>
                <a href="{{ route('admin.projects.edit', $p) }}" class="text-slate-400 hover:text-red-600 transition-colors">
                    <span class="material-symbols-outlined text-lg">edit</span>
                </a>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-slate-400 text-sm">Belum terdapat data proyek.</div>
            @endforelse
        </div>
    </div>

    {{-- Recent Messages --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800">Pesan Terbaru</h2>
            <a href="{{ route('admin.messages.index') }}" class="text-red-600 text-sm font-medium hover:text-red-800">Lihat Semua Pesan</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentMessages as $m)
            <div class="px-6 py-4 flex items-center gap-4 {{ !$m->is_read ? 'bg-red-50/50' : '' }}">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-red-600 font-bold text-sm">{{ substr($m->name, 0, 1) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $m->name }}
                        @if(!$m->is_read)<span class="ml-2 bg-red-600 text-white text-[10px] px-1.5 py-0.5 rounded-full">Pesan Baru</span>@endif
                    </p>
                    <p class="text-xs text-slate-400 truncate">{{ $m->subject }}</p>
                </div>
                <a href="{{ route('admin.messages.show', $m) }}" class="text-slate-400 hover:text-red-600 transition-colors">
                    <span class="material-symbols-outlined text-lg">visibility</span>
                </a>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-slate-400 text-sm">Belum terdapat pesan masuk.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

