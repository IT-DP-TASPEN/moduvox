@extends('admin.layout')

@section('header', 'Edit Banner')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.banners.index') }}" class="text-slate-500 hover:text-brand-blue flex items-center gap-2 text-sm font-medium transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8">
        <h4 class="text-lg font-bold text-slate-800 mb-6">Edit Banner</h4>
        
        <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Judul (Opsional)</label>
                    <input type="text" name="title" value="{{ $banner->title }}" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand-blue">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Ganti Gambar (Kosongkan jika tidak ingin ganti)</label>
                    <div class="mb-4">
                        <img src="{{ asset('storage/' . $banner->image_path) }}" class="w-48 rounded-xl shadow-sm border border-slate-100">
                    </div>
                    <input type="file" name="image" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand-blue">
                    <p class="text-[10px] text-slate-500 mt-2">Format: JPG, PNG. Maksimal 2MB.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Urutan Tampil</label>
                        <input type="number" name="sort_order" value="{{ $banner->sort_order }}" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand-blue">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Status</label>
                        <div class="flex items-center gap-2 py-3">
                            <input type="checkbox" name="is_active" value="1" {{ $banner->is_active ? 'checked' : '' }} class="w-4 h-4 text-brand-blue rounded focus:ring-brand-blue">
                            <span class="text-sm text-slate-600 font-medium">Aktif</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Link URL (Opsional)</label>
                    <input type="url" name="link_url" value="{{ $banner->link_url }}" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand-blue" placeholder="https://...">
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-brand-blue text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-100">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
