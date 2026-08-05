<div class="p-0">
    <x-header title="Inquiry Pencairan Pinjaman" subtitle="Pencarian antrean realisasi, status operasional, dan reversal fasilitas pinjaman" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <select wire:model.live="statusFilter" class="pl-3 pr-10 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 appearance-none shadow-sm">
                        <option value="">Semua Status</option>
                        <option value="APPROVED">SIAP CAIR</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                        <span class="material-symbols-outlined text-sm">expand_more</span>
                    </div>
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live.debounce.500ms="search" type="text" placeholder="No Rekening, PK atau Nama..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 w-64 shadow-sm">
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
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">No. PK</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Plafon Cair</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Tgl Realisasi</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($loans as $loan)
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="py-4 px-6 text-center">
                                        <button wire:click="viewAccount({{ $loan->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-slate-900 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto">
                                            <span class="material-symbols-outlined text-sm">visibility</span>
                                        </button>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-slate-900 tracking-tight">{{ $loan->account_no }}</span>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $loan->product->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-black text-xs text-slate-900 uppercase leading-none mb-1">{{ $loan->cif?->name ?? '-' }}</p>
                                        <p class="text-[9px] text-slate-400 font-bold tracking-widest uppercase">{{ $loan->cif?->cif_no ?? '-' }}</p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black bg-slate-100 text-slate-600 uppercase tracking-widest">{{ $loan->pk_number }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <p class="font-black text-xs text-slate-900 tracking-tighter">Rp {{ number_format($loan->principal_amount, 2, ',', '.') }}</p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest italic">{{ format_percent($loan->interest_rate) }} P.A | {{ $loan->tenor }} {{ $loan->tenor_type }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black text-slate-900 tracking-widest">{{ optional($loan->disbursement_date)->format('d/m/Y') ?? '-' }}</span>
                                        <span class="text-[9px] font-black uppercase tracking-tighter text-amber-600">
                                                Menunggu Realisasi
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        @php
                                            $statusClass = match($loan->status) {
                                                'APPROVED' => 'bg-amber-50 text-amber-600 border-amber-100',
                                                default => 'bg-slate-50 text-slate-400 border-slate-100'
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded border {{ $statusClass }}">
                                            {{ $loan->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-32 text-center text-slate-300">
                                        @if(!$search && !$statusFilter)
                                            <span class="material-symbols-outlined text-6xl mb-4 opacity-50">search</span>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Silakan cari rekening, nomor PK, atau nama anggota<br>untuk menampilkan antrean pencairan kredit</p>
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
                                        | Status: <span class="{{ $selectedAccount->status === 'ACTIVE' ? 'text-emerald-500' : 'text-amber-500' }}">{{ $selectedAccount->status }}</span>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

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
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Plafon Pokok</label>
                                                <div class="relative">
                                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-sm font-bold text-white/50">Rp</span>
                                                    <input type="text" value="{{ number_format($selectedAccount->principal_amount, 2, ',', '.') }}" class="w-full pl-12 pr-5 py-3.5 bg-slate-900 border border-slate-900 rounded-2xl font-black text-sm text-white shadow-lg shadow-slate-900/20">
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
                                                <input type="text" value="{{ $selectedAccount->status }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm uppercase {{ $selectedAccount->status === 'ACTIVE' ? 'text-emerald-500' : 'text-amber-500' }}">
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
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                                                <span class="material-symbols-outlined text-sm">payments</span>
                                                Detail Pencairan & Outstanding
                                            </p>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Suku Bunga (% P.A)</label>
                                                <input type="text" value="{{ format_percent($selectedAccount->interest_rate) }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-slate-900">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tenor Kredit</label>
                                                <input type="text" value="{{ $selectedAccount->tenor }} {{ $selectedAccount->tenor_type }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-slate-900 uppercase">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Metode Hitung</label>
                                                <input type="text" value="{{ $selectedAccount->calculation_method }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-rose-600 uppercase">
                                            </div>
                                            @if($selectedAccount->is_diskonto)
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Bunga di Muka</label>
                                                <input type="text" value="Rp {{ number_format($selectedAccount->diskonto_upfront_amount, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-amber-50 border border-amber-100 rounded-2xl font-black text-sm text-amber-600 uppercase tracking-tighter">
                                            </div>
                                            @endif
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Outstanding Saat Ini</label>
                                                <input type="text" value="Rp {{ number_format($selectedAccount->outstanding_total, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-emerald-50 border border-emerald-100 rounded-2xl font-black text-sm text-emerald-600 uppercase tracking-tighter">
                                            </div>
                                            <div class="col-span-2 space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Rekening Tabungan Terkait</label>
                                                <input type="text" value="{{ $selectedAccount->savingAccount?->account_no ?? 'TUNAI / CASH' }} - {{ $selectedAccount->savingAccount?->product->name ?? 'TANPA REKENING TABUNGAN' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm uppercase">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>

                    <!-- FINANCIAL SUMMARY & ACTION -->
                    @if($selectedAccount->status === 'APPROVED')
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                        <div class="md:col-span-8 bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 p-10">
                            <div class="border-b border-slate-100 pb-4 mb-8">
                                <p class="text-xs font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm text-indigo-600">summarize</span>
                                    Ringkasan Pencairan Dana
                                </p>
                            </div>

                            @php
                                $provisionAmt  = (float)($selectedAccount->provision_fee  ?? 0);
                                $adminFeeAmt   = (float)($selectedAccount->admin_fee       ?? 0);
                                $insuranceAmt  = (float)($selectedAccount->insurance_fee  ?? 0);
                                $flaggingAmt   = (float)($selectedAccount->flagging_fee   ?? 0);
                                $stampDutyAmt  = (float)($selectedAccount->stamp_duty_fee ?? 0);
                                $prepaidAmt    = (float)($selectedAccount->prepaid_installment_amount ?? 0);
                                $blockedAmt    = (float)($selectedAccount->blocked_savings_amount ?? 0);
                                $diskontoUpfrontAmt = $selectedAccount->is_diskonto ? (float)($selectedAccount->diskonto_upfront_amount ?? 0) : 0;

                                $netDisbursement = $selectedAccount->principal_amount - $provisionAmt - $adminFeeAmt - $insuranceAmt - $flaggingAmt - $stampDutyAmt - $prepaidAmt - $blockedAmt - $diskontoUpfrontAmt;
                            @endphp

                            <div class="space-y-4">
                                <div class="flex justify-between items-center px-4 py-3 bg-slate-50 rounded-2xl border border-slate-100">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Plafon Kredit</span>
                                    <span class="text-sm font-black text-slate-900">Rp {{ number_format($selectedAccount->principal_amount, 2, ',', '.') }}</span>
                                </div>
                                
                                @if($provisionAmt > 0)
                                <div class="flex justify-between items-center px-4 py-3 bg-rose-50/50 rounded-2xl border border-rose-100/50">
                                    <span class="text-[10px] font-bold text-rose-500 uppercase tracking-widest leading-none">Biaya Provisi</span>
                                    <span class="text-sm font-black text-rose-600">- Rp {{ number_format($provisionAmt, 2, ',', '.') }}</span>
                                </div>
                                @endif

                                @if($adminFeeAmt > 0)
                                <div class="flex justify-between items-center px-4 py-3 bg-rose-50/50 rounded-2xl border border-rose-100/50">
                                    <span class="text-[10px] font-bold text-rose-500 uppercase tracking-widest leading-none">Biaya Administrasi</span>
                                    <span class="text-sm font-black text-rose-600">- Rp {{ number_format($adminFeeAmt, 2, ',', '.') }}</span>
                                </div>
                                @endif

                                @if($insuranceAmt > 0)
                                <div class="flex justify-between items-center px-4 py-3 bg-rose-50/50 rounded-2xl border border-rose-100/50">
                                    <span class="text-[10px] font-bold text-rose-500 uppercase tracking-widest leading-none">Biaya Asuransi</span>
                                    <span class="text-sm font-black text-rose-600">- Rp {{ number_format($insuranceAmt, 2, ',', '.') }}</span>
                                </div>
                                @endif

                                @if($flaggingAmt > 0)
                                <div class="flex justify-between items-center px-4 py-3 bg-rose-50/50 rounded-2xl border border-rose-100/50">
                                    <span class="text-[10px] font-bold text-rose-500 uppercase tracking-widest leading-none">Biaya Flagging</span>
                                    <span class="text-sm font-black text-rose-600">- Rp {{ number_format($flaggingAmt, 2, ',', '.') }}</span>
                                </div>
                                @endif

                                @if($stampDutyAmt > 0)
                                <div class="flex justify-between items-center px-4 py-3 bg-rose-50/50 rounded-2xl border border-rose-100/50">
                                    <span class="text-[10px] font-bold text-rose-500 uppercase tracking-widest leading-none">Biaya Materai</span>
                                    <span class="text-sm font-black text-rose-600">- Rp {{ number_format($stampDutyAmt, 2, ',', '.') }}</span>
                                </div>
                                @endif

                                @if($prepaidAmt > 0)
                                <div class="flex justify-between items-center px-4 py-3 bg-amber-50/50 rounded-2xl border border-amber-100/50">
                                    <div>
                                        <span class="text-[10px] font-bold text-amber-600 uppercase tracking-widest leading-none block">Potongan Angsuran Pertama</span>
                                        <span class="text-[8px] font-bold text-amber-500/70 uppercase tracking-widest italic">({{ $selectedAccount->prepaid_installment_count }} Bulan)</span>
                                    </div>
                                    <span class="text-sm font-black text-amber-600">- Rp {{ number_format($prepaidAmt, 2, ',', '.') }}</span>
                                </div>
                                @endif

                                @if($diskontoUpfrontAmt > 0)
                                <div class="flex justify-between items-center px-4 py-3 bg-amber-50/50 rounded-2xl border border-amber-100/50">
                                    <div>
                                        <span class="text-[10px] font-bold text-amber-600 uppercase tracking-widest leading-none block">Bunga di Muka Diskonto</span>
                                        <span class="text-[8px] font-bold text-amber-500/70 uppercase tracking-widest italic">Dipotong saat pencairan</span>
                                    </div>
                                    <span class="text-sm font-black text-amber-600">- Rp {{ number_format($diskontoUpfrontAmt, 2, ',', '.') }}</span>
                                </div>
                                @endif

                                @if($blockedAmt > 0)
                                <div class="flex justify-between items-center px-4 py-3 bg-indigo-50/50 rounded-2xl border border-indigo-100/50">
                                    <div>
                                        <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest leading-none block">Dana Mengendap</span>
                                        <span class="text-[8px] font-bold text-indigo-500/70 uppercase tracking-widest italic">Dipindahkan ke Tabungan Anggota</span>
                                    </div>
                                    <span class="text-sm font-black text-indigo-600">- Rp {{ number_format($blockedAmt, 2, ',', '.') }}</span>
                                </div>
                                @endif

                                <div class="pt-4 mt-4 border-t-2 border-dashed border-slate-200 flex justify-between items-center px-4">
                                    <div>
                                        <p class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.2em] mb-1">Dana Bersih Diterima</p>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest italic">
                                            @if($selectedAccount->savingAccount)
                                                Dikreditkan ke: <span class="text-slate-600 font-black">{{ $selectedAccount->savingAccount->account_no }}</span>
                                            @else
                                                Diterima secara <span class="text-rose-600 font-black">TUNAI / CASH</span>
                                            @endif
                                        </p>
                                    </div>
                                    <p class="text-2xl font-black text-slate-900 tracking-tighter">Rp {{ number_format($netDisbursement, 2, ',', '.') }}</p>
                                </div>
                            </div>

                            <div class="mt-10 pt-8 border-t border-slate-100 flex justify-between items-center">
                                <div class="flex items-center space-x-3 text-slate-400">
                                    <span class="material-symbols-outlined text-sm">verified_user</span>
                                    <p class="text-[9px] font-bold uppercase tracking-widest leading-tight">Otorisasi Supervisor diperlukan<br>untuk transaksi di atas limit limit</p>
                                </div>
                                <button wire:click="confirmDisbursement({{ $selectedAccount->id }})" class="bg-indigo-600 hover:shadow-lg hover:shadow-indigo-600/20 text-white px-10 py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all active:scale-95 flex items-center space-x-3">
                                    <span class="material-symbols-outlined text-sm">payments</span>
                                    <span>Konfirmasi & Cairkan Dana</span>
                                </button>
                            </div>
                        </div>

                        <div class="md:col-span-4 p-8 bg-indigo-900 rounded-[2.5rem] shadow-xl shadow-indigo-900/20 text-white relative overflow-hidden">
                            <div class="absolute -right-10 -top-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-indigo-300 mb-6">Instruksi Pencairan</h4>
                            <div class="space-y-6">
                                <div class="flex gap-4">
                                    <div class="w-6 h-6 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                        <span class="text-[10px] font-black">1</span>
                                    </div>
                                    <p class="text-[10px] font-bold leading-relaxed text-indigo-100 uppercase tracking-wider">Periksa kesesuaian data akad dengan sistem</p>
                                </div>
                                <div class="flex gap-4">
                                    <div class="w-6 h-6 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                        <span class="text-[10px] font-black">2</span>
                                    </div>
                                    <p class="text-[10px] font-bold leading-relaxed text-indigo-100 uppercase tracking-wider">Pastikan nomor rekening tabungan tujuan sudah aktif</p>
                                </div>
                                <div class="flex gap-4">
                                    <div class="w-6 h-6 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                        <span class="text-[10px] font-black">3</span>
                                    </div>
                                    <p class="text-[10px] font-bold leading-relaxed text-indigo-100 uppercase tracking-wider">Simpan slip pencairan sebagai bukti arsip</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($selectedAccount->schedules && $selectedAccount->schedules->count() > 0)
                        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span class="material-symbols-outlined text-slate-900 text-xl">calendar_month</span>
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-900">Jadwal Angsuran</h4>
                                </div>
                            </div>
                            <div class="overflow-x-auto custom-scrollbar">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-white border-b border-slate-100">
                                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Ke</th>
                                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Tgl Tagih</th>
                                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Pokok</th>
                                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Bunga</th>
                                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Baki Debet</th>
                                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Sisa Bunga</th>
                                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @php
                                            $remainingPrincipal = (float) $selectedAccount->principal_amount;
                                            $remainingInterest = (float) $selectedAccount->schedules->sum('interest_amount');
                                            $scheduleBalances = [];
                                            $totalScheduleInterest = $remainingInterest;

                                            foreach ($selectedAccount->schedules as $row) {
                                                $remainingPrincipal = max(0, round($remainingPrincipal - (float) $row->principal_amount, 2));
                                                $remainingInterest = max(0, round($remainingInterest - (float) $row->interest_amount, 2));
                                                $scheduleBalances[$row->id] = [
                                                    'principal' => $remainingPrincipal,
                                                    'interest' => $remainingInterest,
                                                ];
                                            }
                                        @endphp
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="py-4 px-6 text-[10px] font-black text-slate-400 text-center">0</td>
                                            <td class="py-4 px-6 text-[10px] font-bold text-slate-900">{{ $selectedAccount->disbursement_date?->format('d/m/Y') ?? '-' }}</td>
                                            <td class="py-4 px-6 text-[10px] font-bold text-slate-600 text-right">-</td>
                                            <td class="py-4 px-6 text-[10px] font-bold text-slate-600 text-right">-</td>
                                            <td class="py-4 px-6 text-[10px] font-bold text-slate-600 text-right">Rp {{ number_format($selectedAccount->principal_amount, 2, ',', '.') }}</td>
                                            <td class="py-4 px-6 text-[10px] font-bold text-slate-600 text-right">Rp {{ number_format($totalScheduleInterest, 2, ',', '.') }}</td>
                                            <td class="py-4 px-6 text-center">
                                                <span class="px-2 py-0.5 text-[8px] font-black rounded uppercase tracking-widest bg-indigo-50 text-indigo-600 border border-indigo-100">REALISASI</span>
                                            </td>
                                        </tr>
                                        @foreach($selectedAccount->schedules as $sched)
                                            <tr class="hover:bg-slate-50/50 transition-colors">
                                                <td class="py-4 px-6 text-[10px] font-black text-slate-400 text-center">{{ $sched->installment_number }}</td>
                                                <td class="py-4 px-6 text-[10px] font-bold text-slate-900">{{ $sched->due_date->format('d/m/Y') }}</td>
                                                <td class="py-4 px-6 text-[10px] font-bold text-slate-600 text-right">Rp {{ number_format($sched->principal_amount, 2, ',', '.') }}</td>
                                                <td class="py-4 px-6 text-[10px] font-bold text-slate-600 text-right">Rp {{ number_format($sched->interest_amount, 2, ',', '.') }}</td>
                                                <td class="py-4 px-6 text-[10px] font-bold text-slate-600 text-right">Rp {{ number_format($scheduleBalances[$sched->id]['principal'] ?? 0, 2, ',', '.') }}</td>
                                                <td class="py-4 px-6 text-[10px] font-bold text-slate-600 text-right">Rp {{ number_format($scheduleBalances[$sched->id]['interest'] ?? 0, 2, ',', '.') }}</td>
                                                <td class="py-4 px-6 text-center">
                                                    <span class="px-2 py-0.5 text-[8px] font-black rounded uppercase tracking-widest {{ $sched->status === 'PAID' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-50 text-slate-400 border border-slate-100' }}">
                                                        {{ $sched->status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        @endif

        @if($confirmingDisbursementId)
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-slate-900/75 backdrop-blur-sm"></div>
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-extrabold text-slate-900 mb-2">Konfirmasi Pencairan Dana</h3>
                                    <div class="mt-2 text-sm text-slate-500 font-medium mb-4">
                                        Anda akan mencairkan dana fasilitas kredit ini. Jurnal akrual pokok akan dibentuk dan dana akan dikreditkan ke Tabungan anggota atau dicairkan sesuai channel yang dipilih.
                                    </div>

                                    <!-- Channel Selection -->
                                    <div class="space-y-2 text-left mb-4">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jalur Transaksi (Channel)</label>
                                        <div class="relative">
                                            <select wire:model.live="disbursement_channel" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all appearance-none cursor-pointer">
                                                <option value="INTERNAL">Tabungan Internal</option>
                                                <option value="CASH">Tunai (Kas)</option>
                                                <option value="ABA">Antar Bank Aktiva (ABA)</option>
                                            </select>
                                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-sm">expand_more</span>
                                        </div>
                                    </div>

                                    <!-- Dropdown Pemilihan Sub-Akun COA (Kas / ABA) -->
                                    @if($disbursement_channel === 'ABA' && $abaCoas->count() > 1)
                                    <div class="space-y-2 text-left mb-4 animate-in fade-in slide-in-from-top-4 duration-300">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sub-Akun Bank (ABA)</label>
                                        <div class="relative">
                                            <select wire:model="bank_coa_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all appearance-none cursor-pointer">
                                                <option value="">-- Pilih Bank --</option>
                                                @foreach($abaCoas as $coa)
                                                    <option value="{{ $coa->id }}">{{ $coa->name }} ({{ $coa->coa_code }})</option>
                                                @endforeach
                                            </select>
                                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-sm">expand_more</span>
                                        </div>
                                        @error('bank_coa_id') <p class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</p> @enderror
                                    </div>
                                    @elseif($disbursement_channel === 'CASH' && $cashCoas->count() > 1)
                                    <div class="space-y-2 text-left mb-4 animate-in fade-in slide-in-from-top-4 duration-300">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sub-Akun Kas</label>
                                        <div class="relative">
                                            <select wire:model="cash_coa_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all appearance-none cursor-pointer">
                                                <option value="">-- Pilih Kas --</option>
                                                @foreach($cashCoas as $coa)
                                                    <option value="{{ $coa->id }}">{{ $coa->name }} ({{ $coa->coa_code }})</option>
                                                @endforeach
                                            </select>
                                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-sm">expand_more</span>
                                        </div>
                                        @error('cash_coa_id') <p class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</p> @enderror
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 py-4 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-100 gap-3">
                            <button wire:click="processDisbursement" type="button" class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-sm px-5 py-3 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 sm:w-auto sm:text-sm transition-colors focus:outline-none focus:ring-4 focus:ring-indigo-500/20 shadow-indigo-600/20">
                                Cairkan Sekarang
                            </button>
                            <button wire:click="$set('confirmingDisbursementId', null)" type="button" class="mt-3 w-full inline-flex justify-center rounded-2xl border border-slate-300 shadow-sm px-5 py-3 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto sm:text-sm transition-colors focus:outline-none focus:ring-4 focus:ring-slate-500/10">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
