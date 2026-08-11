@extends('layouts.admin')
@section('title','Pengaturan Halaman Kontak')
@section('page-title','Pengaturan Halaman Kontak')
@section('page-subtitle','Kelola informasi kontak dan aset visual halaman depan.')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 pb-20">

    {{-- Form Utama --}}
    <form action="{{ route('admin.pages.store', 'contact') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        {{-- CARD 1: HERO & VISUAL --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-600">image</span>
                    Hero & Visual Branding
                </h3>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Hero Video --}}
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Hero Background Video (MP4)</label>
                        @php $heroVid = $settings['contact_hero_video'] ?? null; @endphp
                        @if($heroVid)
                            <div class="relative group rounded-lg overflow-hidden aspect-video bg-slate-900 mb-2">
                                <video src="{{ asset('storage/' . $heroVid) }}" class="w-full h-full object-cover" muted autoplay loop></video>
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="button" onclick="confirmDelete('contact', 'contact_hero_video')" class="bg-white text-red-600 p-2 rounded-full hover:bg-red-50">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="contact_hero_video" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer"/>
                        <p class="text-[10px] text-slate-400">Format: MP4/WebM. Maks 20MB. Disarankan tanpa suara.</p>
                    </div>

                    {{-- Hero Image --}}
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Hero Background Image (Fallback)</label>
                        @php $heroImg = $settings['contact_hero_image'] ?? null; @endphp
                        @if($heroImg)
                            <div class="relative group rounded-lg overflow-hidden aspect-video bg-slate-100 mb-2 border">
                                <img src="{{ asset('storage/' . $heroImg) }}" class="w-full h-full object-cover" alt="Hero Background">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="button" onclick="confirmDelete('contact', 'contact_hero_image')" class="bg-white text-red-600 p-2 rounded-full hover:bg-red-50">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="contact_hero_image" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer"/>
                        <p class="text-[10px] text-slate-400">Gunakan foto Drone kantor, Workshop, atau Fabrikasi.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 2: KONTAK UTAMA --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-600">contact_mail</span>
                    Informasi Kontak Utama
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700">Email Perusahaan</label>
                        <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}"
                               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none"
                               placeholder="Contoh: info@moduvox.id"/>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700">WhatsApp Business</label>
                        <input type="text" name="contact_whatsapp" value="{{ $settings['contact_whatsapp'] ?? '' }}"
                               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none"
                               placeholder="Link WA (https://wa.me/...)"/>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700">Telepon Kantor</label>
                        <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}"
                               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none"
                               placeholder="+62 21 ..."/>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700">Jam Operasional</label>
                        <input type="text" name="contact_hours" value="{{ $settings['contact_hours'] ?? '' }}"
                               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none"
                               placeholder="Contoh: Senin - Jumat, 08:00 - 17:00"/>
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-sm font-semibold text-slate-700">Alamat Lengkap</label>
                        <textarea name="contact_address" rows="3" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none">{{ $settings['contact_address'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 3: MAPS & OFFICE PHOTO --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-600">map</span>
                    Peta & Foto Gedung Utama
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-700">Google Maps Embed Code</label>
                            <textarea name="contact_map" rows="5" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm font-mono focus:ring-2 focus:ring-red-500 outline-none" placeholder="Paste <iframe ...> disini">{{ $settings['contact_map'] ?? '' }}</textarea>
                            <p class="text-[10px] text-slate-400">Pilih 'Sematkan Peta' di Google Maps lalu salin kodenya.</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <label class="block text-sm font-semibold text-slate-700">Foto Utama Gedung (Side Showcase)</label>
                        @php $officeImg = $settings['contact_image'] ?? null; @endphp
                        @if($officeImg)
                            <div class="relative group rounded-lg overflow-hidden aspect-video border bg-slate-50 mb-3 flex items-center justify-center p-2">
                                <img src="{{ asset('storage/' . $officeImg) }}" class="max-h-full max-w-full object-contain" alt="Office">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="button" onclick="confirmDelete('contact', 'contact_image')" class="bg-white text-red-600 p-2 rounded-full hover:bg-red-50">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="contact_image" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer"/>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="flex justify-center pt-4">
            <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-12 py-4 rounded-xl font-bold transition-all shadow-xl shadow-red-700/20 flex items-center gap-3">
                <span class="material-symbols-outlined">save</span>
                Simpan Semua Pengaturan
            </button>
        </div>
    </form>

    <hr class="border-slate-200">

    {{-- CARD 4: FACILITY GALLERY --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-red-600">photo_library</span>
                Galeri Fasilitas (Office & Workshop)
            </h3>
        </div>
        <div class="p-6 space-y-8">
            <form action="{{ route('admin.pages.store', 'contact') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-end gap-4 bg-slate-50 p-6 rounded-xl border border-slate-100">
                @csrf
                <div class="flex-1 min-w-[250px] space-y-2">
                    <label class="block text-sm font-semibold text-slate-700">Upload Foto Fasilitas Baru</label>
                    <input type="file" name="contact_facility_images[]" multiple class="w-full border border-slate-200 bg-white rounded-lg px-3 py-2 text-sm"/>
                </div>
                <button type="submit" class="bg-slate-800 text-white px-6 py-2.5 rounded-lg font-bold text-sm hover:bg-slate-900 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">upload</span>
                    Upload Ke Galeri
                </button>
            </form>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @php 
                    $facImages = json_decode($settings['contact_facility_images'] ?? '[]', true);
                @endphp
                @forelse($facImages as $index => $img)
                    <div class="group relative aspect-square rounded-lg overflow-hidden border border-slate-100 bg-slate-50">
                        <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover" alt="Facility">
                        <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                             <button type="button" onclick="confirmDelete('contact', 'contact_facility_images', '{{ $img }}')" class="bg-white text-red-600 p-1.5 rounded-full hover:bg-red-50">
                                <span class="material-symbols-outlined text-sm">delete</span>
                             </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-slate-300">
                        <span class="material-symbols-outlined text-4xl mb-2">image_not_supported</span>
                        <p class="text-xs font-bold uppercase tracking-widest">Belum ada foto galeri</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

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
        } else {
            document.getElementById('delete-image-path').value = '';
        }
        form.submit();
    }
}
</script>
@endsection

