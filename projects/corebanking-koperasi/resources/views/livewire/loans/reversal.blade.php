<div class="p-0">
    <x-header title="Reversal Pencairan Pinjaman" subtitle="Pencarian fasilitas aktif untuk pengajuan reversal distribusi pinjaman" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live.debounce.500ms="search" type="text" placeholder="No Rekening, PK atau Nama..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 w-64 shadow-sm">
                </div>
            </div>
        </x-slot>
    </x-header>

    <div class="p-10">
        @if (session()->has('success'))
            <div class="mb-8 bg-emerald-50 text-emerald-700 p-6 border border-emerald-100 rounded-[2rem] flex items-center gap-4 animate-in fade-in duration-500">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600">
                    <span class="material-symbols-outlined text-xl">check_circle</span>
                </div>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-8 bg-rose-50 text-rose-700 p-6 border border-rose-100 rounded-[2rem] flex items-center gap-4 animate-in fade-in duration-500">
                <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600">
                    <span class="material-symbols-outlined text-xl">error</span>
                </div>
                <span class="font-bold text-sm">{{ session('error') }}</span>
            </div>
        @endif

        @if($viewMode === 'grid')
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50">
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center w-20">OPSI</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">No. Rekening</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Nama Anggota</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">No. PK</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Nilai Cair</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($items as $item)
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="py-4 px-6 text-center">
                                        <button wire:click="selectLoan({{ $item->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-slate-900 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto">
                                            <span class="material-symbols-outlined text-sm">visibility</span>
                                        </button>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-slate-900 tracking-tight">{{ $item->account_no }}</span>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $item->product->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-black text-xs text-slate-900 uppercase leading-none mb-1">{{ $item->cif->full_name }}</p>
                                        <p class="text-[9px] text-slate-400 font-bold tracking-widest uppercase">{{ $item->cif->cif_no }}</p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black bg-slate-100 text-slate-600 uppercase tracking-widest">{{ $item->pk_number }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <p class="font-black text-xs text-slate-900 tracking-tighter">Rp {{ number_format($item->principal_amount, 2, ',', '.') }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded border bg-emerald-50 text-emerald-600 border-emerald-100">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-32 text-center text-slate-300">
                                        <span class="material-symbols-outlined text-6xl mb-4 opacity-50">search</span>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Cari rekening kredit yang sudah dicairkan<br>untuk mengajukan reversal distribusi</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            @if($selectedLoan)
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
                    <!-- Integrated Info Card (CIF + Account) -->
                    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                        <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <div class="flex items-center space-x-4">
                                <button wire:click="closeView" class="p-2 bg-white hover:bg-slate-200 rounded-xl transition-all shadow-sm border border-slate-200">
                                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                                </button>
                                <div>
                                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ $selectedLoan->cif->full_name }}</h3>
                                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $selectedLoan->account_no }} • {{ $selectedLoan->product->name }}</p>
                                </div>
                            </div>
                            <div class="px-4 py-2 bg-emerald-50 border border-emerald-100 rounded-xl text-center">
                                <p class="text-[8px] font-black text-emerald-400 uppercase leading-none mb-1">Status Fasilitas</p>
                                <p class="text-[10px] font-black text-emerald-600 uppercase">{{ $selectedLoan->status }}</p>
                            </div>
                        </div>

                        <div class="p-10 grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div class="space-y-6 text-left">
                                <div class="border-b border-slate-100 pb-2">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Informasi Pinjaman</p>
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">No. PK</label>
                                        <p class="text-xs font-bold text-amber-600 uppercase tracking-tight">{{ $selectedLoan->pk_number }}</p>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Tgl Cair</label>
                                        <p class="text-xs font-bold text-slate-900">{{ $selectedLoan->disbursement_date?->format('d/m/Y') ?? '-' }}</p>
                                    </div>
                                    <div class="space-y-2 col-span-2">
                                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Produk</label>
                                        <p class="text-xs font-bold text-indigo-600 uppercase">{{ $selectedLoan->product->name }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6 text-left">
                                <div class="border-b border-slate-100 pb-2">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Outstanding Saat Ini</p>
                                </div>
                                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-4">
                                    <div class="flex justify-between items-center">
                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Saldo Pokok</p>
                                        <p class="text-sm font-black text-slate-900 font-mono">Rp {{ number_format($selectedLoan->outstanding_principal, 2, ',', '.') }}</p>
                                    </div>
                                    <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                                        <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Total Outstanding</p>
                                        <p class="text-lg font-black text-slate-900 font-mono">Rp {{ number_format($selectedLoan->outstanding_total, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 p-10">
                        <div class="space-y-8">
                            <div class="border-b border-slate-100 pb-2">
                                <p class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm text-rose-500">settings_backup_restore</span>
                                    Alasan & Detail Reversal Pencairan
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 items-start">
                                <div class="md:col-span-12 space-y-4">
                                    <label class="text-[10px] uppercase tracking-widest font-black text-slate-400 ml-1">Keterangan / Alasan Reversal <span class="text-rose-500">*</span></label>
                                    <textarea wire:model="reason" rows="4" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-rose-500/5 focus:border-rose-500 transition-all" placeholder="Jelaskan secara detail alasan pembatalan pencairan ini..."></textarea>
                                    @error('reason') <span class="text-[10px] text-rose-500 font-bold ml-1 italic">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-7 space-y-6">
                                    <div class="p-8 bg-rose-50 border border-rose-100 rounded-3xl space-y-6">
                                        <p class="text-[11px] font-black text-rose-600 uppercase tracking-widest">Estimasi Nilai Reversal</p>
                                        
                                        @php
                                            $disbTx = $selectedLoan->transactions->where('transaction_type', 'DISBURSEMENT')->first();
                                            $revAmount = $disbTx ? $disbTx->total_amount : $selectedLoan->principal_amount;
                                        @endphp

                                        <div class="space-y-4">
                                            <div class="flex justify-between items-center text-slate-500">
                                                <span class="text-[10px] font-bold uppercase tracking-widest">Nilai Pencairan Bersih</span>
                                                <span class="text-sm font-black text-slate-900">Rp {{ number_format($revAmount, 2, ',', '.') }}</span>
                                            </div>
                                            <div class="pt-4 border-t border-rose-200 flex justify-between items-center">
                                                <div>
                                                    <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest leading-none mb-1">Total Dana Ditarik Kembali</p>
                                                    <p class="text-[9px] font-bold text-rose-400 uppercase tracking-widest">Dari Tabungan: {{ $selectedLoan->savingAccount?->account_no ?? 'TUNAI' }}</p>
                                                </div>
                                                <p class="text-xl font-black text-rose-700">Rp {{ number_format($revAmount, 2, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-5 p-6 bg-slate-900 rounded-3xl text-white relative overflow-hidden h-full">
                                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full blur-xl"></div>
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Informasi Penting</h4>
                                    <ul class="space-y-3">
                                        <li class="flex gap-2">
                                            <span class="material-symbols-outlined text-xs text-rose-400">warning</span>
                                            <p class="text-[9px] font-bold leading-relaxed text-slate-300 uppercase">Jadwal angsuran akan dibatalkan (VOID)</p>
                                        </li>
                                        <li class="flex gap-2">
                                            <span class="material-symbols-outlined text-xs text-rose-400">warning</span>
                                            <p class="text-[9px] font-bold leading-relaxed text-slate-300 uppercase">Saldo tabungan akan berkurang otomatis</p>
                                        </li>
                                        <li class="flex gap-2">
                                            <span class="material-symbols-outlined text-xs text-rose-400">lock</span>
                                            <p class="text-[9px] font-bold leading-relaxed text-slate-300 uppercase">Proses ini tidak dapat diputar balik (final)</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-10 pt-8 border-t border-slate-100 flex justify-end items-center">
                                <button wire:click="submit" class="px-12 py-4 bg-rose-600 shadow-lg shadow-rose-600/20 text-white rounded-xl font-black text-xs uppercase tracking-widest transition-all active:scale-95 flex items-center space-x-3">
                                    <span class="material-symbols-outlined text-sm">settings_backup_restore</span>
                                    <span>Ajukan Reversal Sekarang</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
