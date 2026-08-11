@extends('layouts.admin')
@section('title','Tambah Layanan')
@section('page-title','Tambah Layanan')
@section('page-subtitle','Buat layanan baru yang ditawarkan perusahaan')

@section('content')
<div class="mt-2 max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Layanan *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent @error('name') border-red-400 @enderror"
                       placeholder="Contoh: General Contractor"/>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @include('admin.partials.icon-picker', ['fieldName' => 'icon', 'currentIcon' => old('icon', 'build'), 'label' => 'Ikon Layanan *'])
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Gambar Banner Layanan (Opsional)</label>
                <input type="file" name="image" class="w-full border border-slate-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-red-500 transition-all"/>
                <p class="text-xs text-slate-400 mt-2 italic">Format: JPG, PNG, WEBP. Maks 2MB. Digunakan sebagai background halaman detail.</p>
                @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Ringkas *</label>
                <textarea name="description" rows="2" required
                          class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent @error('description') border-red-400 @enderror"
                          placeholder="Deskripsi singkat yang tampil di daftar layanan...">{{ old('description') }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Konten Detail Layanan (Halaman Khusus)</label>
                <textarea name="content" rows="10"
                          class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent @error('content') border-red-400 @enderror"
                          placeholder="Tuliskan detail pekerjaan, cakupan, dan spesifikasi teknis layanan ini...">{{ old('content') }}</textarea>
                <p class="text-xs text-slate-400 mt-2 italic">Gunakan baris baru untuk memisahkan paragraf. Bagian ini akan muncul di halaman detail layanan.</p>
                @error('content')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-8 py-3 rounded-lg text-sm font-semibold transition-colors shadow-sm">Simpan Layanan</button>
                <a href="{{ route('admin.services.index') }}" class="text-slate-500 hover:text-slate-700 text-sm font-medium">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

