<div class="p-0">
    <x-header title="Pembayaran Bunga Simpanan Berjangka" subtitle="Proses bunga pending ke rekening tabungan internal anggota" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            @if($viewMode === 'detail')
                <button wire:click="closeView" class="flex items-center space-x-2 bg-white text-slate-600 border border-slate-200 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Kembali ke Daftar</span>
                </button>
            @else
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <select wire:model.live="filter_branch" class="pl-3 pr-10 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 appearance-none shadow-sm">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                            <span class="material-symbols-outlined text-sm">expand_more</span>
                        </div>
                    </div>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="No Rekening atau Nama..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 w-64 shadow-sm">
                    </div>
                </div>
            @endif
        </x-slot>
    </x-header>

    <div class="p-10">
        @if (session()->has('success'))
            <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-[2rem] border border-emerald-100 flex items-center mb-8 shadow-sm">
                <span class="material-symbols-outlined text-emerald-600 mr-3">check_circle</span>
                <p class="font-bold text-sm">{{ session('success') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-rose-50 text-rose-700 px-6 py-4 rounded-[2rem] border border-rose-100 flex items-center mb-8 shadow-sm">
                <span class="material-symbols-outlined text-rose-600 mr-3">error</span>
                <p class="font-bold text-sm">{{ session('error') }}</p>
            </div>
        @endif

        @if($viewMode === 'detail' && $selectedAccount)
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden mb-8">
                <div class="p-8 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center space-x-5">
                        <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                            <span class="material-symbols-outlined text-3xl">account_balance_wallet</span>
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="text-base font-black text-slate-900 uppercase tracking-tight">{{ $selectedAccount->account_no }}</h4>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $selectedAccount->cif->name }} • {{ $selectedAccount->product->name }}</p>
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Tujuan: {{ $selectedAccount->savingAccount?->account_no ?? 'Belum ditautkan' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Pokok</p>
                        <p class="text-2xl font-black text-slate-900 tracking-tighter">Rp {{ number_format($selectedAccount->amount, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        @if($viewMode === 'grid')
                            <tr class="border-b border-slate-100 bg-slate-50/50">
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center w-20">Opsi</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">No. Rekening</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Nama Anggota</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Produk</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Pokok</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Jatuh Tempo</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Status</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Rekening Tujuan</th>
                            </tr>
                        @else
                            <tr class="border-b border-slate-100 bg-slate-50/50">
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center w-20">Opsi</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Bulan Ke</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Tanggal Jadwal</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Bunga Bruto</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Pajak</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Net Dibayar</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Status</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($items as $item)
                            @if($viewMode === 'grid')
                                <tr wire:key="interest-account-{{ $item->id }}" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="py-4 px-6 text-center">
                                        <button wire:click="selectAccount({{ $item->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-slate-900 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto">
                                            <span class="material-symbols-outlined text-sm">list_alt</span>
                                        </button>
                                    </td>
                                    <td class="py-4 px-6 font-black text-xs text-slate-900">{{ $item->account_no }}</td>
                                    <td class="py-4 px-6">
                                        <p class="font-black text-xs text-slate-900 uppercase leading-none mb-1">{{ $item->cif->name }}</p>
                                        <p class="text-[9px] text-slate-400 font-bold tracking-widest uppercase">{{ $item->cif->cif_no }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-xs font-bold text-slate-600">{{ $item->product->name }}</td>
                                    <td class="py-4 px-6 text-right font-black text-xs text-slate-900">Rp {{ number_format($item->amount, 2, ',', '.') }}</td>
                                    <td class="py-4 px-6 text-center text-[10px] font-black text-slate-900 tracking-widest">{{ $item->maturity_date->format('d/m/Y') }}</td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded border {{ $item->status === 'MATURED' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100' }}">{{ $item->status }}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($item->savingAccount)
                                            <span class="text-xs font-black text-slate-900 tracking-tight">{{ $item->savingAccount->account_no }}</span>
                                        @else
                                            <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded border bg-rose-50 text-rose-600 border-rose-100">Belum ditautkan</span>
                                        @endif
                                    </td>
                                </tr>
                            @else
                                <tr wire:key="interest-payment-{{ $item->id }}" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="py-4 px-6 text-center">
                                        @if($item->status === 'PENDING')
                                            <button wire:click="pay({{ $item->id }})" wire:loading.attr="disabled" wire:target="pay({{ $item->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-emerald-600 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto disabled:opacity-50">
                                                <span wire:loading.remove wire:target="pay({{ $item->id }})" class="material-symbols-outlined text-sm">payments</span>
                                                <span wire:loading wire:target="pay({{ $item->id }})" class="material-symbols-outlined text-sm animate-spin">refresh</span>
                                            </button>
                                        @else
                                            <span class="w-8 h-8 flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-lg border border-emerald-100 mx-auto">
                                                <span class="material-symbols-outlined text-sm">check</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-center text-xs font-black text-slate-900">Bulan {{ $item->month_index }}</td>
                                    <td class="py-4 px-6 text-center text-[10px] font-black text-slate-900 tracking-widest">{{ $item->schedule_date->format('d/m/Y') }}</td>
                                    <td class="py-4 px-6 text-right font-black text-xs text-slate-900">Rp {{ number_format($item->gross_interest, 2, ',', '.') }}</td>
                                    <td class="py-4 px-6 text-right font-black text-xs text-rose-500">Rp {{ number_format($item->tax_amount, 2, ',', '.') }}</td>
                                    <td class="py-4 px-6 text-right font-black text-xs text-emerald-600">Rp {{ number_format($item->net_interest, 2, ',', '.') }}</td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded border {{ $item->status === 'PENDING' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100' }}">{{ $item->status }}</span>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="py-32 text-center text-slate-300">
                                    <span class="material-symbols-outlined text-6xl mb-4 opacity-50">{{ $viewMode === 'grid' ? 'search' : 'task_alt' }}</span>
                                    @if($viewMode === 'grid' && !$search && !$filter_branch)
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Silakan cari nomor rekening atau nama anggota<br>untuk memilih simpanan berjangka</p>
                                    @elseif($viewMode === 'grid')
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Tidak ada rekening dengan bunga pending<br>untuk pencarian ini</p>
                                    @else
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Semua jadwal bunga sudah terbayar<br>untuk rekening ini</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
                <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30 font-bold text-xs uppercase tracking-widest">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
