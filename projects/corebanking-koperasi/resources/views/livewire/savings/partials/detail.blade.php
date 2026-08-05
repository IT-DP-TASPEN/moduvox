<div wire:key="view-form-detail" class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col mb-10 animate-fade-in">
    <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <button type="button" wire:click="closeView"
                class="p-2 bg-white hover:bg-slate-200 rounded-xl transition-all shadow-sm border border-slate-200">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
            </button>
            <div>
                <h2 class="font-extrabold text-sm text-slate-900 tracking-wider uppercase">Data Rekening: {{ $selectedAccount->account_no }}</h2>
                <p class="text-[10px] uppercase font-bold text-slate-500 tracking-widest mt-1">
                    Produk: <span class="text-indigo-600">{{ $selectedAccount->product?->name ?? 'N/A' }}</span> 
                    | Status: <span class="{{ $selectedAccount->status === 'ACTIVE' ? 'text-emerald-500' : 'text-rose-500' }}">{{ $selectedAccount->status }}</span>
                </p>
            </div>
        </div>
    </div>

    <div class="p-10 space-y-12">
        <!-- Balance Dashboard Block (Top Summary) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="p-6 bg-slate-50 rounded-[2rem] border border-slate-200/50 flex flex-col items-center text-center">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Saldo Kotor</p>
                <p class="text-2xl font-black text-slate-900 tracking-tighter">Rp {{ number_format($selectedAccount->balance, 2, ',', '.') }}</p>
            </div>
            <div class="p-6 bg-slate-50 rounded-[2rem] border border-slate-200/50 flex flex-col items-center text-center">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Saldo Akhir</p>
                <p class="text-2xl font-black text-slate-900 tracking-tighter">Rp {{ number_format($selectedAccount->balance, 2, ',', '.') }}</p>
            </div>
            <div class="p-6 bg-rose-50 rounded-[2rem] border border-rose-100 flex flex-col items-center text-center">
                <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-2">Saldo Terblokir</p>
                <p class="text-2xl font-black text-rose-600 tracking-tighter italic">Rp {{ number_format($selectedAccount->blocked_balance, 2, ',', '.') }}</p>
            </div>
            <div class="p-6 bg-slate-900 text-white rounded-[2rem] shadow-xl shadow-slate-900/20 flex flex-col items-center text-center scale-[1.02]">
                <p class="text-[10px] font-black text-white/50 uppercase tracking-widest mb-2">Saldo Efektif</p>
                <p class="text-3xl font-black tracking-tighter">Rp {{ number_format($selectedAccount->effective_balance, 2, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 pt-6">
            <!-- Column 1: Account & CIF Info -->
            <div class="space-y-8">
                <div>
                    <div class="border-b border-slate-100 pb-2 mb-6">
                        <p class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-slate-400">person</span> 
                            Informasi Pemilik (CIF)
                        </p>
                    </div>
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Lengkap Sesuai CIF</label>
                            <input type="text" value="{{ $selectedAccount->cif?->name ?? 'N/A' }}" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 uppercase" disabled>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. CIF</label>
                                <input type="text" value="{{ $selectedAccount->cif?->cif_no ?? '-' }}" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-700" disabled>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">NIK</label>
                                <input type="text" value="{{ $selectedAccount->cif?->nik ?? '-' }}" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-700" disabled>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Cabang Terdaftar</label>
                            <input type="text" value="{{ $selectedAccount->branch?->name ?? 'Pusat' }}" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-emerald-600 uppercase" disabled>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="border-b border-slate-100 pb-2 mb-6">
                        <p class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-slate-400">account_balance_wallet</span> 
                            Detail Rekening Simpanan
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. Rekening</label>
                            <input type="text" value="{{ $selectedAccount->account_no }}" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-700" disabled>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Buka</label>
                            <input type="text" value="{{ $selectedAccount->opened_at ? $selectedAccount->opened_at->format('d M Y') : '-' }}" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-700" disabled>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2: Mutation Summary -->
            <div class="space-y-8">
                <div>
                    <div class="border-b border-slate-100 pb-2 mb-6 flex justify-between items-center">
                        <p class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-slate-400">history</span> 
                            Riwayat Ringkas
                        </p>
                        <div class="flex items-center space-x-2">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Dari</span>
                            <input wire:model.live="dateFrom" type="date" class="bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl text-[10px] font-bold outline-none">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Sampai</span>
                            <input wire:model.live="dateTo" type="date" class="bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl text-[10px] font-bold outline-none">
                        </div>
                    </div>
                    
                    <div class="bg-white border border-slate-100 rounded-[2rem] overflow-hidden shadow-sm">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50">
                                <tr class="text-[8px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">
                                    <th class="py-4 px-6 text-center">Tanggal</th>
                                    <th class="py-4 px-6">Keterangan</th>
                                    <th class="py-4 px-6 text-right">Debit</th>
                                    <th class="py-4 px-6 text-right">Kredit</th>
                                    <th class="py-4 px-6 text-right">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($history as $trx)
                                <tr wire:key="trx-row-{{ $trx->id }}" class="text-[10px] font-bold group">
                                    <td class="py-4 px-6 text-slate-500 text-center">{{ $trx->transaction_date ? $trx->transaction_date->format('d/m/y') : '-' }}</td>
                                    <td class="py-4 px-6 text-slate-800 uppercase group-hover:text-indigo-600 transition-colors">{{ $trx->description }}</td>
                                    <td class="py-4 px-6 text-right text-rose-500">
                                        {{ $trx->isDebitMutation() ? number_format($trx->amount, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-right text-emerald-500">
                                        {{ $trx->isCreditMutation() ? number_format($trx->amount, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-right text-slate-700">
                                        {{ number_format($trx->balance_after ?? 0, 2, ',', '.') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-300 italic text-[10px]">Tidak ada mutasi dalam range ini</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-50">
                            {{ $history->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
        <p class="text-[10px] font-bold text-slate-400 tracking-widest uppercase">Petugas Terakhir: {{ $selectedAccount->creator?->name ?? 'SYSTEM' }}</p>
        <div class="flex items-center space-x-2">
            @can('savings.deposit')
            <a href="{{ route('savings.deposit') }}?account={{ $selectedAccount->account_no }}" class="px-4 py-2 bg-emerald-500 text-white rounded-xl text-[10px] font-extrabold uppercase tracking-widest shadow-md shadow-emerald-500/20 hover:scale-105 transition-all">Setor</a>
            @endcan
            @can('savings.withdrawal')
            <a href="{{ route('savings.withdrawal') }}?account={{ $selectedAccount->account_no }}" class="px-4 py-2 bg-rose-500 text-white rounded-xl text-[10px] font-extrabold uppercase tracking-widest shadow-md shadow-rose-500/20 hover:scale-105 transition-all">Tarik</a>
            @endcan
        </div>
    </div>
</div>
