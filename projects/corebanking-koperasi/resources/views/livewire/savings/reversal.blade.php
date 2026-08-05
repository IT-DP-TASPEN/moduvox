<div class="p-0">
    <x-header title="Koreksi Transaksi (Reversal)" subtitle="Pencarian mutasi dan pengajuan pembatalan transaksi" :user="$user" :role="$role">
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
        @if (session()->has('success'))
        <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-[2rem] border border-emerald-100 flex items-center mb-10 animate-fade-in shadow-sm">
            <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center mr-4 shrink-0">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            </div>
            <p class="font-bold text-sm">{{ session('success') }}</p>
        </div>
        @endif

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/50">
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-center w-20">Aksi</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Tgl. Transaksi</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">No. Referensi</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">No. Rekening</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Jenis</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                             <td class="py-4 px-6">
                                <div class="flex items-center justify-center">
                                    <button wire:click="selectTrx({{ $trx->id }})" class="p-2 bg-white text-slate-600 hover:bg-slate-50 rounded-xl shadow-sm border border-slate-200 transition-all hover:text-slate-900" title="Koreksi Transaksi">
                                        <span class="material-symbols-outlined text-sm">history_toggle_off</span>
                                    </button>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-[11px] font-bold text-slate-900 uppercase tracking-tight">{{ $trx->created_at->format('d/m/Y') }}</p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $trx->created_at->format('H:i') }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-xs font-extrabold text-slate-800 tracking-wider font-mono">{{ $trx->transaction_no }}</span>
                            </td>
                             <td class="py-4 px-6">
                                <p class="font-bold text-sm text-slate-900 uppercase leading-none mb-1">{{ $trx->account->cif->name }}</p>
                                <p class="text-[10px] text-slate-500 font-bold tracking-widest">NIK: {{ $trx->account->cif->nik }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2 py-1 text-[9px] font-black uppercase tracking-widest rounded-lg {{ $trx->type == 'DEPOSIT' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $trx->type }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <p class="font-black text-sm {{ $trx->type == 'DEPOSIT' ? 'text-emerald-600' : 'text-rose-600' }} tracking-tight">Rp {{ number_format($trx->amount, 2, ',', '.') }}</p>
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
            @if(!empty($this->search) && $transactions->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $transactions->links() }}
            </div>
            @endif
        </div>
        
        @else
        
        <!-- REVERSAL FORM VIEW -->
        <div class="space-y-8 animate-in slide-in-from-bottom-6 duration-700">
            <!-- Transaction Detail Info Card -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                            <span class="material-symbols-outlined">receipt_long</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ $selectedTrx->transaction_no }}</h3>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $selectedTrx->created_at->format('d M Y • H:i') }}</p>
                        </div>
                    </div>
                    <div class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-center shadow-sm">
                        <p class="text-[8px] font-black text-slate-400 uppercase leading-none mb-1">Status Saldo</p>
                        <p class="text-[10px] font-black {{ $selectedTrx->type == 'DEPOSIT' ? 'text-emerald-600' : 'text-rose-600' }} uppercase">{{ $selectedTrx->type }}</p>
                    </div>
                </div>
                
                <div class="p-10 grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-6">
                        <div class="border-b border-slate-100 pb-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Informasi Rekening</p>
                        </div>
                        <div class="grid grid-cols-1 gap-4">
                            <div class="space-y-1">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Nama Peserta</p>
                                <p class="text-xs font-bold text-slate-900">{{ $selectedTrx->account->cif->name }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Nomor Rekening</p>
                                <p class="text-xs font-bold text-slate-900 font-mono">{{ $selectedTrx->account->account_no }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Produk Simpanan</p>
                                <p class="text-xs font-bold text-slate-900 uppercase">{{ $selectedTrx->account->product->name }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="border-b border-slate-100 pb-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Detail Transaksi</p>
                        </div>
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-4">
                            <div class="flex justify-between items-center">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Nominal Ori</p>
                                <p class="text-sm font-black text-slate-900">Rp {{ number_format($selectedTrx->amount, 2, ',', '.') }}</p>
                            </div>
                            <div class="flex justify-between items-start">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Keterangan Ori</p>
                                <p class="text-[10px] font-bold text-slate-900 text-right max-w-[150px] italic">"{{ $selectedTrx->description }}"</p>
                            </div>
                            <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                                <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Saldo Rekening Saat Ini</p>
                                <p class="text-lg font-black text-indigo-600">Rp {{ number_format($selectedTrx->account->balance, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 p-10 mt-10">
                <div class="space-y-8">
                    <div class="border-b border-slate-100 pb-2">
                        <p class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-slate-400">history_toggle_off</span> 
                            Form Pengajuan Koreksi (Reversal)
                        </p>
                    </div>

                    <div class="space-y-6">
                        <div class="space-y-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Alasan Koreksi / Reversal</label>
                            <textarea wire:model="reason" rows="3" class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-medium" placeholder="Jelaskan alasan pembatalan / koreksi transaksi ini secara detail..."></textarea>
                            @error('reason') <p class="text-[10px] text-rose-500 font-bold uppercase tracking-widest ml-1 italic">{{ $message }}</p> @enderror
                        </div>

                        <div class="p-6 bg-rose-50 border border-rose-100 rounded-2xl flex items-start space-x-4">
                            <span class="material-symbols-outlined text-rose-500 mt-0.5">warning</span>
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest">PENTING: Efek Reversal</p>
                                <p class="text-[10px] font-bold text-rose-500 leading-relaxed uppercase opacity-70">
                                    Reversal akan membalikkan efek mutasi pada saldo rekening. Pastikan saldo mencukupi jika me-reversal transaksi DEPOSIT.
                                </p>
                            </div>
                        </div>

                        <div class="pt-8 border-t border-slate-100 flex justify-between items-center">
                            <div class="flex items-center space-x-3 text-slate-400">
                                <span class="material-symbols-outlined text-sm">shield_person</span>
                                <p class="text-[9px] font-bold uppercase tracking-widest leading-none">Mekanisme Otorisasi Supervisor Ganda</p>
                            </div>
                            <button wire:click="submitReversal" class="bg-slate-900 hover:shadow-lg hover:shadow-slate-900/20 text-white px-12 py-4 rounded-xl font-bold text-xs uppercase tracking-widest transition-all active:scale-95 flex items-center space-x-3 group shadow-xl">
                                <span class="material-symbols-outlined text-sm">history_toggle_off</span>
                                <span>Ajukan Koreksi</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
