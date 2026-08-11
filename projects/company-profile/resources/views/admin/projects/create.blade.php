@extends('layouts.admin')
@section('title','Tambah Proyek')
@section('page-title','Tambah Proyek Baru')
@section('page-subtitle','Lengkapi formulir di bawah ini untuk menambahkan data proyek.')

@section('content')
<div class="mt-2 max-w-3xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Proyek *</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('title') border-red-400 @enderror"
                           placeholder="Masukkan nama proyek" required/>
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Klien / Instansi</label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
                           placeholder="Nama Pemilik Proyek"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi Proyek</label>
                    <input type="text" name="location" value="{{ old('location') }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
                           placeholder="Contoh: Depok, Jawa Barat"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun Pelaksanaan / Durasi</label>
                    <input type="text" name="year" value="{{ old('year') }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
                           placeholder="Contoh: 2023 atau 2023 - 2024"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori *</label>
                    <input type="text" name="category" value="{{ old('category') }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('category') border-red-400 @enderror"
                           placeholder="Contoh: Infrastruktur, Gedung" required/>
                    @error('category')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Ringkas (Summary)</label>
                <textarea name="description" rows="2"
                          class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
                          placeholder="Masukkan uraian singkat proyek...">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Cakupan Pekerjaan (Technical Scope)</label>
                <textarea name="project_scope" rows="5"
                          class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
                          placeholder="Rincian teknis cakupan pekerjaan (Scope of Work)...">{{ old('project_scope') }}</textarea>
                <p class="text-xs text-slate-400 mt-2 italic">Gunakan baris baru untuk memisahkan poin pekerjaan.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Gambar Proyek</label>
                <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center hover:border-red-400 transition-colors cursor-pointer" onclick="document.getElementById('image').click()">
                    <div id="preview-placeholder">
                        <span class="material-symbols-outlined text-slate-300 text-4xl">add_photo_alternate</span>
                        <p class="text-slate-400 text-sm mt-2">Klik untuk unggah gambar</p>
                        <p class="text-slate-300 text-xs mt-1">JPG, PNG, WEBP • Max 2MB • Rekomendasi 16:9</p>
                    </div>
                    <img id="image-preview" src="" class="hidden max-h-48 mx-auto rounded-lg object-cover" alt="Preview"/>
                </div>
                <input type="file" name="image" id="image" class="hidden" accept="image/jpeg,image/png,image/webp"
                       onchange="previewImage(this)"/>
                <div class="mt-3 bg-blue-50/50 p-3 rounded-lg border border-blue-100 flex items-start gap-2">
                    <span class="material-symbols-outlined text-blue-500 text-lg mt-0.5">info</span>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        <strong class="text-slate-700">Tips Unggah:</strong> Gunakan format <span class="font-semibold">JPG, PNG, atau WEBP</span> (Maksimal 2MB).<br>
                        Untuk hasil terbaik tanpa terpotong, gunakan <strong>Rasio 16:9</strong> dengan resolusi ideal <span class="font-semibold text-blue-700">1280 x 720 pixel</span>.
                    </p>
                </div>
                @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Galeri Foto Tambahan (Banyak Foto)</label>
                <input type="file" name="gallery[]" class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
                       multiple accept="image/jpeg,image/png,image/webp"/>
                <p class="text-xs text-slate-400 mt-1 italic">Anda dapat memilih lebih dari satu foto sekaligus.</p>
                @error('gallery.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_featured" id="is_featured" class="w-4 h-4 text-red-600 rounded" {{ old('is_featured') ? 'checked': '' }}>
                <label for="is_featured" class="text-sm font-medium text-slate-700">Tampilkan sebagai Proyek Unggulan</label>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-8 py-3 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                    Simpan Data
                </button>
                <a href="{{ route('admin.projects.index') }}" class="text-slate-500 hover:text-slate-700 text-sm font-medium transition-colors">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const placeholder = document.getElementById('preview-placeholder');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection

