<div class="p-0">
    <x-header title="Cetak Slip Transaksi" subtitle="Pencarian mutasi dan pencetakan bukti transaksi" :user="$user" :role="$role">
        <x-slot:actions>
            <div class="flex items-center space-x-3">
                @if($viewMode === 'list')
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live.debounce.500ms="search" type="text" placeholder="Cari No. Referensi / No. Rekening..."
                        class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-medium w-80">
                </div>
                @else
                <button wire:click="closeView" class="flex items-center space-x-2 bg-white text-slate-900 border border-slate-200 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Kembali ke Daftar</span>
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
                    Ditemukan <span class="text-slate-900">{{ $totalResults }}</span> Transaksi untuk pencarian "{{ $search }}"
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
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Tgl. Transaksi</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">No. Referensi</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Rekening</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-right">Nominal</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                             <td class="py-4 px-6">
                                <div class="flex items-center justify-center">
                                    <button wire:click="selectTrx({{ $item->id }})" class="p-2 bg-white text-slate-600 hover:bg-slate-50 rounded-xl shadow-sm border border-slate-200 transition-all hover:text-slate-900" title="Pilih Transaksi">
                                        <span class="material-symbols-outlined text-sm">receipt_long</span>
                                    </button>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-[11px] font-bold text-slate-900 uppercase tracking-tight">{{ $item->created_at->format('d/m/Y') }}</p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $item->created_at->format('H:i') }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-xs font-extrabold text-slate-800 tracking-wider font-mono">{{ $item->transaction_no }}</span>
                            </td>
                             <td class="py-4 px-6">
                                <p class="font-bold text-sm text-slate-900 uppercase leading-none mb-1">{{ $item->account->cif->name }}</p>
                                <p class="text-[10px] text-slate-500 font-bold tracking-widest">NIK: {{ $item->account->cif->nik }}</p>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <p class="font-black text-sm {{ $item->type == 'DEPOSIT' ? 'text-emerald-600' : 'text-rose-600' }} tracking-tight">Rp {{ number_format($item->amount, 2, ',', '.') }}</p>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-2 py-1 text-[9px] font-black uppercase tracking-widest rounded-lg bg-slate-100 text-slate-600">
                                    {{ $item->type }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-32 text-center text-slate-400">
                                <span class="material-symbols-outlined text-5xl mb-4 opacity-20">person_search</span>
                                <p class="text-sm font-bold">Lakukan pencarian no transaksi atau rekening...</p>
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
        
        <!-- PRINT PREVIEW VIEW -->
        <div class="max-w-4xl mx-auto space-y-8 animate-in slide-in-from-bottom-6 duration-700 no-print">
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 p-10 space-y-8">
                <div class="flex justify-between items-center border-b border-slate-100 pb-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-white">
                            <span class="material-symbols-outlined">print</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Pratinjau Cetak Slip</h3>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Siapkan printer thermal atau jet Ink</p>
                        </div>
                    </div>
                    <button onclick="window.print()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all active:scale-95 shadow-lg shadow-emerald-500/20 flex items-center space-x-3">
                        <span class="material-symbols-outlined text-sm">print_connect</span>
                        <span>Cetak Slip</span>
                    </button>
                </div>

                <!-- SLIP LAYOUT (For Screen Preview) -->
                <div class="max-w-md mx-auto bg-slate-50 border-2 border-dashed border-slate-200 p-8 rounded-[2rem] space-y-6 shadow-inner relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-slate-200 rotate-45 opacity-20"></div>
                    
                    <div class="text-center space-y-1">
                        <h4 class="text-sm font-black text-slate-900 uppercase">SIRARA CORE BANKING</h4>
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">{{ $selectedTrx->account->branch->name ?? 'CABANG UTAMA' }}</p>
                    </div>

                    <div class="border-y border-slate-200 py-4 space-y-3">
                        <div class="flex justify-between text-[10px] font-bold uppercase tracking-widest">
                            <span class="text-slate-400 leading-none">Nomor Reff</span>
                            <span class="text-slate-900 leading-none">{{ $selectedTrx->transaction_no }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] font-bold uppercase tracking-widest">
                            <span class="text-slate-400 leading-none">Tanggal</span>
                            <span class="text-slate-900 leading-none">{{ $selectedTrx->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1 text-left">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">Informasi Rekening</p>
                            <p class="text-[11px] font-black text-slate-900 uppercase leading-none">{{ $selectedTrx->account->cif->name }}</p>
                            <p class="text-[10px] font-bold text-slate-500 leading-none">{{ $selectedTrx->account->account_no }}</p>
                        </div>

                        <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-1 flex flex-col items-center">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">{{ $selectedTrx->type }}</p>
                            <p class="text-2xl font-black text-slate-900 tracking-tight leading-none pt-2">Rp {{ number_format($selectedTrx->amount, 2, ',', '.') }}</p>
                        </div>

                        <div class="space-y-1 text-left">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">Keterangan</p>
                            <p class="text-[10px] font-bold text-slate-600 leading-relaxed italic">"{{ $selectedTrx->description }}"</p>
                        </div>
                    </div>

                    <div class="pt-6 grid grid-cols-2 gap-4 text-center">
                        <div class="space-y-8">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">Petugas Teller</p>
                            <p class="text-[9px] font-black text-slate-900 uppercase leading-none underline">{{ auth()->user()->name }}</p>
                        </div>
                        <div class="space-y-8">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">Tanda Tangan</p>
                            <p class="text-[9px] font-black text-slate-900 uppercase leading-none italic">________________</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTUAL PRINTABLE CONTENT (Only visible on print) -->
        <div class="print-content hidden print:block text-slate-900" style="font-family: monospace;">
             <div class="text-center mb-4">
                 <h2 style="font-size: 16px; font-weight: bold; margin: 0;">SIRARA CORE BANKING</h2>
                 <p style="font-size: 10px; margin: 2px 0;">{{ $selectedTrx->account->branch->name ?? 'CABANG UTAMA' }}</p>
                 <p style="font-size: 10px; margin: 0;">-----------------------------------------</p>
             </div>
             
             <div style="font-size: 11px; margin-bottom: 10px;">
                 <table style="width: 100%;">
                     <tr><td>No Reff</td><td>: {{ $selectedTrx->transaction_no }}</td></tr>
                     <tr><td>Tanggal</td><td>: {{ $selectedTrx->created_at->format('d/m/Y H:i') }}</td></tr>
                     <tr><td>Rekening</td><td>: {{ $selectedTrx->account->account_no }}</td></tr>
                     <tr><td>Nama</td><td>: {{ $selectedTrx->account->cif->name }}</td></tr>
                 </table>
             </div>

             <div style="font-size: 11px; margin-bottom: 10px; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 10px 0; text-align: center;">
                 <p style="margin: 0; font-weight: bold;">BUKTI {{ $selectedTrx->type }}</p>
                 <h3 style="margin: 5px 0; font-size: 18px;">Rp {{ number_format($selectedTrx->amount, 2, ',', '.') }}</h3>
             </div>

             <div style="font-size: 10px; margin-bottom: 20px;">
                 <p>Keterangan: {{ $selectedTrx->description }}</p>
             </div>

             <div style="font-size: 10px;">
                 <table style="width: 100%; text-align: center;">
                     <tr>
                         <td>
                             TELLER<br><br><br><br>
                             ({{ auth()->user()->name }})
                         </td>
                         <td>
                             PENYETOR/PENARIK<br><br><br><br>
                             (..................)
                         </td>
                     </tr>
                 </table>
             </div>
             
             <p style="text-align: center; font-size: 9px; margin-top: 20px;">Simpanlah bukti transaksi ini sebagai bukti yang sah.</p>
        </div>
        @endif
    </div>
</div>

<style>
    @media print {
        header, .x-header, x-header, nav, #sidebar-nav, .no-print, x-slot\:actions, .actions {
            display: none !important;
        }
        .hidden.print\:block {
            display: block !important;
        }
        body { background: white !important; margin: 20px; }
    }
</style>
