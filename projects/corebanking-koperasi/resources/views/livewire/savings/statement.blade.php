<div class="p-0">
    <x-header title="Cetak Rekening Koran" subtitle="Pencetakan laporan mutasi (statement) rekening simpanan" :user="$user" :role="$role">
        <x-slot name="actions">
            @if($viewMode === 'list')
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live.debounce.500ms="search" type="text" placeholder="No Rekening / CIF / Nama..." 
                        class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold w-80 shadow-sm">
                </div>
            @else
                <button wire:click="closeView" class="flex items-center space-x-2 bg-white text-slate-600 border border-slate-200 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Kembali ke Pencarian</span>
                </button>
            @endif
        </x-slot>
    </x-header>

    <div class="p-10">
        @if($viewMode === 'list')
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden animate-in fade-in duration-500">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50">
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center w-20">OPSI</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">No. Rekening</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Nama Anggota</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Saldo Saat Ini</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($accounts as $item)
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="py-4 px-6 text-center">
                                        <button wire:click="selectAccount({{ $item->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-slate-900 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto">
                                            <span class="material-symbols-outlined text-sm">receipt_long</span>
                                        </button>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-slate-900 tracking-tight">{{ $item->account_no }}</span>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $item->product->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-black text-xs text-slate-900 uppercase leading-none mb-1">{{ $item->cif->name }}</p>
                                        <p class="text-[9px] text-slate-400 font-bold tracking-widest uppercase">{{ $item->cif->cif_no }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <p class="font-black text-xs text-slate-900 tracking-tighter">Rp {{ number_format($item->balance, 2, ',', '.') }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded border {{ $item->status === 'ACTIVE' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-50 text-slate-400 border-slate-100' }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-32 text-center text-slate-300">
                                        @if(!$search)
                                            <span class="material-symbols-outlined text-6xl mb-4 opacity-50">search</span>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Silakan cari nomor rekening atau nama anggota<br>untuk menampilkan rekening koran</p>
                                        @else
                                            <span class="material-symbols-outlined text-6xl mb-4 opacity-50">drafts</span>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Data tidak ditemukan untuk pencarian: "{{ $search }}"</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($accounts->hasPages())
                    <div class="px-8 py-4 bg-white/50 border-t border-slate-50">
                        {{ $accounts->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- PREVIEW SECTION -->
            <div class="max-w-5xl mx-auto space-y-6 animate-in zoom-in-95 duration-300 no-print pb-20">
                <!-- Filter Card -->
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 p-6 flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="flex gap-4">
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Dari Tanggal</label>
                            <input type="date" wire:model.live="startDate" class="mt-1 block w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-slate-900 focus:border-slate-900">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Sampai Tanggal</label>
                            <input type="date" wire:model.live="endDate" class="mt-1 block w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-slate-900 focus:border-slate-900">
                        </div>
                    </div>
                    <button onclick="window.print()" class="bg-slate-900 text-white px-6 py-3 rounded-xl font-bold text-xs hover:shadow-lg transition-all flex items-center space-x-2">
                        <span class="material-symbols-outlined text-sm">print</span>
                        <span>Cetak</span>
                    </button>
                </div>

                <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-10 print:shadow-none print:border-none print:p-0" id="print-area">
                    <!-- Header Statement -->
                    <div class="flex justify-between border-b-2 border-slate-900 pb-6 mb-6">
                        <div>
                            <h2 class="text-2xl font-black text-slate-900 uppercase">REKENING KORAN</h2>
                            <p class="text-sm text-slate-600 mt-1 font-bold">{{ \App\Models\Company::first()?->name ?? 'KOPERASI SIMPAN PINJAM' }}</p>
                        </div>
                        <div class="text-right text-xs">
                            <p><strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                            <p><strong>Tgl Cetak:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Info Rekening -->
                    <div class="grid grid-cols-2 gap-8 mb-8 text-sm">
                        <div class="space-y-2">
                            <div class="grid grid-cols-3"><span class="text-slate-500">Nama Anggota</span> <span class="col-span-2 font-bold uppercase">: {{ $selectedAccount->cif->name }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-slate-500">No. CIF</span> <span class="col-span-2 font-bold">: {{ $selectedAccount->cif->cif_no }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-slate-500">Alamat</span> <span class="col-span-2 font-bold">: {{ $selectedAccount->cif->alamat_lengkap }}</span></div>
                        </div>
                        <div class="space-y-2">
                            <div class="grid grid-cols-3"><span class="text-slate-500">No. Rekening</span> <span class="col-span-2 font-bold">: {{ $selectedAccount->account_no }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-slate-500">Produk</span> <span class="col-span-2 font-bold">: {{ $selectedAccount->product->name }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-slate-500">Cabang</span> <span class="col-span-2 font-bold">: {{ $selectedAccount->branch->name ?? '-' }}</span></div>
                        </div>
                    </div>

                    <!-- Tabel Mutasi -->
                    <table class="w-full text-xs border-collapse">
                        <thead>
                            <tr class="border-y-2 border-slate-800 text-left">
                                <th class="py-2 px-2">Tanggal</th>
                                <th class="py-2 px-2">Keterangan</th>
                                <th class="py-2 px-2 text-right">Debit</th>
                                <th class="py-2 px-2 text-right">Kredit</th>
                                <th class="py-2 px-2 text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($transactions as $tx)
                                @php
                                    $isDebit = $tx->isDebitMutation();
                                    $isCredit = $tx->isCreditMutation();
                                @endphp
                                <tr>
                                    <td class="py-2 px-2 whitespace-nowrap">{{ $tx->created_at->format('d/m/Y') }}</td>
                                    <td class="py-2 px-2">{{ $tx->description }}</td>
                                    <td class="py-2 px-2 text-right">{{ $isDebit ? number_format(abs($tx->amount), 2, ',', '.') : '-' }}</td>
                                    <td class="py-2 px-2 text-right">{{ $isCredit ? number_format(abs($tx->amount), 2, ',', '.') : '-' }}</td>
                                    <td class="py-2 px-2 text-right">{{ number_format($tx->balance_after, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center italic text-slate-500">Tidak ada mutasi pada periode ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-slate-800 font-bold">
                                <td colspan="4" class="py-2 px-2 text-right">Saldo Akhir :</td>
                                <td class="py-2 px-2 text-right">Rp {{ number_format($selectedAccount->balance, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    @media print {
        header, .header, x-header, .no-print, nav, #sidebar-nav, .actions, .print\:hidden {
            display: none !important;
        }
        .p-10 { padding: 0 !important; }
        body { 
            margin: 0; 
            padding: 0; 
            background-color: white !important; 
            color: black !important;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
        }
    }
</style>
