@extends('layouts.admin')
@section('title', 'Tambah Media Sosial')
@section('page-title', 'Tambah Media Sosial')
@section('page-subtitle', 'Tambahkan akun media sosial resmi perusahaan')

@section('content')
<div class="mt-2 max-w-2xl">
    <div class="mb-4">
        <a href="{{ route('admin.socials.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-red-700 font-medium transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('admin.socials.store') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Media Sosial *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent @error('name') border-red-400 @enderror"
                       placeholder="Contoh: LinkedIn, Instagram, Facebook"/>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            @include('admin.partials.icon-picker', ['fieldName' => 'icon', 'currentIcon' => old('icon', 'share'), 'label' => 'Ikon Media Sosial *'])

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">URL Profil Media Sosial *</label>
                <input type="url" name="url" value="{{ old('url') }}" required
                       class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent @error('url') border-red-400 @enderror"
                       placeholder="https://linkedin.com/company/pt-dwi-artha-prima"/>
                @error('url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Urutan Tampil</label>
                    <input type="number" name="order" value="{{ old('order', 0) }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="0"/>
                </div>
                <div class="flex items-end pb-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5 text-red-600 border-slate-200 rounded focus:ring-red-500">
                        <span class="text-sm font-semibold text-slate-700">Aktifkan Tautan</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-8 py-3 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                    Simpan Media Sosial
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

