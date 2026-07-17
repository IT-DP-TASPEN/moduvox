@extends('admin.layout')

@section('header')
<div class="flex items-center gap-4">
    <a href="{{ route('admin.divisions.index') }}" class="p-2 hover:bg-slate-100 rounded-full transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Tambah Divisi</h1>
        <p class="text-sm text-slate-500">Tambah data master divisi baru</p>
    </div>
</div>
@endsection

@section('content')
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden max-w-2xl">
    <div class="p-8">
        @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-2xl">
            <ul class="list-disc list-inside text-sm font-medium">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.divisions.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Kode</label>
                <input type="text" name="code" value="{{ old('code') }}" placeholder="Contoh: DIV01, 001" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-brand-blue outline-none transition-all">
            </div>
            
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Nama Divisi / Cabang</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: IT, HRD, KPO" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-brand-blue outline-none transition-all">
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('admin.divisions.index') }}" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-all">Batal</a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-brand-blue text-white font-bold shadow-lg shadow-blue-100 hover:opacity-90 transition-all">Simpan Divisi</button>
            </div>
        </form>
    </div>
</div>
@endsection
