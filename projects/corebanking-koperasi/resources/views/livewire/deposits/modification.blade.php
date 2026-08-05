<div class="p-0">
    <x-header title="Ubah Instruksi Simpanan Berjangka" subtitle="Modifikasi pengaturan perpanjangan otomatis (ARO) dan rekening penampung" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
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
                                <tr wire:key="modify-row-{{ $item->id }}" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="py-4 px-6 text-center">
                                        <button wire:click="selectAccount({{ $item->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-slate-900 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto">
                                            <span class="material-symbols-outlined text-sm">edit_note</span>
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
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Silakan cari nomor rekening atau nama anggota<br>untuk melakukan perubahan instruksi simpanan berjangka</p>
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
                        <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                            {{ $items->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- FORM VIEW: Modification Form -->
            <div class="max-w-5xl mx-auto space-y-8 animate-in zoom-in-95 duration-300">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="p-8 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center space-x-5">
                            <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                                <span class="material-symbols-outlined text-3xl">contract_edit</span>
                            </div>
                            <div class="space-y-0.5">
                                <h4 class="text-base font-black text-slate-900 uppercase tracking-tight">{{ $account->account_no }}</h4>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $account->cif->name }} • Instr. Saat Ini: <span class="text-slate-900">{{ $account->rollover_type }}</span></p>
                                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Jatuh Tempo: {{ $account->maturity_date->format('d M Y') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                             <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black bg-white text-slate-600 uppercase tracking-widest border border-slate-200 shadow-sm">Saldo Pokok: Rp {{ number_format($account->amount, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="p-10 space-y-12">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                            <!-- Panel Rollover (ARO) -->
                            <div class="space-y-6">
                                <div class="border-b border-slate-200 pb-2 mb-6">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                                        <span class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">published_with_changes</span>
                                        Instruksi Jatuh Tempo (ARO)
                                    </p>
                                </div>

                                <div class="space-y-4">
                                    @foreach(['NONE' => 'Non-ARO (Cair Saat Jatuh Tempo)', 'PRINCIPAL' => 'ARO Pokok (Bunga Cair)', 'PRINCIPAL_INTEREST' => 'ARO Pokok + Bunga'] as $val => $desc)
                                    <label class="relative flex items-center p-5 rounded-2xl border-2 cursor-pointer transition-all group {{ $rollover_type === $val ? 'border-slate-900 bg-slate-50 shadow-sm' : 'border-slate-100 hover:border-slate-200 hover:bg-slate-50/50' }}">
                                        <input type="radio" wire:model.live="rollover_type" value="{{ $val }}" class="sr-only">
                                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center mr-4 transition-all {{ $rollover_type === $val ? 'border-slate-900 bg-white' : 'border-slate-200' }}">
                                            @if($rollover_type === $val)
                                                <div class="w-2.5 h-2.5 bg-slate-900 rounded-full"></div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-slate-900 uppercase tracking-tight">{{ $val }}</p>
                                            <p class="text-[9px] text-slate-500 font-bold italic">{{ $desc }}</p>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Panel Rekening Payout -->
                            <div class="space-y-6">
                                <div class="border-b border-slate-200 pb-2 mb-6">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                                        <span class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">account_balance_wallet</span>
                                        Rekening Payout Bunga / Pokok
                                    </p>
                                </div>

                                <div class="space-y-4">
                                    <div class="relative">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Pilih Rekening Simpanan <span class="text-rose-500">*</span></label>
                                        <select wire:model="saving_account_id" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700 mt-2">
                                            <option value="">-- Cair Secara Tunai (Default) --</option>
                                            @foreach($savingAccounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->account_no }} - {{ $acc->product->name }} (Balance: Rp {{ number_format($acc->balance, 2, ',', '.') }})</option>
                                            @endforeach
                                        </select>
                                        @error('saving_account_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="p-5 bg-amber-50 rounded-2xl border border-amber-100 flex items-start space-x-3 mt-4">
                                        <span class="material-symbols-outlined text-amber-500 text-sm mt-0.5">info</span>
                                        <p class="text-[9px] text-amber-700 font-bold leading-relaxed uppercase tracking-widest italic leading-relaxed">Perubahan instruksi ini akan dikirimkan ke antrean persetujuan (Checker) terlebih dahulu sebelum berlaku pada periode jatuh tempo berikutnya.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-8 border-t border-slate-100 flex justify-end">
                            <button wire:click="submit" 
                                class="px-10 py-4 bg-slate-900 text-white hover:shadow-lg hover:shadow-slate-900/20 font-bold text-xs rounded-2xl transition-all active:scale-95 flex items-center justify-center">
                                <span class="material-symbols-outlined text-sm mr-2">save</span>
                                <span>Simpan Perubahan Instruksi</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
