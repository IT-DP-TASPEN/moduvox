<div wire:key="view-comprehensive-detail" class="space-y-10 animate-fade-in">
    <!-- Header & Navigation -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col">
        <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="{{ route('savings.inquiry') }}" wire:navigate
                    class="p-2 bg-white hover:bg-slate-200 rounded-xl transition-all shadow-sm border border-slate-200">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                </a>
                <div>
                    <h2 class="font-extrabold text-sm text-slate-900 tracking-wider uppercase">Data Rekening: {{
                        $selectedAccount->account_no }}</h2>
                    <p class="text-[10px] uppercase font-bold text-slate-500 tracking-widest mt-1">
                        Produk: <span class="text-indigo-600">{{ $selectedAccount->product?->name ?? 'N/A' }}</span>
                        | Status: <span
                            class="{{ $selectedAccount->status === 'ACTIVE' ? 'text-emerald-500' : 'text-rose-500' }}">{{
                            $selectedAccount->status }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="p-10 space-y-12">
            <!-- SECTION: Comprehensive CIF Form (Standardized) -->
            <form class="flex flex-col">
                <fieldset disabled class="m-0 p-0 border-0">
                    <div class="space-y-12">
                        <!-- SECTION 0: Saving Account Info -->
                        <div>
                            <div
                                class="border-b border-slate-200 pb-2 mb-6 text-slate-900 flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">account_balance</span>
                                    Informasi Rekening & Saldo
                                </p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
                                <div class="space-y-2 text-left">
                                    <label
                                        class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No.
                                        Rekening</label>
                                    <input type="text" value="{{ $selectedAccount->account_no }}"
                                        class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                </div>
                                <div class="space-y-2 text-left">
                                    <label
                                        class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Produk
                                        Simpanan</label>
                                    <input type="text" value="{{ $selectedAccount->product?->name }}"
                                        class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-indigo-600">
                                </div>

                                <!-- Balances Row 1 -->
                                <div class="space-y-2 text-left">
                                    <label
                                        class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Saldo
                                        Kotor (Buku)</label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-5 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                                        <input type="text"
                                            value="{{ number_format($selectedAccount->balance, 2, ',', '.') }}"
                                            class="w-full pl-12 pr-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-slate-900">
                                    </div>
                                </div>
                                <div class="space-y-2 text-left">
                                    <label
                                        class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Saldo
                                        Minimum Produk</label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-5 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                                        <input type="text"
                                            value="{{ number_format($selectedAccount->product?->min_balance ?? 0, 2, ',', '.') }}"
                                            class="w-full pl-12 pr-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-amber-600">
                                    </div>
                                </div>

                                <!-- Balances Row 2 -->
                                <div class="space-y-2 text-left">
                                    <label
                                        class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Saldo
                                        Terblokir</label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-5 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                                        <input type="text"
                                            value="{{ number_format($selectedAccount->blocked_balance, 2, ',', '.') }}"
                                            class="w-full pl-12 pr-5 py-3.5 bg-rose-50 border border-rose-100 rounded-2xl font-bold text-sm text-rose-600">
                                    </div>
                                </div>
                                <div class="space-y-2 text-left">
                                    <label
                                        class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Saldo
                                        Efektif (Tersedia)</label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-5 top-1/2 -translate-y-1/2 text-sm font-bold text-white/50">Rp</span>
                                        <input type="text"
                                            value="{{ number_format($selectedAccount->effective_balance, 2, ',', '.') }}"
                                            class="w-full pl-12 pr-5 py-3.5 bg-slate-900 border border-slate-900 rounded-2xl font-black text-sm text-white shadow-lg shadow-slate-900/20">
                                    </div>
                                </div>

                                <div class="space-y-2 text-left">
                                    <label
                                        class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal
                                        Pembukaan</label>
                                    <input type="text" value="{{ $selectedAccount->opened_at?->format('d/m/Y') }}"
                                        class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm">
                                </div>
                                <div class="space-y-2 text-left">
                                    <label
                                        class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status
                                        Rekening</label>
                                    <input type="text" value="{{ $selectedAccount->status }}"
                                        class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm {{ $selectedAccount->status === 'ACTIVE' ? 'text-emerald-600' : 'text-rose-600' }}">
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 1: Linked CIF Profile -->
                        <div>
                            <div class="border-b border-slate-200 pb-2 mb-6 text-indigo-600 flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">person</span>
                                    Profil Pemilik / CIF Anggota
                                </p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. CIF</label>
                                    <input type="text" readonly value="{{ $selectedAccount->cif->cif_no }}" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-indigo-600">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Cabang CIF</label>
                                    <input type="text" readonly value="{{ $selectedAccount->cif->branch->name ?? '-' }}" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status CIF</label>
                                    <input type="text" readonly value="{{ $selectedAccount->cif->status }}" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm {{ $selectedAccount->cif->status === 'ACTIVE' ? 'text-emerald-600' : 'text-rose-600' }}">
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3.5: Active Blocks -->
                        <div>
                            <div class="border-b border-slate-200 pb-2 mb-6 text-rose-600 flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">lock</span>
                                    Daftar Blokir Aktif
                                </p>
                                <span class="px-3 py-1 bg-rose-50 border border-rose-100 rounded-lg text-[9px] font-black text-rose-600 uppercase tracking-widest">
                                    Total: Rp {{ number_format($selectedAccount->blocked_balance, 2, ',', '.') }}
                                </span>
                            </div>
                            
                            <div class="bg-white border border-slate-100 rounded-[2rem] overflow-hidden shadow-sm">
                                <table class="w-full text-left">
                                    <thead class="bg-slate-50/50">
                                        <tr class="text-[8px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">
                                            <th class="py-4 px-6">No. Referensi (REF)</th>
                                            <th class="py-4 px-6">Alasan Blokir</th>
                                            <th class="py-4 px-6 text-center">Tgl. Blokir</th>
                                            <th class="py-4 px-6 text-right">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @forelse($selectedAccount->activeBlocks as $block)
                                        <tr class="text-[10px] font-bold group">
                                            <td class="py-4 px-6 text-slate-900 font-mono uppercase">{{ $block->reference_no ?? '-' }}</td>
                                            <td class="py-4 px-6 text-slate-500 uppercase">{{ $block->reason }}</td>
                                            <td class="py-4 px-6 text-center text-slate-400 font-medium">{{ $block->created_at->format('d/m/Y') }}</td>
                                            <td class="py-4 px-6 text-right text-rose-600 font-black">Rp {{ number_format($block->amount, 2, ',', '.') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="py-12 text-center text-slate-300 italic text-[10px]">
                                                Tidak ada dana yang terblokir pada rekening ini</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- SECTION: Linked Loans -->
                        @if($selectedAccount->loanAccounts && $selectedAccount->loanAccounts->count() > 0)
                        <div>
                            <div class="border-b border-slate-200 pb-2 mb-6 text-rose-600 flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">link</span>
                                    Koneksi Autodebet Pinjaman / Kredit
                                </p>
                                <span class="px-3 py-1 bg-rose-50 border border-rose-100 rounded-lg text-[9px] font-black text-rose-600 uppercase tracking-widest">
                                    Total: {{ $selectedAccount->loanAccounts->count() }} Terhubung
                                </span>
                            </div>
                            
                            <div class="space-y-3">
                                @foreach($selectedAccount->loanAccounts as $loan)
                                <div class="p-4 bg-white border border-slate-100 rounded-2xl">
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="space-y-1">
                                            <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest">No. Rekening</p>
                                            <p class="text-xs font-black text-slate-800 font-mono">{{ $loan->account_no }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest">Cabang</p>
                                            <p class="text-xs font-bold text-slate-600">{{ $loan->branch->name ?? '-' }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest">Status</p>
                                            <span class="text-[9px] font-black px-2 py-0.5 rounded-full {{ $loan->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600' }}">{{ $loan->status }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- SECTION: Linked Deposit Payouts -->
                        @if($selectedAccount->depositAccounts && $selectedAccount->depositAccounts->count() > 0)
                        <div>
                            <div class="border-b border-slate-200 pb-2 mb-6 text-amber-600 flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">account_balance_wallet</span>
                                    Koneksi Rekening Pencairan Simpanan Berjangka
                                </p>
                                <span class="px-3 py-1 bg-amber-50 border border-amber-100 rounded-lg text-[9px] font-black text-amber-600 uppercase tracking-widest">
                                    Total: {{ $selectedAccount->depositAccounts->count() }} Terhubung
                                </span>
                            </div>
                            
                            <div class="space-y-3">
                                @foreach($selectedAccount->depositAccounts as $deposit)
                                <div class="p-4 bg-white border border-slate-100 rounded-2xl">
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="space-y-1">
                                            <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest">No. Rekening</p>
                                            <p class="text-xs font-black text-slate-800 font-mono">{{ $deposit->account_no }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest">Cabang</p>
                                            <p class="text-xs font-bold text-slate-600">{{ $deposit->branch->name ?? '-' }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest">Status</p>
                                            <span class="text-[9px] font-black px-2 py-0.5 rounded-full {{ $deposit->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-600' }}">{{ $deposit->status }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                    </div>
                </fieldset>
            </form>

                        <!-- SECTION 4: Mutation History -->
                        <div>
                            <div
                                class="border-b border-slate-200 pb-2 mb-6 flex justify-between items-center text-slate-900">
                                <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm text-slate-400">history</span>
                                    Riwayat Ringkas Mutasi
                                </p>
                                <div class="flex items-center space-x-2">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Dari</span>
                                    <input wire:model.live="dateFrom" type="date"
                                        class="bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl text-[10px] font-bold outline-none">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Sampai</span>
                                    <input wire:model.live="dateTo" type="date"
                                        class="bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl text-[10px] font-bold outline-none">
                                </div>
                            </div>

                            <div class="bg-white border border-slate-100 rounded-[2rem] overflow-hidden shadow-sm">
                                <table class="w-full text-left">
                                    <thead class="bg-slate-50/50">
                                        <tr
                                            class="text-[8px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">
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
                                            <td class="py-4 px-6 text-slate-500 text-center text-left">{{
                                                $trx->transaction_date ? $trx->transaction_date->format('d/m/y') : '-'
                                                }}</td>
                                            <td
                                                class="py-4 px-6 text-slate-800 uppercase group-hover:text-indigo-600 transition-colors text-left">
                                                {{ $trx->description }}</td>
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
                                            <td colspan="5" class="py-12 text-center text-slate-300 italic text-[10px]">
                                                Tidak ada mutasi dalam range ini</td>
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
