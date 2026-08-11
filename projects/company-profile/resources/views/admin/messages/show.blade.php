@extends('layouts.admin')
@section('title','Detail Pesan')
@section('page-title','Detail Pesan')
@section('page-subtitle','Baca pesan dari pengunjung')

@section('content')
<div class="mt-2 max-w-3xl">
    <div class="mb-4">
        <a href="{{ route('admin.messages.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-red-700 font-medium transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke Kotak Masuk
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        {{-- Header Info --}}
        <div class="p-6 md:p-8 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-50/50">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-red-100 text-red-600 font-bold text-xl flex items-center justify-center">
                    {{ substr($message->name, 0, 1) }}
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-lg leading-tight">{{ $message->name }}</h3>
                    <a href="mailto:{{ $message->email }}" class="text-sm text-slate-500 hover:text-red-600 inline-flex items-center gap-1.5 mt-1">
                        <span class="material-symbols-outlined text-[14px]">mail</span> {{ $message->email }}
                    </a>
                </div>
            </div>
            <div class="text-right text-xs text-slate-400">
                <p class="font-medium text-slate-600">{{ $message->created_at->isoFormat('D MMMM YYYY') }}</p>
                <p class="mt-0.5">{{ $message->created_at->isoFormat('HH:mm') }} WIB ({{ $message->created_at->diffForHumans() }})</p>
            </div>
        </div>

        {{-- Message Body --}}
        <div class="p-6 md:p-8">
            <h4 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">Subjek:</h4>
            <p class="text-xl font-bold text-slate-800 mb-6">{{ $message->subject }}</p>

            <h4 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">Pesan:</h4>
            <div class="text-slate-700 bg-slate-50 rounded-xl p-6 text-sm md:text-base leading-relaxed whitespace-pre-wrap border border-slate-100">{{ $message->message }}</div>
        </div>

        {{-- Footer Actions --}}
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
            <a href="mailto:{{ $message->email }}?subject=Re: {{ urlencode($message->subject) }}"
               class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                <span class="material-symbols-outlined text-lg">reply</span> Balas via Email
            </a>
            <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" class="delete-form">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 text-red-600 hover:bg-red-50 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                    <span class="material-symbols-outlined text-lg">delete</span> Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

