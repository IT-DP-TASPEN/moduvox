<div class="p-0">
    <x-header title="Transfer Antar Rekening" subtitle="Pencarian rekening pengirim dan pemindahan saldo" :user="$user" :role="$role">
        <x-slot:actions>
            <div class="flex items-center space-x-3">
                @if($viewMode === 'list')
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live.debounce.500ms="search" type="text" placeholder="Cari No. Rekening Pengirim..."
                        class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-medium w-72">
                </div>
                @else
                <button wire:click="closeView" class="flex items-center space-x-2 bg-white text-slate-900 border border-slate-200 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Ganti Pengirim</span>
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
        
        <!-- TABLE VIEW (Select Sender) -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/50">
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-center w-20">Aksi</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">No. Rekening</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Nama Peserta (PENGIRIM)</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-right">Saldo Efektif</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $item)
                        <tr wire:key="transfer-row-{{ $item->id }}" class="hover:bg-slate-50/50 transition-colors group">
                             <td class="py-4 px-6">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route('savings.transfer', ['account' => $item->account_no]) }}" wire:navigate
                                        class="p-2 bg-white text-slate-600 hover:bg-slate-50 rounded-xl shadow-sm border border-slate-200 transition-all hover:text-slate-900"
                                        title="Pilih Sebagai Pengirim">
                                        <span class="material-symbols-outlined text-sm">logout</span>
                                    </a>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-sm font-extrabold text-slate-800 tracking-wider font-mono">{{ $item->account_no }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-sm text-slate-900 uppercase">{{ $item->cif->name }}</p>
                                <p class="text-[10px] text-slate-500 font-bold tracking-wide">NIK: {{ $item->cif->nik }}</p>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <p class="font-black text-sm text-rose-600 tracking-tight">Rp {{ number_format($item->effective_balance, 2, ',', '.') }}</p>
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
        
        <!-- TRANSACTION FORM VIEW -->
        <div class="space-y-8 animate-in slide-in-from-bottom-6 duration-700">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- SENDER INFO (FromAccount) -->
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center text-rose-600">
                        <p class="text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">upload</span> 
                            Sumber Dana (PENGIRIM)
                        </p>
                        <span class="text-[9px] font-black bg-white border border-rose-100 px-2 py-0.5 rounded-lg">{{ $fromAccount->status }}</span>
                    </div>
                    <div class="p-8 space-y-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-900 uppercase">{{ $fromAccount->cif->name }}</h4>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $fromAccount->account_no }}</p>
                            </div>
                        </div>
                        <div class="p-4 bg-rose-50/50 rounded-2xl border border-rose-100/50 flex justify-between items-center">
                            <p class="text-[9px] font-bold uppercase tracking-widest text-rose-400 leading-none">Saldo Efektif</p>
                            <p class="text-lg font-black text-rose-600 tracking-tight">Rp {{ number_format($fromAccount->effective_balance, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- RECEIVER SEARCH & INFO (ToAccount) -->
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60">
                    <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center text-emerald-600">
                        <p class="text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">download</span> 
                            Tujuan Dana (PENERIMA)
                        </p>
                        @if($toAccount)
                        <span class="text-[9px] font-black bg-white border border-emerald-100 px-2 py-0.5 rounded-lg">{{ $toAccount->status }}</span>
                        @endif
                    </div>
                    <div class="p-8 space-y-6">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                            <input wire:model.live.debounce.500ms="to_account_no" type="text" 
                                class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-mono" 
                                placeholder="Cari Rekening Penerima (Min 3 Karakter)...">
                            
                            <!-- Search Results Dropdown -->
                            @if(!empty($toAccountResults))
                            <div class="absolute z-50 w-full mt-2 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-y-auto max-h-64 animate-in fade-in slide-in-from-top-2 duration-300">
                                @foreach($toAccountResults as $res)
                                <button wire:click="selectToAccount({{ $res->id }})" class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0 group">
                                    <div class="flex items-center space-x-4 text-left">
                                        <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-hover:text-emerald-500 group-hover:bg-emerald-50 transition-all shrink-0">
                                            <span class="material-symbols-outlined text-sm">person</span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-slate-900 uppercase">{{ $res->cif->name }}</p>
                                            <p class="text-[10px] text-slate-500 font-bold font-mono">{{ $res->account_no }} • {{ $res->product->name }}</p>
                                        </div>
                                    </div>
                                    <span class="material-symbols-outlined text-slate-400 opacity-0 group-hover:opacity-100 transition-all">chevron_right</span>
                                </button>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @error('to_account_no') <p class="text-[10px] text-rose-500 font-bold uppercase tracking-widest ml-1 italic">{{ $message }}</p> @enderror

                        @if($toAccount)
                        <div class="flex items-center space-x-4 animate-in fade-in duration-500">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-500">
                                <span class="material-symbols-outlined">person_check</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-900 uppercase leading-none">{{ $toAccount->cif->name }}</h4>
                                <p class="text-[11px] text-emerald-600 font-black tracking-widest mt-1">{{ $toAccount->account_no }}</p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase mt-1 tracking-widest">{{ $toAccount->product->name }}</p>
                            </div>
                        </div>
                        @else
                        <div class="py-6 text-center text-slate-300 italic">
                            <p class="text-[10px] font-bold uppercase tracking-widest">Silakan cari rekening penerima...</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TRANSFER DETAIL FORM -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 p-10 {{ !$toAccount ? 'opacity-30 pointer-events-none' : '' }}">
                <div class="space-y-8">
                    <div class="border-b border-slate-100 pb-2">
                        <p class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-slate-400">sync_alt</span> 
                            Input Detail Transfer
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nominal Transfer (Rp)</label>
                            <div class="relative" x-data="{ 
                                display: '',
                                raw: @entangle('amount'),
                                format(v) { return v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
                                init() { this.display = this.format(this.raw || 0); }
                            }">
                                <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</div>
                                <input type="text" 
                                    x-model="display"
                                    @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                    class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-black focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all text-indigo-600" placeholder="0">
                            </div>
                            @error('amount') <p class="text-[10px] text-rose-500 font-bold uppercase tracking-widest ml-2 italic">{{ $message }}</p> @enderror
                        </div>



                        <div class="space-y-4 md:col-span-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Keterangan Transfer</label>
                            <input wire:model="description" type="text" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all" placeholder="Contoh: Transfer bantuan biaya pendidikan">
                            @error('description') <p class="text-[10px] text-rose-500 font-bold uppercase tracking-widest ml-2 italic">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="pt-8 border-t border-slate-100 flex justify-between items-center">
                        <div class="flex items-center space-x-3 text-slate-400">
                            <span class="material-symbols-outlined text-sm">verified_user</span>
                            <p class="text-[9px] font-bold uppercase tracking-widest">Otorisasi Supervisor & Validasi Saldo Ganda</p>
                        </div>
                        <button wire:click="submit" class="bg-slate-900 hover:shadow-lg hover:shadow-slate-900/20 text-white px-12 py-4 rounded-xl font-bold text-xs uppercase tracking-widest transition-all active:scale-95 flex items-center space-x-3 group">
                            <span class="material-symbols-outlined text-sm">swap_vert</span>
                            <span>Konfirmasi & Kirim</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl flex items-center space-x-4">
                <span class="material-symbols-outlined text-slate-400 text-sm">info</span>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 leading-relaxed italic">
                    Transaksi ini merupakan pemindahan saldo internal. Setelah diposting, sistem akan mengirimkan notifikasi kepada kedua belah pihak.
                </p>
            </div>
        </div>
        @endif
    </div>
</div>
