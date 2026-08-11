@extends('layouts.admin')
@section('title','Tambah Klien')
@section('page-title','Tambah Klien')
@section('page-subtitle','Buat data klien baru')

@section('content')
<div class="mt-2 max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('admin.clients.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Klien / Perusahaan *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent @error('name') border-red-400 @enderror"
                       placeholder="Contoh: PT Pertamina (Persero)"/>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Logo Klien</label>
                <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center hover:border-red-400 transition-colors cursor-pointer" onclick="document.getElementById('logo').click()">
                    <div id="preview-placeholder">
                        <span class="material-symbols-outlined text-slate-300 text-4xl">add_photo_alternate</span>
                        <p class="text-slate-400 text-sm mt-2">Klik untuk upload logo</p>
                        <p class="text-slate-300 text-xs mt-1">PNG, WEBP (Transparan Direkomendasikan) • Max 1MB</p>
                    </div>
                    <div class="h-24 flex items-center justify-center p-2 bg-slate-50 rounded-lg hidden" id="preview-container">
                        <img id="image-preview" src="" class="max-h-full max-w-full object-contain" alt="Preview"/>
                    </div>
                </div>
                <input type="file" name="logo" id="logo" class="hidden" accept="image/png,image/jpeg,image/webp" onchange="previewImage(this)"/>
                <div class="mt-3 bg-blue-50/50 p-3 rounded-lg border border-blue-100 flex items-start gap-2">
                    <span class="material-symbols-outlined text-blue-500 text-lg mt-0.5">info</span>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        <strong class="text-slate-700">Tips Logo:</strong> Sangat disarankan menggunakan format <span class="font-semibold text-blue-700">PNG Transparan</span> agar menyatu dengan latar belakang web.<br>
                        Max ukuran file <strong>1MB</strong>. Resolusi ideal <span class="font-semibold">500 x 500 pixel</span> atau bentuk persegi.
                    </p>
                </div>
                @error('logo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-8 py-3 rounded-lg text-sm font-semibold transition-colors">Simpan Klien</button>
                <a href="{{ route('admin.clients.index') }}" class="text-slate-500 hover:text-slate-700 text-sm font-medium">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
@section('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const container = document.getElementById('preview-container');
    const placeholder = document.getElementById('preview-placeholder');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; container.classList.remove('hidden'); placeholder.classList.add('hidden'); };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection

