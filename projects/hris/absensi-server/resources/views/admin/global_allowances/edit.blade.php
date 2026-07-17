@extends('admin.layout')

@section('header', 'Edit Komponen Global')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.global-allowances.index') }}" class="text-slate-500 hover:text-brand-blue flex items-center gap-2 text-sm font-medium transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8" x-data="{ type: '{{ $allowance->type }}' }">
        <h4 class="text-lg font-bold text-slate-800 mb-6">Edit Komponen Global</h4>
        
        <form action="{{ route('admin.global-allowances.update', $allowance->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Komponen</label>
                    <input type="text" name="name" value="{{ $allowance->name }}" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand-blue" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kategori</label>
                        <select name="category" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand-blue" required>
                            <option value="earning" {{ $allowance->category == 'earning' ? 'selected' : '' }}>Tunjangan (+)</option>
                            <option value="deduction" {{ $allowance->category == 'deduction' ? 'selected' : '' }}>Potongan (-)</option>
                            <option value="company_paid" {{ $allowance->category == 'company_paid' ? 'selected' : '' }}>Dibayar Perusahaan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Target Status</label>
                        <select name="target_status" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand-blue" required>
                            <option value="All" {{ $allowance->target_status == 'All' ? 'selected' : '' }}>Semua Karyawan</option>
                            <option value="Tetap" {{ $allowance->target_status == 'Tetap' ? 'selected' : '' }}>Hanya Tetap</option>
                            <option value="Kontrak" {{ $allowance->target_status == 'Kontrak' ? 'selected' : '' }}>Hanya Kontrak</option>
                            <option value="OJT" {{ $allowance->target_status == 'OJT' ? 'selected' : '' }}>Hanya OJT</option>
                            <option value="PE" {{ $allowance->target_status == 'PE' ? 'selected' : '' }}>Hanya PE</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Nilai</label>
                        <select name="type" x-model="type" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand-blue" required>
                            <option value="fixed">Nominal (Rp)</option>
                            <option value="percentage_gapok">Persentase (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nilai</label>
                        <div class="relative" x-show="type === 'fixed'">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">Rp</span>
                            <input type="number" name="amount" value="{{ $allowance->type == 'fixed' ? $allowance->amount : '' }}" class="w-full pl-12 pr-4 py-3 rounded-xl bg-slate-50 border-none focus:ring-2 focus:ring-brand-blue transition-all font-bold" x-bind:disabled="type !== 'fixed'">
                        </div>
                        <div class="relative" x-show="type === 'percentage_gapok'" style="display: none;">
                            <input type="number" step="0.0001" name="amount" value="{{ $allowance->type == 'percentage_gapok' ? $allowance->amount : '' }}" class="w-full pl-4 pr-12 py-3 rounded-xl bg-slate-50 border-none focus:ring-2 focus:ring-brand-blue transition-all font-bold" x-bind:disabled="type !== 'percentage_gapok'">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">Rate</span>
                        </div>
                    </div>
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
