<div class="p-0">
    <x-header title="Cetak Buku Tabungan" subtitle="Cetak riwayat transaksi ke buku fisik peserta" :user="$user" :role="$role">
        <x-slot:actions>
            <div class="flex items-center space-x-3">
                @if($viewMode === 'list')
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live.debounce.500ms="search" type="text" placeholder="Cari No. Rekening, NIK, Nama..."
                        class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-medium w-72">
                </div>
                @else
                <button wire:click="closeView" class="flex items-center space-x-2 bg-white text-slate-900 border border-slate-200 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Pilih Rekening Lain</span>
                </button>
                @endif
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-10">
        @if($viewMode === 'list')
        @if(!empty($search))
        <div class="mb-6 flex items-center justify-between px-2">
            <div class="flex items-center space-x-2 text-slate-500">
                <span class="material-symbols-outlined text-sm">info</span>
                <p class="text-[11px] font-bold uppercase tracking-widest">
                    Ditemukan <span class="text-slate-900">{{ $totalResults }}</span> Rekening untuk pencarian "{{ $search }}"
                </p>
            </div>
        </div>
        @endif
        
        <!-- TABLE VIEW -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/50">
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-center w-20">Aksi</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">No. Rekening</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Nama Peserta</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Produk</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-right">Saldo</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                             <td class="py-4 px-6">
                                <div class="flex items-center justify-center">
                                    <button wire:click="selectAccount({{ $item->id }})" class="p-2 bg-white text-slate-600 hover:bg-slate-50 rounded-xl shadow-sm border border-slate-200 transition-all hover:text-slate-900" title="Cetak Buku">
                                        <span class="material-symbols-outlined text-sm">menu_book</span>
                                    </button>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-sm font-extrabold text-slate-800 tracking-wider font-mono">{{ $item->account_no }}</span>
                            </td>
                             <td class="py-4 px-6">
                                <p class="font-bold text-sm text-slate-900 uppercase leading-none mb-1">{{ $item->cif->name }}</p>
                                <p class="text-[10px] text-slate-500 font-bold tracking-widest">NIK: {{ $item->cif->nik }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-[10px] text-slate-600 uppercase tracking-widest">{{ $item->product->name }}</p>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <p class="font-black text-sm text-slate-900 tracking-tight">Rp {{ number_format($item->balance, 2, ',', '.') }}</p>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg {{ $item->status == 'ACTIVE' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-32 text-center text-slate-400">
                                <span class="material-symbols-outlined text-5xl mb-4 opacity-20">person_search</span>
                                <p class="text-sm font-bold">Lakukan pencarian no rekening atau nama...</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(!empty($this->search) && $items->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $items->links() }}
            </div>
            @endif
        </div>
        
        @else
        
        <!-- PRINT OPTIONS VIEW -->
        <div class="max-w-6xl mx-auto space-y-8 animate-in slide-in-from-bottom-6 duration-700">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Profile Summary -->
                <div class="lg:col-span-1 space-y-8">
                    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                        <div class="p-8 text-center space-y-4">
                            <div class="w-20 h-20 rounded-[2rem] bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 mx-auto shadow-inner">
                                <span class="material-symbols-outlined text-4xl">person</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">{{ $selectedAccount->cif->name }}</h3>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.2em]">{{ $selectedAccount->account_no }}</p>
                            </div>
                            <div class="pt-4 flex justify-center">
                                <span class="px-4 py-1.5 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-xl border border-emerald-100 italic">
                                    {{ $selectedAccount->status }}
                                </span>
                            </div>
                        </div>
                        <div class="p-8 bg-slate-50/50 border-t border-slate-100 grid grid-cols-1 gap-6 text-left">
                            <div class="space-y-1">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Produk Simpanan</p>
                                <p class="text-xs font-bold text-slate-800 uppercase leading-none">{{ $selectedAccount->product->name }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Saldo Akhir</p>
                                <p class="text-lg font-black text-slate-900 tracking-tight leading-none">Rp {{ number_format($selectedAccount->balance, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range Selector -->
                    <div class="bg-slate-900 rounded-[2rem] shadow-xl p-10 space-y-6 text-left">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">event</span>
                            Filter Periode Cetak
                        </p>
                        <div class="space-y-5">
                            <div class="space-y-2">
                                <label class="text-[9px] font-bold text-slate-500 uppercase tracking-widest ml-1 leading-none">Dari Tanggal</label>
                                <input wire:model.live="dateFrom" type="date" class="w-full bg-slate-800 border-none rounded-xl text-white text-xs font-bold p-3 focus:ring-2 focus:ring-emerald-500/50">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-bold text-slate-500 uppercase tracking-widest ml-1 leading-none">Sampai Tanggal</label>
                                <input wire:model.live="dateTo" type="date" class="w-full bg-slate-800 border-none rounded-xl text-white text-xs font-bold p-3 focus:ring-2 focus:ring-emerald-500/50">
                            </div>
                        </div>
                        <div class="pt-6 border-t border-slate-800 flex justify-center">
                            <button onclick="window.print()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-4 rounded-xl font-black text-[10px] uppercase tracking-[0.2em] transition-all active:scale-95 shadow-lg shadow-emerald-500/20 flex items-center justify-center space-x-3">
                                <span class="material-symbols-outlined text-sm leading-none">print</span>
                                <span>Cetak Sekarang</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Transaction History Table for Book -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col h-[600px]">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest flex items-center gap-2 leading-none">
                                <span class="material-symbols-outlined text-sm text-slate-400">format_list_bulleted</span> Riwayat Transaksi Buku
                            </p>
                            <span class="text-[9px] font-black bg-white px-3 py-1 rounded-lg border border-slate-200 text-slate-500">{{ count($history) }} Baris ditemukan</span>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse min-w-[500px]">
                                <thead class="sticky top-0 bg-white shadow-sm z-10">
                                    <tr class="border-b border-slate-100">
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Tgl</th>
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Sandi</th>
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Mutasi</th>
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Saldo</th>
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">User</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @php $runningBalance = 0; @endphp
                                    @forelse($history as $trx)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="py-3 px-6 text-[10px] font-bold text-slate-600">{{ $trx->created_at->format('d/m/y') }}</td>
                                        <td class="py-3 px-6 text-[10px] font-black text-slate-400">{{ $trx->type == 'DEPOSIT' ? 'DEP' : 'WDR' }}</td>
                                        <td class="py-3 px-6 text-[10px] font-black text-right {{ $trx->type == 'DEPOSIT' ? 'text-emerald-500' : 'text-rose-500' }}">
                                            {{ number_format($trx->amount, 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-6 text-[10px] font-black text-right text-slate-900">
                                            {{ number_format($trx->balance_after ?? 0, 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-6 text-[9px] font-bold text-slate-400 text-center uppercase truncate max-w-[80px]">{{ $trx->created_by ?? '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="py-32 text-center text-slate-300">
                                            <span class="material-symbols-outlined text-4xl mb-4 opacity-20">history_edu</span>
                                            <p class="text-[10px] font-bold uppercase tracking-widest opacity-50">Tidak ada mutasi transaksi pada periode ini.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    @media print {
        header, .x-header, x-header, nav, #sidebar-nav, .no-print, x-slot\:actions, .actions {
            display: none !important;
        }
        body { background: white !important; }
        .p-10 { padding: 0 !important; }
        .bg-white { border: none !important; box-shadow: none !important; }
        /* Add specific styles for passbook printer if needed */
    }
</style>
