@extends('layouts.admin')
@section('title','Ubah Lowongan')
@section('page-title','Ubah Lowongan Kerja')
@section('page-subtitle','Perbarui detail informasi lowongan kerja.')

@section('content')
<div class="mt-2 max-w-4xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('admin.careers.update', $career) }}" method="POST" class="space-y-6">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Posisi *</label>
                    <input type="text" name="title" value="{{ old('title', $career->title) }}" required
                           class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe Pekerjaan *</label>
                    <select name="type" class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all">
                        <option value="Full-time" {{ $career->type == 'Full-time' ? 'selected' : '' }}>Full-time</option>
                        <option value="Contract" {{ $career->type == 'Contract' ? 'selected' : '' }}>Contract</option>
                        <option value="Internship" {{ $career->type == 'Internship' ? 'selected' : '' }}>Internship</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi Penempatan</label>
                    <input type="text" name="location" value="{{ old('location', $career->location) }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all"/>
                </div>
                <div class="flex items-center pt-8">
                    <input type="checkbox" name="is_active" id="is_active" {{ $career->is_active ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded">
                    <label for="is_active" class="ml-2 text-sm font-medium text-slate-700">Publikasikan (Aktif)</label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Pekerjaan *</label>
                <textarea name="description" rows="5" required
                          class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all">{{ old('description', $career->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Persyaratan / Kualifikasi</label>
                <textarea name="requirements" rows="5"
                          class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all">{{ old('requirements', $career->requirements) }}</textarea>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-8 py-3 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.careers.index') }}" class="text-slate-500 hover:text-slate-700 text-sm font-medium transition-colors">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

