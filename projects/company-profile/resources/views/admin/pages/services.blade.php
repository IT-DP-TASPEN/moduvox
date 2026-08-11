@extends('layouts.admin')
@section('title','Pengaturan Halaman Layanan')
@section('page-title','Teks Halaman Services')
@section('page-subtitle','Atur teks hero dan call-to-action halaman layanan')

@section('content')
<div class="mt-2 max-w-3xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('admin.pages.store', 'services') }}" method="POST" class="space-y-8">
            @csrf

            <div>
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">Hero Section</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Judul (Hero Title)</label>
                        <input type="text" name="services_hero_title" value="{{ $settings['services_hero_title'] ?? '' }}"
                               class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent"/>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi (Hero Description)</label>
                        <textarea name="services_hero_desc" rows="3"
                                  class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent">{{ $settings['services_hero_desc'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">Call To Action</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Judul CTA</label>
                        <input type="text" name="services_cta_title" value="{{ $settings['services_cta_title'] ?? '' }}"
                               class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent"/>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi CTA</label>
                        <textarea name="services_cta_desc" rows="3"
                                  class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent">{{ $settings['services_cta_desc'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-8 py-3 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


