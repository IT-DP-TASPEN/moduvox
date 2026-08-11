@extends('layouts.admin')
@section('title','Tambah Lowongan')
@section('page-title','Tambah Lowongan Baru')
@section('page-subtitle','Publikasikan kesempatan karir baru di PT Moduvox Tech ID.')

@section('content')
<div class="mt-2 max-w-4xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('admin.careers.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Posisi *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all"
                           placeholder="Contoh: Project Engineer / Site Manager"/>
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe Pekerjaan *</label>
                    <select name="type" class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all">
                        <option value="Full-time">Full-time</option>
                        <option value="Contract">Contract</option>
                        <option value="Internship">Internship</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi Penempatan</label>
                    <input type="text" name="location" value="{{ old('location') }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all"
                           placeholder="Contoh: Head Office (Depok) / On-site"/>
                </div>
                <div class="flex items-center pt-8">
                    <input type="checkbox" name="is_active" id="is_active" checked class="w-4 h-4 text-red-600 rounded">
                    <label for="is_active" class="ml-2 text-sm font-medium text-slate-700">Langsung Publikasikan (Aktif)</label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Pekerjaan *</label>
                <textarea name="description" rows="5" required
                          class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all"
                          placeholder="Jelaskan peran dan tanggung jawab posisi ini...">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Persyaratan / Kualifikasi</label>
                <textarea name="requirements" rows="5"
                          class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all"
                          placeholder="Contoh: Min. 3 tahun pengalaman di konstruksi, Pendidikan S1 Teknik Sipil...">{{ old('requirements') }}</textarea>
                <p class="text-xs text-slate-400 mt-2 italic">Gunakan baris baru untuk memisahkan poin kualifikasi.</p>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-8 py-3 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                    Simpan Lowongan
                </button>
                <a href="{{ route('admin.careers.index') }}" class="text-slate-500 hover:text-slate-700 text-sm font-medium transition-colors">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

