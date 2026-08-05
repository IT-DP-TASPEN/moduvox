<div class="p-0">
    <x-header title="Buka Blokir Saldo" subtitle="Pencarian rekening terblokir dan pengajuan pembukaan dana" :user="$user" :role="$role">
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
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-right text-rose-600">Terblokir Saat Ini</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-right">Total Saldo</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $item)
                        <tr wire:key="unblock-row-{{ $item->id }}" class="hover:bg-slate-50/50 transition-colors group">
                             <td class="py-4 px-6">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route('savings.unblock', ['account' => $item->account_no]) }}" wire:navigate
                                        class="p-2 bg-white text-slate-600 hover:bg-slate-50 rounded-xl shadow-sm border border-slate-200 transition-all hover:text-slate-900"
                                        title="Buka Blokir">
                                        <span class="material-symbols-outlined text-sm">lock_open</span>
                                    </a>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-sm font-extrabold text-slate-800 tracking-wider font-mono">{{ $item->account_no }}</span>
                            </td>
                             <td class="py-4 px-6">
                                <p class="font-bold text-sm text-slate-900 uppercase leading-none mb-1">{{ $item->cif->name }}</p>
                                <p class="text-[10px] text-slate-500 font-bold tracking-widest">NIK: {{ $item->cif->nik }}</p>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <p class="font-black text-sm text-rose-600 tracking-tight">Rp {{ number_format($item->blocked_balance ?? 0, 2, ',', '.') }}</p>
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
        
        <!-- TRANSACTION FORM VIEW -->
        <div class="space-y-8 animate-in slide-in-from-bottom-6 duration-700">
            <!-- Integrated Info Card (CIF + Account) -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center text-emerald-600">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                            <span class="material-symbols-outlined">person</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ $selectedAccount->cif->name }}</h3>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $selectedAccount->account_no }} • {{ $selectedAccount->product->name }}</p>
                        </div>
                    </div>
                    <div class="px-4 py-2 bg-white border border-emerald-100 rounded-xl text-center shadow-sm">
                        <p class="text-[8px] font-black text-emerald-400 uppercase leading-none mb-1">Status Rekening</p>
                        <p class="text-[10px] font-black text-emerald-600 uppercase">{{ $selectedAccount->status }}</p>
                    </div>
                </div>
                
                <div class="p-10 grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-6">
                        <div class="border-b border-slate-100 pb-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Informasi Anggota</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">NIK</p>
                                <p class="text-xs font-bold text-slate-900">{{ $selectedAccount->cif->nik }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Telepon</p>
                                <p class="text-xs font-bold text-slate-900">{{ $selectedAccount->cif->phone ?? '-' }}</p>
                            </div>
                            <div class="space-y-1 col-span-2">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Alamat</p>
                                <p class="text-xs font-bold text-slate-900 leading-relaxed">{{ $selectedAccount->cif->address }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="border-b border-slate-100 pb-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Saldo Saat Ini</p>
                        </div>
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-4">
                            <div class="flex justify-between items-center text-rose-600">
                                <p class="text-[10px] font-black uppercase tracking-widest">Saldo TERBLOKIR</p>
                                <p class="text-lg font-black tracking-tight">Rp {{ number_format($selectedAccount->blocked_balance ?? 0, 2, ',', '.') }}</p>
                            </div>
                            <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Saldo Akhir</p>
                                <p class="text-sm font-black text-slate-900">Rp {{ number_format($selectedAccount->balance, 2, ',', '.') }}</p>
                            </div>
                            <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Saldo Efektif</p>
                                <p class="text-sm font-black text-slate-900">Rp {{ number_format($selectedAccount->effective_balance, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 p-10 mt-10">
                <div class="space-y-8">
                    <div class="border-b border-slate-100 pb-2">
                        <p class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-slate-400">lock_open</span> 
                            Form Pengajuan Buka Blokir
                        </p>
                    </div>

                    <div class="space-y-6">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pilih Blokir yang Akan Dibuka</label>
                        
                        <div class="grid grid-cols-1 gap-4">
                            @forelse($activeBlocks as $block)
                            <label class="relative group cursor-pointer">
                                <input type="radio" wire:model.live="selectedBlockId" value="{{ $block->id }}" class="peer sr-only">
                                <div class="p-6 bg-white border-2 border-slate-100 rounded-3xl transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50/30 group-hover:border-slate-200">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 peer-checked:text-emerald-500 peer-checked:bg-white transition-all">
                                                <span class="material-symbols-outlined text-sm">lock</span>
                                            </div>
                                            <div>
                                                <p class="text-xs font-black text-slate-900 uppercase">REF: {{ $block->reference_no ?? '-' }}</p>
                                                <p class="text-[10px] text-slate-500 font-bold tracking-widest leading-none mt-1">{{ $block->created_at->format('d M Y') }} • {{ $block->reason }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-black text-slate-900 tracking-tight">Rp {{ number_format($block->amount, 2, ',', '.') }}</p>
                                            <div class="flex items-center justify-end space-x-1 mt-1">
                                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                                <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">Active</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Checkmark Icon -->
                                    <div class="absolute -top-2 -right-2 w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center scale-0 peer-checked:scale-100 transition-all shadow-lg shadow-emerald-500/20">
                                        <span class="material-symbols-outlined text-[14px]">check</span>
                                    </div>
                                </div>
                            </label>
                            @empty
                            <div class="py-12 text-center bg-slate-50 border border-dashed border-slate-200 rounded-3xl">
                                <span class="material-symbols-outlined text-slate-300 text-4xl mb-2">history</span>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tidak ada blokir aktif ditemukan</p>
                            </div>
                            @endforelse
                        </div>
                        @error('selectedBlockId') <p class="text-[10px] text-rose-500 font-bold uppercase tracking-widest ml-2 italic">{{ $message }}</p> @enderror

                        <div class="pt-6 space-y-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Keterangan / Alasan Pembukaan</label>
                            <input wire:model="description" type="text" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-medium" placeholder="Contoh: Pinjaman sudah lunas / Salah blokir">
                            @error('description') <p class="text-[10px] text-rose-500 font-bold uppercase tracking-widest ml-2 italic">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="pt-8 border-t border-slate-100 flex justify-between items-center">
                        <div class="flex items-center space-x-3 text-slate-400">
                            <span class="material-symbols-outlined text-sm">verified_user</span>
                            <p class="text-[9px] font-bold uppercase tracking-widest">Memerlukan Otorisasi Supervisor</p>
                        </div>
                        <button wire:click="submit" class="bg-slate-900 hover:shadow-lg hover:shadow-slate-900/20 text-white px-12 py-4 rounded-xl font-bold text-xs uppercase tracking-widest transition-all active:scale-95 flex items-center space-x-3 group shadow-xl">
                            <span class="material-symbols-outlined text-sm">lock_open</span>
                            <span>Ajukan Buka Blokir</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl flex items-center space-x-4 text-center">
                <span class="material-symbols-outlined text-slate-400 text-sm">info</span>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 leading-relaxed italic mx-auto">
                    Membuka blokir akan mengembalikan saldo ke Saldo Efektif sehingga dapat digunakan kembali oleh anggota.
                </p>
            </div>
        </div>
        @endif
    </div>
</div>
