@extends('layouts.admin')
@section('title','Pengaturan Halaman Portofolio')
@section('page-title','Teks Halaman Proyek (Portfolio)')
@section('page-subtitle','Atur teks pembuka dan ajakan pada daftar proyek')

@section('content')
<div class="mt-2 max-w-3xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('admin.pages.store', 'project') }}" method="POST" class="space-y-8">
            @csrf
            
            {{-- Section: Hero Banner --}}
            <div>
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">Area Banner Proyek (Paling Atas)</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Utama (Page Title)</label>
                        <input type="text" name="project_hero_title" value="{{ $settings['project_hero_title'] ?? 'Proyek Kami' }}"
                               class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent"/>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Pembuka (Subtitle)</label>
                        <input type="text" name="project_hero_desc" value="{{ $settings['project_hero_desc'] ?? 'Membangun masa depan Indonesia melalui infrastruktur yang berkelanjutan dan inovasi konstruksi kelas dunia.' }}"
                               class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent"/>
                    </div>
                </div>
            </div>

            {{-- Section: Call To Action --}}
            <div>
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">Area Ajakan (Call to Action / Bawah Layar)</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Ajakan</label>
                        <input type="text" name="project_cta_title" value="{{ $settings['project_cta_title'] ?? 'Punya visi untuk proyek Anda berikutnya?' }}"
                               class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent"/>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Ajakan</label>
                        <textarea name="project_cta_desc" rows="3"
                                  class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent">{{ $settings['project_cta_desc'] ?? 'Tim ahli kami siap membantu Anda mewujudkan infrastruktur yang kokoh dan inovatif. Mari kita bangun masa depan bersama.' }}</textarea>
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

