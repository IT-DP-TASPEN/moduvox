<div class="p-0">
    <x-header title="Pembayaran Angsuran Pinjaman" subtitle="Pencarian pinjaman aktif, tagihan berjalan, dan input pembayaran manual" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <select wire:model.live="statusFilter" class="pl-3 pr-10 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 appearance-none shadow-sm">
                        <option value="">Semua Status</option>
                        <option value="ACTIVE">AKTIF</option>
                        <option value="NPL">NPL</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                        <span class="material-symbols-outlined text-sm">expand_more</span>
                    </div>
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live.debounce.500ms="search" type="text" placeholder="No Rekening atau Nama..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 w-64 shadow-sm">
                </div>
            </div>
        </x-slot>
    </x-header>

    <div class="p-10">
        @if (session()->has('success'))
            <div class="mb-8 bg-emerald-50 text-emerald-700 p-6 border border-emerald-100 rounded-[2rem] flex items-center gap-4 animate-in fade-in duration-500">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600">
                    <span class="material-symbols-outlined text-xl">check_circle</span>
                </div>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-8 bg-rose-50 text-rose-700 p-6 border border-rose-100 rounded-[2rem] flex items-center gap-4 animate-in fade-in duration-500">
                <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600">
                    <span class="material-symbols-outlined text-xl">error</span>
                </div>
                <span class="font-bold text-sm">{{ session('error') }}</span>
            </div>
        @endif

        @if($viewMode === 'grid')
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden animate-in fade-in duration-500">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50">
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center w-20">OPSI</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">No. Rekening</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Nama Anggota</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Produk</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Plafon</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Angsuran</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Outstanding</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Cycle Due</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($loans as $loan)
                                @php
                                    $installment = $loan->schedules->first();
                                    $installmentAmount = $installment
                                        ? $installment->principal_amount + $installment->interest_amount + $installment->penalty_amount
                                        : 0;
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="py-4 px-6 text-center">
                                        <button wire:click="viewAccount({{ $loan->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-slate-900 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto">
                                            <span class="material-symbols-outlined text-sm">visibility</span>
                                        </button>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-slate-900 tracking-tight">{{ $loan->account_no }}</span>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $loan->cif->cif_no }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-black text-xs text-slate-900 uppercase leading-none mb-1">{{ $loan->cif->name }}</p>
                                        <p class="text-[9px] text-slate-400 font-bold tracking-widest uppercase">{{ $loan->pk_number }}</p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black bg-slate-100 text-slate-600 uppercase tracking-widest">{{ $loan->product->name }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <p class="font-black text-xs text-slate-900 tracking-tighter">Rp {{ number_format($loan->principal_amount, 2, ',', '.') }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <p class="font-black text-xs text-indigo-600 tracking-tighter">Rp {{ number_format($installmentAmount, 2, ',', '.') }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <p class="font-black text-xs text-rose-600 tracking-tighter">Rp {{ number_format($loan->outstanding_total, 2, ',', '.') }}</p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest italic">P: {{ number_format($loan->outstanding_principal, 2, ',', '.') }} | B: {{ number_format($loan->outstanding_interest, 2, ',', '.') }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black text-slate-900 tracking-widest">Tgl {{ $loan->due_date_cycle }}</span>
                                            <span class="text-[9px] font-black uppercase tracking-tighter text-emerald-600">Aktif Ditagih</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded border bg-emerald-50 text-emerald-600 border-emerald-100">
                                            {{ $loan->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-32 text-center text-slate-300">
                                        @if(!filled(trim($search)))
                                            <span class="material-symbols-outlined text-6xl mb-4 opacity-50">search</span>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Silakan cari rekening, PK, atau nama anggota<br>untuk menampilkan data pembayaran manual</p>
                                        @else
                                            <span class="material-symbols-outlined text-6xl mb-4 opacity-50">drafts</span>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Data tidak ditemukan untuk pencarian saat ini</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($loans->hasPages())
                    <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                        {{ $loans->links(data: ['scrollTo' => false]) }}
                    </div>
                @endif
            </div>
        @else
            @if($selectedAccount)
                <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
                    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col mb-10">
                        <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                            <div class="flex items-center space-x-4">
                                <button wire:click="closeView" class="p-2 bg-white hover:bg-slate-200 rounded-xl transition-all shadow-sm border border-slate-200">
                                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                                </button>
                                <div>
                                    <h2 class="font-extrabold text-sm text-slate-900 tracking-wider uppercase">
                                        Data Rekening: {{ $selectedAccount->account_no }}
                                    </h2>
                                    <p class="text-[10px] uppercase font-bold text-slate-500 tracking-widest mt-1">
                                        Produk: <span class="text-indigo-600">{{ $selectedAccount->product->name }}</span>
                                        | Status: <span class="text-emerald-500">{{ $selectedAccount->status }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-8">
                        <div class="col-span-12 lg:col-span-7 space-y-6">
                            <div class="p-10 bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 overflow-hidden">
                                <form class="flex flex-col">
                                    <fieldset disabled class="m-0 p-0 border-0">
                                        <div class="space-y-12">
                                            <div>
                                                <div class="border-b border-slate-200 pb-2 mb-6 text-slate-900 flex items-center justify-between">
                                                    <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-sm">account_balance</span>
                                                        Informasi Rekening & Produk
                                                    </p>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. Rekening</label>
                                                        <input type="text" value="{{ $selectedAccount->account_no }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Produk Kredit</label>
                                                        <input type="text" value="{{ $selectedAccount->product->name }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-indigo-600 uppercase">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Outstanding Saat Ini</label>
                                                        <div class="relative">
                                                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-sm font-bold text-white/50">Rp</span>
                                                            <input type="text" value="{{ number_format($selectedAccount->outstanding_total, 2, ',', '.') }}" class="w-full pl-12 pr-5 py-3.5 bg-slate-900 border border-slate-900 rounded-2xl font-black text-sm text-white shadow-lg shadow-slate-900/20">
                                                        </div>
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. Perjanjian Kredit</label>
                                                        <input type="text" value="{{ $selectedAccount->pk_number }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-amber-600 uppercase">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Realisasi</label>
                                                        <input type="text" value="{{ optional($selectedAccount->disbursement_date)->format('d/m/Y') ?? '-' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status Rekening</label>
                                                        <input type="text" value="{{ $selectedAccount->status }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm uppercase text-emerald-500">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Plafon</label>
                                                        <input type="text" value="Rp {{ number_format($selectedAccount->principal_amount, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-slate-900">
                                                    </div>
                                                    <div class="space-y-2">
                                                        @php
                                                            $detailInstallment = $selectedAccount->schedules->first();
                                                            $detailInstallmentAmount = $detailInstallment
                                                                ? $detailInstallment->principal_amount + $detailInstallment->interest_amount + $detailInstallment->penalty_amount
                                                                : 0;
                                                        @endphp
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Jumlah Angsuran</label>
                                                        <input type="text" value="Rp {{ number_format($detailInstallmentAmount, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-indigo-600">
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <div class="border-b border-slate-200 pb-2 mb-6 text-indigo-600 flex items-center justify-between">
                                                    <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-sm">person</span>
                                                        Profil Pemilik / CIF Anggota
                                                    </p>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Anggota</label>
                                                        <input type="text" readonly value="{{ $selectedAccount->cif->name }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 uppercase">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. CIF</label>
                                                        <input type="text" readonly value="{{ $selectedAccount->cif->cif_no }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-indigo-600">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Cabang CIF</label>
                                                        <input type="text" readonly value="{{ $selectedAccount->cif->branch->name ?? '-' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status CIF</label>
                                                        <input type="text" readonly value="{{ $selectedAccount->cif->status }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm {{ $selectedAccount->cif->status === 'ACTIVE' ? 'text-emerald-600' : 'text-rose-600' }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <div class="border-b border-slate-200 pb-2 mb-6 text-emerald-600 flex items-center justify-between">
                                                    <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-sm">savings</span>
                                                        Detail Tagihan & Rekening Terkait
                                                    </p>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Sisa Pokok</label>
                                                        <input type="text" value="Rp {{ number_format($selectedAccount->outstanding_principal, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-slate-900">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Sisa Bunga</label>
                                                        <input type="text" value="Rp {{ number_format($selectedAccount->outstanding_interest, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-rose-600">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Denda / Penalti</label>
                                                        <input type="text" value="Rp {{ number_format($selectedAccount->outstanding_penalty, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-amber-600">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Cycle Due Date</label>
                                                        <input type="text" value="Tanggal {{ $selectedAccount->due_date_cycle }}" class="w-full px-5 py-3.5 bg-emerald-50 border border-emerald-100 rounded-2xl font-black text-sm text-emerald-600 uppercase tracking-tighter">
                                                    </div>
                                                    <div class="col-span-2 space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Rekening Auto-Debit</label>
                                                        <input type="text" value="{{ $selectedAccount->savingAccount?->account_no ?? 'TIDAK TERHUBUNG' }} - {{ $selectedAccount->savingAccount?->product->name ?? 'TANPA REKENING TABUNGAN' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm uppercase">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>

                            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-900">Antrean Tagihan (Unpaid/Partial)</h4>
                                </div>
                                <div class="overflow-x-auto custom-scrollbar">
                                    <table class="w-full text-left border-collapse">
                                        <thead class="bg-white border-b border-slate-100">
                                            <tr>
                                                <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Ke</th>
                                                <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Tgl Due</th>
                                                <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Invoiced</th>
                                                <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Dibayar</th>
                                                <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Sisa Tagih</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            @foreach($selectedAccount->schedules as $sched)
                                                @php
                                                    $invoiced = $sched->principal_amount + $sched->interest_amount + $sched->penalty_amount;
                                                    $paid = $sched->principal_paid + $sched->interest_paid + $sched->penalty_paid;
                                                    $due = $invoiced - $paid;
                                                @endphp
                                                <tr class="hover:bg-slate-50/50 transition-colors">
                                                    <td class="py-4 px-6 text-[10px] font-black text-slate-400 text-center">{{ $sched->installment_number }}</td>
                                                    <td class="py-4 px-6 text-[10px] font-bold text-slate-900">{{ $sched->due_date->format('d/m/Y') }}</td>
                                                    <td class="py-4 px-6 text-[10px] font-bold text-slate-600 text-right">Rp {{ number_format($invoiced, 2, ',', '.') }}</td>
                                                    <td class="py-4 px-6 text-[10px] font-bold text-emerald-600 text-right">Rp {{ number_format($paid, 2, ',', '.') }}</td>
                                                    <td class="py-4 px-6 text-[10px] font-black text-rose-600 text-right">Rp {{ number_format($due, 2, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-12 lg:col-span-5 space-y-6">
                            <div class="p-8 bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60">
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 border-b border-slate-100 pb-3">Input Pembayaran Manual</h4>

                                <div class="space-y-6">
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nominal Setoran <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</span>
                                            <input type="text" wire:model.live.debounce.500ms="payment_amount_display" inputmode="decimal" class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-black text-xl text-slate-900" placeholder="0,00">
                                        </div>
                                        @error('payment_amount') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Jalur Transaksi (Channel)</label>
                                        <div class="relative">
                                            <select wire:model.live="channel" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all appearance-none cursor-pointer">
                                                <option value="INTERNAL">Tabungan Internal</option>
                                                <option value="CASH">Tunai (Kas)</option>
                                                <option value="ABA">Antar Bank Aktiva (ABA)</option>
                                            </select>
                                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-sm">expand_more</span>
                                        </div>
                                        @error('channel') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>
                                    <!-- Dropdown Pemilihan Sub-Akun COA (Kas / ABA) -->
                                    @if($channel === 'ABA' && $abaCoas->count() > 1)
                                    <div class="space-y-2 animate-in fade-in slide-in-from-top-4 duration-300">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Sub-Akun Bank (ABA)</label>
                                        <div class="relative">
                                            <select wire:model.live="bank_coa_id" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all appearance-none cursor-pointer">
                                                <option value="">-- Pilih Bank --</option>
                                                @foreach($abaCoas as $coa)
                                                    <option value="{{ $coa->id }}">{{ $coa->name }} ({{ $coa->coa_code }})</option>
                                                @endforeach
                                            </select>
                                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-sm">expand_more</span>
                                        </div>
                                        @error('bank_coa_id') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>
                                    @elseif($channel === 'CASH' && $cashCoas->count() > 1)
                                    <div class="space-y-2 animate-in fade-in slide-in-from-top-4 duration-300">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Sub-Akun Kas</label>
                                        <div class="relative">
                                            <select wire:model.live="cash_coa_id" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all appearance-none cursor-pointer">
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

                                    @php($sourceStatus = $this->repaymentSourceStatus())
                                    @if(!empty($sourceStatus))
                                        <div class="p-4 rounded-2xl border {{ $sourceStatus['is_sufficient'] ? 'bg-emerald-50 border-emerald-100' : 'bg-rose-50 border-rose-100' }}">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-[10px] font-black uppercase tracking-widest {{ $sourceStatus['is_sufficient'] ? 'text-emerald-700' : 'text-rose-700' }}">
                                                        {{ $sourceStatus['label'] }}
                                                    </p>
                                                    <p class="text-xs font-black text-slate-900 mt-1 leading-snug">{{ $sourceStatus['account'] }}</p>
                                                    <p class="text-[10px] font-bold text-slate-500 mt-1 uppercase tracking-wider">
                                                        Tersedia: Rp {{ number_format($sourceStatus['balance'], 2, ',', '.') }}
                                                    </p>
                                                </div>
                                                <span class="shrink-0 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $sourceStatus['is_sufficient'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                    {{ $sourceStatus['status'] }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
                                        <div class="flex items-start gap-3">
                                            <span class="material-symbols-outlined text-amber-500 text-lg">info</span>
                                            <p class="text-[10px] font-bold text-amber-700 leading-relaxed uppercase tracking-wider">
                                                Pembayaran diprioritaskan secara sistematis:
                                                <span class="text-slate-900">Bunga lalu pokok lalu penalti</span>
                                                berdasarkan urutan tagihan tertua yang belum lunas.
                                            </p>
                                        </div>
                                    </div>

                                    <button wire:click="processRepayment" class="w-full bg-slate-900 text-white font-black py-4 rounded-2xl text-xs uppercase tracking-widest hover:shadow-lg hover:shadow-slate-900/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-sm">check_circle</span>
                                        Eksekusi Pembayaran Manual
                                    </button>
                                </div>
                            </div>

                            <div class="p-8 bg-slate-900 rounded-[2.5rem] shadow-xl shadow-slate-900/10 text-white">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/40 mb-1">Total Outstanding</p>
                                <h3 class="text-3xl font-black tracking-tight mb-8">Rp {{ number_format($selectedAccount->outstanding_total, 2, ',', '.') }}</h3>

                                <div class="space-y-4">
                                    <div class="flex justify-between items-center bg-white/5 p-4 rounded-2xl border border-white/5">
                                        <span class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Sisa Pokok</span>
                                        <span class="text-sm font-black text-white">Rp {{ number_format($selectedAccount->outstanding_principal, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center bg-white/5 p-4 rounded-2xl border border-white/5">
                                        <span class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Sisa Bunga</span>
                                        <span class="text-sm font-black text-rose-400">Rp {{ number_format($selectedAccount->outstanding_interest, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center bg-white/5 p-4 rounded-2xl border border-white/5">
                                        <span class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Denda / Penalti</span>
                                        <span class="text-sm font-black text-amber-400">Rp {{ number_format($selectedAccount->outstanding_penalty, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
