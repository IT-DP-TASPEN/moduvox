@extends('admin.layout')

@section('header', 'Master Tunjangan & Potongan Global')

@section('content')
<div class="max-w-6xl">
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-10">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-bold text-slate-800 flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-brand-blue/5 text-brand-blue flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    Kelola Komponen Global
                </h3>
            </div>

            <!-- Form Tambah -->
            <div class="bg-slate-50 p-6 rounded-3xl mb-10 border border-slate-100" x-data="{ type: 'fixed' }">
                <h4 class="font-bold text-slate-700 mb-4">Tambah Komponen Baru</h4>
                <form action="{{ route('admin.global-allowances.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    @csrf
                    <div class="col-span-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-2 block">Nama Komponen</label>
                        <input type="text" name="name" placeholder="Misal: Uang Makan, Pulsa" class="w-full px-4 py-3 rounded-xl bg-white border-none focus:ring-2 focus:ring-brand-blue transition-all" required>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-2 block">Kategori</label>
                        <select name="category" class="w-full px-4 py-3 rounded-xl bg-white border-none focus:ring-2 focus:ring-brand-blue transition-all" required>
                            <option value="earning">Tunjangan Menambah THP (+)</option>
                            <option value="deduction">Potongan Mengurangi THP (-)</option>
                            <option value="company_paid">Dibayar Perusahaan (Non-THP)</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-2 block">Jenis Nilai</label>
                        <select name="type" x-model="type" class="w-full px-4 py-3 rounded-xl bg-white border-none focus:ring-2 focus:ring-brand-blue transition-all" required>
                            <option value="fixed">Nominal (Rp)</option>
                            <option value="percentage_gapok">Persentase (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-2 block">Target Status</label>
                        <select name="target_status" class="w-full px-4 py-3 rounded-xl bg-white border-none focus:ring-2 focus:ring-brand-blue transition-all" required>
                            <option value="All">Semua Karyawan</option>
                            <option value="Tetap">Hanya Tetap</option>
                            <option value="Kontrak">Hanya Kontrak</option>
                            <option value="OJT">Hanya OJT</option>
                            <option value="PE">Hanya PE</option>
                        </select>
                    </div>
                    <div class="col-span-1 md:col-span-4 mt-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-2 block">Nilai</label>
                        
                        <!-- Input for Nominal (Rupiah Formatted) -->
                        <div class="relative" x-show="type === 'fixed'">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">Rp</span>
                            <input type="text" name="amount" placeholder="Misal: 500000" class="rupiah-input w-full pl-12 pr-4 py-3 rounded-xl bg-white border-none focus:ring-2 focus:ring-brand-blue transition-all font-bold" x-bind:disabled="type !== 'fixed'" required>
                        </div>
                        
                        <!-- Input for Percentage (Decimal) -->
                        <div class="relative" x-show="type === 'percentage_gapok'" style="display: none;">
                            <input type="number" step="0.0001" name="amount" placeholder="Misal: 0.05 untuk 5%" class="w-full pl-4 pr-12 py-3 rounded-xl bg-white border-none focus:ring-2 focus:ring-brand-blue transition-all font-bold" x-bind:disabled="type !== 'percentage_gapok'" required>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">Rate Decimal</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="w-full bg-brand-blue text-white px-4 py-3 rounded-xl font-bold hover:opacity-90 transition-all h-[48px]">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Data -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-xs font-black text-slate-400 uppercase tracking-wider">Nama Komponen</th>
                            <th class="py-4 px-4 text-xs font-black text-slate-400 uppercase tracking-wider">Kategori</th>
                            <th class="py-4 px-4 text-xs font-black text-slate-400 uppercase tracking-wider">Target</th>
                            <th class="py-4 px-4 text-xs font-black text-slate-400 uppercase tracking-wider">Nilai</th>
                            <th class="py-4 px-4 text-xs font-black text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($allowances as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-4 font-bold text-slate-700">
                                {{ $item->name }}
                            </td>
                            <td class="py-4 px-4">
                                @if($item->category === 'deduction')
                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">Potongan</span>
                                @elseif($item->category === 'earning')
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Tunjangan</span>
                                @else
                                    <span class="px-3 py-1 bg-blue-100 text-brand-blue rounded-full text-xs font-bold">Perusahaan</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 font-medium text-slate-600">
                                {{ $item->target_status }}
                            </td>
                            <td class="py-4 px-4 font-bold text-brand-blue">
                                @if($item->type === 'fixed')
                                    Rp {{ number_format($item->amount, 0, ',', '.') }}
                                @else
                                    {{ $item->amount * 100 }}% dari Gapok
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.global-allowances.edit', $item->id) }}" class="w-8 h-8 rounded-xl bg-blue-50 text-brand-blue hover:bg-brand-blue hover:text-white flex items-center justify-center transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.global-allowances.destroy', $item->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmAction(event, 'Hapus komponen {{ $item->name }}?')" class="w-8 h-8 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 font-medium">Belum ada komponen global yang ditambahkan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
