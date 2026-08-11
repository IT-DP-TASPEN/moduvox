@extends('layouts.admin')
@section('title','Pengaturan Halaman Karir')
@section('page-title','Pengaturan Karir')
@section('page-subtitle','Atur teks hero dan gambar branding untuk halaman karir.')

@section('content')
<div class="mt-2 max-w-3xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('admin.pages.store', 'career') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            
            <div>
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">Hero Section</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Hero (Career)</label>
                        <input type="text" name="career_hero_title" value="{{ $settings['career_hero_title'] ?? '' }}"
                               class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all"
                               placeholder="Contoh: Bergabunglah Bersama Kami"/>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Hero</label>
                        <textarea name="career_hero_desc" rows="3"
                                  class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all"
                                  placeholder="Teks ajakan untuk bergabung...">{{ $settings['career_hero_desc'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">Employer Branding Image</h3>
                <div class="space-y-4">
                    @if(isset($settings['career_hero_image']))
                    <div class="mb-4">
                        <p class="text-xs text-slate-400 mb-2">Gambar Saat Ini:</p>
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 p-2 inline-block">
                            <img src="{{ asset('storage/' . $settings['career_hero_image']) }}" class="max-h-48 w-auto object-contain rounded-lg" alt="Career Branding">
                        </div>
                    </div>
                    @endif
                    <input type="file" name="career_hero_image" 
                           class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all"/>
                    <p class="text-xs text-slate-400 mt-2 italic">Gunakan foto aktivitas tim atau workshop untuk menunjukkan budaya kerja.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-8 py-3 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.careers.index') }}" class="ml-4 text-slate-500 hover:text-slate-700 text-sm font-medium transition-colors">
                    Kembali ke Lowongan
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
