@extends('layouts.admin')
@section('title','Edit Layanan')
@section('page-title','Edit Layanan')
@section('page-subtitle','Perbarui data layanan')

@section('content')
<div class="mt-2 max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('admin.services.update', $service) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Layanan *</label>
                <input type="text" name="name" value="{{ old('name', $service->name) }}" required
                       class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent @error('name') border-red-400 @enderror"/>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @include('admin.partials.icon-picker', ['fieldName' => 'icon', 'currentIcon' => old('icon', $service->icon), 'label' => 'Ikon Layanan *'])
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Gambar Banner Layanan</label>
                @if($service->image)
                <div class="mb-4">
                    <p class="text-xs text-slate-400 mb-2">Banner saat ini:</p>
                    <div class="aspect-video w-48 rounded-xl overflow-hidden border border-slate-200">
                        <img src="{{ asset('storage/' . $service->image) }}" class="w-full h-full object-cover" alt="{{ $service->name }}">
                    </div>
                </div>
                @endif
                <input type="file" name="image" class="w-full border border-slate-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-red-500 transition-all"/>
                <p class="text-xs text-slate-400 mt-2 italic">Kosongkan jika tidak ingin mengubah banner. Format: JPG, PNG, WEBP. Maks 2MB.</p>
                @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Ringkas *</label>
                <textarea name="description" rows="2" required
                          class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent @error('description') border-red-400 @enderror">{{ old('description', $service->description) }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Konten Detail Layanan (Halaman Khusus)</label>
                <textarea name="content" rows="10"
                          class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent @error('content') border-red-400 @enderror"
                          placeholder="Tuliskan detail pekerjaan, cakupan, dan spesifikasi teknis layanan ini...">{{ old('content', $service->content) }}</textarea>
                <p class="text-xs text-slate-400 mt-2 italic">Gunakan baris baru untuk memisahkan paragraf.</p>
                @error('content')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-8 py-3 rounded-lg text-sm font-semibold transition-colors shadow-sm">Perbarui Layanan</button>
                <a href="{{ route('admin.services.index') }}" class="text-slate-500 hover:text-slate-700 text-sm font-medium">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

