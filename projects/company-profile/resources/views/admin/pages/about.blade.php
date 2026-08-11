@extends('layouts.admin')
@section('title','Pengaturan Halaman Profil')
@section('page-title','Pengaturan Halaman Profil')
@section('page-subtitle','Kelola konten teks dan aset visual untuk halaman Tentang Kami.')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 pb-20">
    <form action="{{ route('admin.pages.store', 'about') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        {{-- CARD 1: HERO SECTION --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-600">branding_watermark</span>
                    Hero Section
                </h3>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Judul (Hero Title)</label>
                            <input type="text" name="about_hero_title" value="{{ $settings['about_hero_title'] ?? '' }}"
                                   class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none"/>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi (Hero Description)</label>
                            <textarea name="about_hero_desc" rows="4" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none">{{ $settings['about_hero_desc'] ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <label class="block text-sm font-semibold text-slate-700">Hero Background Image</label>
                        @php $heroImg = $settings['about_hero_image'] ?? null; @endphp
                        @if($heroImg)
                            <div class="relative group rounded-xl overflow-hidden aspect-video border bg-slate-50">
                                <img src="{{ asset('storage/' . $heroImg) }}" class="w-full h-full object-cover" alt="Hero">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="button" onclick="confirmDelete('about', 'about_hero_image')" class="bg-white text-red-600 p-2 rounded-full hover:bg-red-50 transition-colors">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="about_hero_image" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer"/>
                        <p class="text-[10px] text-slate-400 italic">Disarankan gambar landscape resolusi tinggi (min 1920x1080).</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 2: PROFIL & CERITA --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-600">history_edu</span>
                    Profil & Perjalanan
                </h3>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Cerita</label>
                            <input type="text" name="about_story_title" value="{{ $settings['about_story_title'] ?? '' }}"
                                   class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none"/>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Profil</label>
                            <textarea name="about_story_desc" rows="8" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none">{{ $settings['about_story_desc'] ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Milestone / Perjalanan (Optional)</label>
                            <textarea name="about_journey" rows="6" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none" placeholder="Tuliskan sejarah singkat atau pencapaian penting...">{{ $settings['about_journey'] ?? '' }}</textarea>
                        </div>
                        <div class="p-6 bg-slate-900 rounded-2xl text-white">
                            <h4 class="text-xs font-bold uppercase tracking-widest text-red-500 mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">lightbulb</span> Visi & Misi
                            </h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-white/40 uppercase mb-1">Visi</label>
                                    <textarea name="about_vision" rows="2" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-xs focus:border-red-500 outline-none">{{ $settings['about_vision'] ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-white/40 uppercase mb-1">Misi (1 Per Baris)</label>
                                    <textarea name="about_mission" rows="4" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-xs focus:border-red-500 outline-none">{{ $settings['about_mission'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 3: FASILITAS & AKTIVITAS --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-600">apartment</span>
                    Fasilitas & Dokumentasi Aktivitas
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {{-- Office --}}
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Foto Head Office</label>
                        @php $offImg = $settings['about_facility_office'] ?? null; @endphp
                        @if($offImg)
                            <div class="relative group rounded-lg overflow-hidden aspect-video border bg-slate-50 mb-2">
                                <img src="{{ asset('storage/' . $offImg) }}" class="w-full h-full object-cover" alt="Office">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="button" onclick="confirmDelete('about', 'about_facility_office')" class="bg-white text-red-600 p-1.5 rounded-full shadow-lg">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="about_facility_office" class="text-xs w-full"/>
                    </div>
                    {{-- Workshop --}}
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Foto Workshop</label>
                        @php $workImg = $settings['about_facility_workshop'] ?? null; @endphp
                        @if($workImg)
                            <div class="relative group rounded-lg overflow-hidden aspect-video border bg-slate-50 mb-2">
                                <img src="{{ asset('storage/' . $workImg) }}" class="w-full h-full object-cover" alt="Workshop">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="button" onclick="confirmDelete('about', 'about_facility_workshop')" class="bg-white text-red-600 p-1.5 rounded-full shadow-lg">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="about_facility_workshop" class="text-xs w-full"/>
                    </div>
                    {{-- Activity --}}
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Foto Team Activity</label>
                        @php $actImg = $settings['about_facility_activity'] ?? null; @endphp
                        @if($actImg)
                            <div class="relative group rounded-lg overflow-hidden aspect-video border bg-slate-50 mb-2">
                                <img src="{{ asset('storage/' . $actImg) }}" class="w-full h-full object-cover" alt="Activity">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="button" onclick="confirmDelete('about', 'about_facility_activity')" class="bg-white text-red-600 p-1.5 rounded-full shadow-lg">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="about_facility_activity" class="text-xs w-full"/>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 4: STRUKTUR ORGANISASI --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-600">account_tree</span>
                    Struktur Organisasi
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                    <div class="space-y-4">
                        <label class="block text-sm font-semibold text-slate-700">Bagan Struktur Organisasi</label>
                        @php $orgImg = $settings['about_org_structure'] ?? null; @endphp
                        @if($orgImg)
                            <div class="relative group rounded-xl overflow-hidden border bg-white p-4">
                                <img src="{{ asset('storage/' . $orgImg) }}" class="max-h-80 w-auto mx-auto object-contain" alt="Struktur">
                                <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" onclick="confirmDelete('about', 'about_org_structure')" class="bg-red-600 text-white p-2 rounded-full shadow-xl">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="about_org_structure" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer"/>
                    </div>
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-6">
                        <div class="flex gap-4">
                            <span class="material-symbols-outlined text-blue-600">info</span>
                            <div>
                                <h5 class="font-bold text-blue-900 text-sm mb-2">Panduan Gambar:</h5>
                                <ul class="text-xs text-blue-700 space-y-2 list-disc pl-4">
                                    <li>Gunakan format <strong>PNG transparan</strong> jika memungkinkan agar terlihat menyatu dengan background.</li>
                                    <li>Pastikan resolusi cukup tinggi agar teks dalam bagan dapat terbaca dengan jelas.</li>
                                    <li>Maksimal ukuran file adalah 4MB.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-center pt-8">
            <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-12 py-4 rounded-xl font-bold transition-all shadow-xl shadow-red-700/20 flex items-center gap-3">
                <span class="material-symbols-outlined">save</span>
                Simpan Semua Pengaturan Profil
            </button>
        </div>
    </form>
</div>

{{-- Hidden delete form --}}
<form id="delete-setting-form" method="POST" class="hidden">
    @csrf
    @method('DELETE')
    <input type="hidden" name="image_path" id="delete-image-path">
</form>

@endsection

@section('scripts')
<script>
function confirmDelete(page, key, imagePath = null) {
    if(confirm('Apakah Anda yakin ingin menghapus aset visual ini?')) {
        const form = document.getElementById('delete-setting-form');
        form.action = '{{ url("admin/pages") }}/' + page + '/' + key;
        if(imagePath) {
            document.getElementById('delete-image-path').value = imagePath;
        }
        form.submit();
    }
}
</script>
@endsection

