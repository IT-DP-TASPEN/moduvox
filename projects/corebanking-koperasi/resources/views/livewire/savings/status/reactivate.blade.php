<div class="p-0">
    <x-header title="Reaktivasi Rekening" subtitle="Pencarian rekening non-aktif dan pengembalian status AKTIF" :user="$user" :role="$role">
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
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Status Asal</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-right">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                             <td class="py-4 px-6">
                                <div class="flex items-center justify-center">
                                    <button wire:click="selectAccount({{ $item->id }})" class="p-2 bg-white text-slate-600 hover:bg-slate-50 rounded-xl shadow-sm border border-slate-200 transition-all hover:text-slate-900" title="Pilih Rekening">
                                        <span class="material-symbols-outlined text-sm">history</span>
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
                            <td class="py-4 px-6">
                                <span class="px-3 py-1 text-[10px] font-black uppercase tracking-wider rounded-lg bg-rose-50 text-rose-500 border border-rose-100">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <p class="font-black text-sm text-slate-900 tracking-tight">Rp {{ number_format($item->balance, 2, ',', '.') }}</p>
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
        
        <!-- TRANSACTION FORM VIEW -->
        <div class="max-w-4xl mx-auto space-y-8 animate-in slide-in-from-bottom-6 duration-700">
            <!-- Account Info Card -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center text-indigo-600">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                            <span class="material-symbols-outlined">person</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ $selectedAccount->cif->name }}</h3>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $selectedAccount->account_no }} • {{ $selectedAccount->product->name }}</p>
                        </div>
                    </div>
                    <div class="px-4 py-2 bg-rose-50 border border-rose-100 rounded-xl text-center shadow-sm">
                        <p class="text-[8px] font-black text-rose-400 uppercase leading-none mb-1">Status Saat Ini</p>
                        <p class="text-[10px] font-black text-rose-600 uppercase">{{ $selectedAccount->status }}</p>
                    </div>
                </div>
                
                <div class="p-10 grid grid-cols-1 md:grid-cols-2 gap-10 text-left">
                    <div class="space-y-6">
                        <div class="border-b border-slate-100 pb-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Detail Anggota</p>
                        </div>
                        <div class="grid grid-cols-1 gap-4">
                            <div class="space-y-1">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">NIK</p>
                                <p class="text-xs font-bold text-slate-900">{{ $selectedAccount->cif->nik }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Alamat</p>
                                <p class="text-xs font-bold text-slate-900 leading-relaxed">{{ $selectedAccount->cif->address }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="border-b border-slate-100 pb-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Informasi Saldo</p>
                        </div>
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-4">
                            <div class="flex justify-between items-center text-slate-900">
                                <p class="text-[10px] font-black uppercase tracking-widest">Saldo Akhir</p>
                                <p class="text-lg font-black tracking-tight">Rp {{ number_format($selectedAccount->balance, 2, ',', '.') }}</p>
                            </div>
                            <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Blokir</p>
                                <p class="text-[10px] font-black text-rose-500">Rp {{ number_format($selectedAccount->blocked_balance ?? 0, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 p-10 mt-10">
                <div class="space-y-8">
                    <div class="border-b border-slate-100 pb-2 text-left">
                        <p class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-slate-400">history</span> 
                            Form Pengajuan Reaktivasi Rekening
                        </p>
                    </div>

                    <div class="space-y-6 text-left">
                        <div class="space-y-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Alasan Reaktivasi</label>
                            <textarea wire:model="reason" rows="3" class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-medium" placeholder="Jelaskan mengapa rekening ini perlu diaktifkan kembali secara detail..."></textarea>
                            @error('reason') <p class="text-[10px] text-rose-500 font-bold uppercase tracking-widest ml-1 italic">{{ $message }}</p> @enderror
                        </div>

                        <div class="p-6 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-start space-x-4">
                            <span class="material-symbols-outlined text-emerald-500 mt-0.5">verified</span>
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest leading-none">Konfirmasi Pemulihan</p>
                                <p class="text-[10px] font-medium text-emerald-700 leading-relaxed italic opacity-70">
                                    Rekening yang direaktivasi akan kembali ke status AKTIF dan dapat digunakan untuk transaksi operasional seperti biasa.
                                </p>
                            </div>
                        </div>

                        <div class="pt-8 border-t border-slate-100 flex justify-between items-center">
                            <div class="flex items-center space-x-3 text-slate-400">
                                <span class="material-symbols-outlined text-sm">security</span>
                                <p class="text-[9px] font-bold uppercase tracking-widest leading-none">Otorisasi Level Supervisor</p>
                            </div>
                            <button wire:click="submit" class="bg-slate-900 hover:shadow-lg hover:shadow-slate-900/20 text-white px-12 py-4 rounded-xl font-bold text-xs uppercase tracking-widest transition-all active:scale-95 flex items-center space-x-3 group shadow-xl">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                <span>Proses Reaktivasi</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
