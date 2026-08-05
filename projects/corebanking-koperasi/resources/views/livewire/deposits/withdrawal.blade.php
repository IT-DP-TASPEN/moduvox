<div class="p-0">
    <x-header title="Pencairan Simpanan Berjangka" subtitle="Proses penutupan rekening dan pengembalian dana simpanan berjangka" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            @if($viewMode === 'grid')
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
            @else
                <button wire:click="closeView" class="flex items-center space-x-2 bg-white text-slate-600 border border-slate-200 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Kembali ke Daftar</span>
                </button>
            @endif
        </x-slot>
    </x-header>

    <div class="p-10">
        @if($viewMode === 'grid')
            <!-- GRID VIEW: List of Active/Matured Accounts -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden animate-in fade-in duration-500">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50">
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center w-20">OPSI</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">No. Rekening</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Nama Anggota</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Kode Bilyet</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Pokok Simpanan Berjangka</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Jatuh Tempo</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($items as $item)
                                <tr wire:key="withdraw-row-{{ $item->id }}" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="py-4 px-6 text-center">
                                        <button wire:click="selectAccount({{ $item->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-rose-600 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto">
                                            <span class="material-symbols-outlined text-sm">payments</span>
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
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black bg-slate-100 text-slate-600 uppercase tracking-widest">{{ $item->bilyet?->kode_bilyet ?? 'NON-BILYET' }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <p class="font-black text-xs text-slate-900 tracking-tighter">Rp {{ number_format($item->amount, 2, ',', '.') }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black text-slate-900 tracking-widest">{{ $item->maturity_date->format('d/m/Y') }}</span>
                                            @php $days = now()->diffInDays($item->maturity_date, false); @endphp
                                            <span class="text-[9px] font-black uppercase tracking-tighter {{ $days <= 0 ? 'text-rose-500' : 'text-amber-500' }}">
                                                {{ $days <= 0 ? 'Sudah Jatuh Tempo' : "H - $days Hari" }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        @php
                                            $statusClass = match($item->status) {
                                                'ACTIVE' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                                'MATURED' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                                                'CLOSED' => 'bg-slate-100 text-slate-500 border-slate-200',
                                                default => 'bg-slate-50 text-slate-400 border-slate-100'
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded border {{ $statusClass }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-32 text-center text-slate-300">
                                        @if(!$search && !$filter_branch)
                                            <span class="material-symbols-outlined text-6xl mb-4 opacity-50">search</span>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Silakan cari nomor rekening atau nama anggota<br>untuk melakukan pencairan simpanan berjangka</p>
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
                    @if($items->hasPages())
                        <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30 font-bold text-xs uppercase tracking-widest">
                            {{ $items->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- FORM VIEW: Detailed Withdrawal Form -->
            <div class="max-w-5xl mx-auto space-y-8 animate-in zoom-in-95 duration-300">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="p-8 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center space-x-5">
                            <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                                <span class="material-symbols-outlined text-3xl">account_balance_wallet</span>
                            </div>
                            <div class="space-y-0.5">
                                <h4 class="text-base font-black text-slate-900 uppercase tracking-tight">{{ $account->account_no }}</h4>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $account->cif->name }} • Jatuh Tempo: {{ $account->maturity_date->format('d/m/Y') }}</p>
                                @if($account->maturity_date->isFuture())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black bg-rose-50 text-rose-600 uppercase tracking-tighter border border-rose-100">Belum Jatuh Tempo (Penalty Berlaku)</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black bg-emerald-50 text-emerald-600 uppercase tracking-tighter border border-emerald-100">Sudah Jatuh Tempo</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Saldo Pokok</p>
                            <p class="text-2xl font-black text-slate-900 tracking-tighter">Rp {{ number_format($account->amount, 2, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-10">
                        <!-- Panel Rincian -->
                        <div class="space-y-8">
                            <div class="border-b border-slate-200 pb-2 mb-6">
                                <p class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                                    <span class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">calculate</span>
                                    Kalkulasi Pencairan
                                </p>
                            </div>

                            <div class="space-y-6">
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Saldo Pokok Simpanan Berjangka</span>
                                    <span class="text-sm font-black text-slate-900">Rp {{ number_format($account->amount, 2, ',', '.') }}</span>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Biaya Penalti (Early Withdrawal) <span class="text-rose-500">*</span></label>
                                    <div class="relative" x-data="{ 
                                        display: '',
                                        raw: @entangle('penalty_amount').live,
                                        format(v) { return v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
                                        init() { this.display = this.format(this.raw || 0); }
                                    }">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</div>
                                        <input type="text" x-model="display"
                                            @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                            class="w-full pl-12 pr-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-rose-600"
                                            placeholder="0">
                                    </div>
                                    @error('penalty_amount') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="p-6 bg-emerald-50 rounded-2xl border border-emerald-100 flex items-center justify-between shadow-inner">
                                    <div class="space-y-0.5">
                                        <p class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">Net Terbayarkan</p>
                                    </div>
                                    <p class="text-xl font-black text-emerald-600 italic">Rp {{ number_format($account->amount - $penalty_amount, 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Metode -->
                        <div class="space-y-8">
                            <div class="border-b border-slate-200 pb-2 mb-6">
                                <p class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                                    <span class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">payments</span>
                                    Metode Pembayaran
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-4">
                                @foreach(['INTERNAL' => 'Ke Rekening Simpanan Terhubung', 'CASH' => 'Tarik Tunai / Cash', 'ABA' => 'Tarik ABA / Transfer Bank'] as $val => $label)
                                <label class="relative flex items-center p-5 rounded-2xl border-2 cursor-pointer transition-all group {{ $payout_channel === $val ? 'border-slate-900 bg-slate-50 shadow-sm' : 'border-slate-100 hover:border-slate-200 hover:bg-slate-50/50' }}">
                                    <input type="radio" wire:model.live="payout_channel" value="{{ $val }}" class="sr-only">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4 {{ $payout_channel === $val ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200' }}">
                                        <span class="material-symbols-outlined text-sm">{{ $val === 'CASH' ? 'payments' : ($val === 'ABA' ? 'account_balance' : 'savings') }}</span>
                                    </div>
                                    <div class="flex-grow">
                                        <p class="text-xs font-black text-slate-900 uppercase tracking-tight">{{ $label }}</p>
                                        @if($val === 'INTERNAL')
                                            <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest italic">
                                                {{ $account->savingAccount ? $account->savingAccount->account_no . ' - ' . $account->savingAccount->product?->name : 'Belum ada rekening simpanan tertaut.' }}
                                            </p>
                                        @endif
                                        @if($val === 'ABA')
                                            <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest italic">Gunakan ABA untuk pencairan ke rekening bank eksternal.</p>
                                        @endif
                                    </div>
                                    @if($payout_channel === $val)
                                        <span class="material-symbols-outlined text-slate-900 text-xl">check_circle</span>
                                    @endif
                                </label>
                                @endforeach
                            </div>
                            @error('target_saving_account_id') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror

                            @if($payout_channel === 'ABA' && $abaCoas->count() > 1)
                                <div class="space-y-2 animate-in fade-in slide-in-from-top-4 duration-300">
                                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Sub-Akun Bank (ABA)</label>
                                    <div class="relative">
                                        <select wire:model="bank_coa_id" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all appearance-none cursor-pointer">
                                            <option value="">-- Pilih Bank --</option>
                                            @foreach($abaCoas as $coa)
                                                <option value="{{ $coa->id }}">{{ $coa->name }} ({{ $coa->coa_code }})</option>
                                            @endforeach
                                        </select>
                                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-sm">expand_more</span>
                                    </div>
                                    @error('bank_coa_id') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                            @elseif($payout_channel === 'CASH' && $cashCoas->count() > 1)
                                <div class="space-y-2 animate-in fade-in slide-in-from-top-4 duration-300">
                                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Sub-Akun Kas</label>
                                    <div class="relative">
                                        <select wire:model="cash_coa_id" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all appearance-none cursor-pointer">
                                            <option value="">-- Pilih Kas --</option>
                                            @foreach($cashCoas as $coa)
                                                <option value="{{ $coa->id }}">{{ $coa->name }} ({{ $coa->coa_code }})</option>
                                            @endforeach
                                        </select>
                                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-sm">expand_more</span>
                                    </div>
                                    @error('cash_coa_id') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            <button wire:click="submit" 
                                class="w-full py-4 bg-slate-900 text-white hover:shadow-lg hover:shadow-slate-900/20 font-bold text-xs rounded-2xl transition-all active:scale-95 flex items-center justify-center space-x-2">
                                <span class="material-symbols-outlined text-sm">verified_user</span>
                                <span>Konfirmasi & Cairkan Simpanan Berjangka</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
