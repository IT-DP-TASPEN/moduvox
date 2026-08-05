<div class="p-0">
    <x-header title="Pinjaman (Inquiry)" subtitle="Manajemen profil fasilitas pinjaman dan riwayat tagihan anggota." :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <select wire:model.live="statusFilter" class="pl-3 pr-10 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 appearance-none shadow-sm">
                        <option value="">Semua Status</option>
                        <option value="PENDING">PENDING</option>
                        <option value="APPROVED">APPROVED</option>
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="NPL">NPL / OVERDUE</option>
                        <option value="CANCELLED">CANCELLED</option>
                        <option value="CLOSED">CLOSED</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                        <span class="material-symbols-outlined text-sm">expand_more</span>
                    </div>
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live.debounce.500ms="search" type="text" placeholder="Cari Rekening, NIK atau PK..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 w-64 shadow-sm">
                </div>
                @can('loans.origination')
                    <a href="{{ route('loans.origination') }}" wire:navigate class="flex items-center space-x-2 bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                        <span class="material-symbols-outlined text-sm">post_add</span>
                        <span>Pendaftaran Baru</span>
                    </a>
                @endcan
            </div>
        </x-slot>
    </x-header>

    <div class="p-10">
        @if($viewMode === 'grid')
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden animate-in fade-in duration-500">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50">
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center w-20">OPSI</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Rekening & Customer</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">PK & Produk</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Plafon Cair</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Saldo Terutang</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">KOL / DPD</th>
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
                                            <span class="text-[10px] text-slate-700 font-black uppercase tracking-tight">{{ $loan->cif?->name ?? '-' }}</span>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $loan->cif?->cif_no ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-black text-xs text-slate-900 uppercase leading-none mb-1">{{ $loan->pk_number }}</p>
                                        <p class="text-[9px] text-slate-400 font-bold tracking-widest uppercase">{{ $loan->product->name }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <p class="font-black text-xs text-slate-900 tracking-tighter">Rp {{ number_format($loan->principal_amount, 2, ',', '.') }}</p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest italic">{{ $loan->tenor }} {{ $loan->tenor_type }} ({{ $loan->calculation_method }})</p>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <p class="font-black text-xs text-rose-600 tracking-tighter">Rp {{ number_format($loan->outstanding_total, 2, ',', '.') }}</p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest italic">P: {{ number_format($loan->outstanding_principal, 2, ',', '.') }} | B: {{ number_format($loan->outstanding_interest, 2, ',', '.') }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <p class="font-black text-xs text-slate-800">
                                            @php
                                                $angs = $loan->schedules->first() ? ($loan->schedules->first()->principal_amount + $loan->schedules->first()->interest_amount) : 0;
                                            @endphp
                                            Angs: Rp {{ number_format($angs, 2, ',', '.') }}
                                        </p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Real: {{ $loan->disbursement_date ? $loan->disbursement_date->format('d/m/y') : '-' }} | JT: {{ $loan->disbursement_date ? $loan->disbursement_date->copy()->addMonths($loan->tenor)->format('d/m/y') : '-' }} | B: {{ format_percent($loan->interest_rate) }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        @php
                                            $statusClass = match($loan->status) {
                                                'ACTIVE' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                                'PENDING' => 'bg-amber-50 text-amber-600 border-amber-100',
                                                'NPL' => 'bg-rose-50 text-rose-600 border-rose-100',
                                                'CLOSED' => 'bg-slate-100 text-slate-500 border-slate-200',
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
                                    <td colspan="7" class="py-20 text-center text-slate-300">
                                        @if(!$search)
                                            <span class="material-symbols-outlined text-6xl mb-4 opacity-50">search</span>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Silakan cari nomor rekening, NIK atau CIF<br>untuk menampilkan data pinjaman</p>
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
                
                @if($loans->hasPages())
                    <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                        {{ $loans->links(data: ['scrollTo' => false]) }}
                    </div>
                @endif
            </div>
        @else
            <!-- DETAIL VIEW: Modern Standardized Inquiry -->
            @if($selectedAccount)
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
                    <!-- TOP BAR: Navigation & Quick Stats -->
                    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col mb-2">
                        <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                            <div class="flex items-center space-x-4">
                                <button wire:click="closeView" class="p-2 bg-white hover:bg-slate-200 rounded-xl transition-all shadow-sm border border-slate-200">
                                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                                </button>
                                <div>
                                    <h2 class="font-extrabold text-sm text-slate-900 tracking-wider uppercase">
                                        Detil Fasilitas: <span class="text-indigo-600">{{ $selectedAccount->account_no }}</span>
                                    </h2>
                                    <p class="text-[10px] uppercase font-bold text-slate-500 tracking-widest mt-1">
                                        Produk: <span class="text-slate-900">{{ $selectedAccount->product->name }}</span>
                                        | Cabang: <span class="text-slate-900">{{ $selectedAccount->branch->name ?? '-' }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                @php
                                    $statusClass = match($selectedAccount->status) {
                                        'ACTIVE' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'PENDING' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        'NPL' => 'bg-rose-50 text-rose-600 border-rose-100',
                                        'CLOSED' => 'bg-slate-100 text-slate-500 border-slate-200',
                                        default => 'bg-slate-50 text-slate-400 border-slate-100'
                                    };
                                @endphp
                                <span class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-xl border {{ $statusClass }}">
                                    {{ $selectedAccount->status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-2xl border border-slate-200 p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Angsuran Terbayar</p>
                            <p class="mt-2 text-xl font-black text-emerald-600">{{ $detailStats['paid_installments'] }} / {{ $detailStats['total_installments'] }}</p>
                        </div>
                        <div class="bg-white rounded-2xl border border-slate-200 p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Kekurangan Bulan Sebelumnya</p>
                            <p class="mt-2 text-xl font-black text-rose-600">Rp {{ number_format($detailStats['previous_month_shortfall'], 2, ',', '.') }}</p>
                        </div>
                        <div class="bg-white rounded-2xl border border-slate-200 p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Kolektibilitas</p>
                            <p class="mt-2 text-xl font-black text-slate-800">KOL {{ $selectedAccount->kol_level ?? 1 }} <span class="text-sm text-slate-500">({{ $selectedAccount->dpd_days ?? 0 }} hari)</span></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-8">
                        <!-- Left Pillar: Form Information -->
                        <div class="col-span-12 lg:col-span-8 space-y-8">
                            
                            <!-- FORM VIEW: SECTIONED CARDS -->
                            <div class="p-10 bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 overflow-hidden">
                                <form class="flex flex-col">
                                    <fieldset disabled class="m-0 p-0 border-0">
                                        <div class="space-y-12">
                                            
                                            <!-- SECTION 1: Profil Anggota -->
                                            <div>
                                                <div class="border-b border-slate-200 pb-2 mb-6 text-indigo-600 flex items-center justify-between">
                                                    <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-sm">person</span>
                                                        Profil Pemilik / CIF Anggota
                                                    </p>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Lengkap</label>
                                                        <input type="text" value="{{ $selectedAccount->cif->name }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 uppercase">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. CIF</label>
                                                        <input type="text" value="{{ $selectedAccount->cif->cif_no }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-indigo-600">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- SECTION 2: Struktur Pinjaman -->
                                            <div>
                                                <div class="border-b border-slate-200 pb-2 mb-6 text-slate-900 flex items-center justify-between">
                                                    <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-sm">payments</span>
                                                        Struktur & Parameter Kredit
                                                    </p>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Plafon Pinjaman</label>
                                                        <div class="relative">
                                                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-sm font-bold text-white/40">Rp</span>
                                                            <input type="text" value="{{ number_format($selectedAccount->principal_amount, 2, ',', '.') }}" class="w-full pl-12 pr-5 py-3.5 bg-slate-900 border border-slate-900 rounded-2xl font-black text-sm text-white shadow-lg shadow-slate-900/20">
                                                        </div>
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Metode & Bunga</label>
                                                        <input type="text" value="{{ $selectedAccount->calculation_method }} @ {{ format_percent($selectedAccount->interest_rate) }} P.A" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Jangka Waktu (Tenor)</label>
                                                        <input type="text" value="{{ $selectedAccount->tenor }} {{ $selectedAccount->tenor_type }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                                    </div>
                                                    @if($selectedAccount->is_diskonto)
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Bunga di Muka</label>
                                                        <input type="text" value="Rp {{ number_format($selectedAccount->diskonto_upfront_amount, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-amber-50 border border-amber-100 rounded-2xl font-black text-sm text-amber-600">
                                                    </div>
                                                    @endif
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tgl Realisasi / Pencairan</label>
                                                        <input type="text" value="{{ $selectedAccount->disbursement_date ? $selectedAccount->disbursement_date->format('d/m/Y') : '-' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-emerald-600">
                                                    </div>
                                                    <div class="col-span-2 space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nomor Perjanjian Kredit (PK)</label>
                                                        <input type="text" value="{{ $selectedAccount->pk_number }}" class="w-full px-5 py-3.5 bg-indigo-50 border border-indigo-100 rounded-2xl font-black text-sm text-indigo-700 tracking-tight">
                                                    </div>
                                                    <div class="col-span-2 space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Alasan Kredit / Keperluan</label>
                                                        <textarea class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-700" rows="3">{{ $selectedAccount->reason ?? '-' }}</textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- SECTION 3: Linked Accounts -->
                                            <div>
                                                <div class="border-b border-slate-200 pb-2 mb-6 text-amber-600 flex items-center justify-between">
                                                    <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-sm">account_balance_wallet</span>
                                                        Rekening Auto-Debet Angsuran
                                                    </p>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. Rekening Tabungan</label>
                                                        <input type="text" value="{{ $selectedAccount->savingAccount->account_no ?? 'TIDAK TERLINK' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-amber-600">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Produk Tabungan</label>
                                                        <input type="text" value="{{ $selectedAccount->savingAccount->product->name ?? '-' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- SECTION 4: Rincian Biaya & Potongan -->
                                            <div>
                                                <div class="border-b border-slate-200 pb-2 mb-6 text-emerald-600 flex items-center justify-between">
                                                    <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-sm">receipt_long</span>
                                                        Rincian Biaya & Potongan
                                                    </p>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Biaya Provisi</label>
                                                        <input type="text" value="Rp {{ number_format($selectedAccount->provision_fee, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Biaya Administrasi</label>
                                                        <input type="text" value="Rp {{ number_format($selectedAccount->admin_fee, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Biaya Asuransi</label>
                                                        <input type="text" value="Rp {{ number_format($selectedAccount->insurance_fee, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Biaya Flagging</label>
                                                        <input type="text" value="Rp {{ number_format($selectedAccount->flagging_fee, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Biaya Materai</label>
                                                        <input type="text" value="Rp {{ number_format($selectedAccount->stamp_duty_fee, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Angsuran di Muka</label>
                                                        <input type="text" value="Rp {{ number_format($selectedAccount->prepaid_installment_amount, 2, ',', '.') }} ({{ $selectedAccount->prepaid_installment_count }} bln)" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tabungan Mengendap</label>
                                                        <input type="text" value="Rp {{ number_format($selectedAccount->blocked_savings_amount, 2, ',', '.') }} ({{ $selectedAccount->blocked_savings_count }} bln)" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-amber-600">
                                                    </div>
                                                    @if($selectedAccount->is_diskonto)
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Bunga di Muka Diskonto</label>
                                                        <input type="text" value="Rp {{ number_format($selectedAccount->diskonto_upfront_amount, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-amber-50 border border-amber-100 rounded-2xl font-black text-sm text-amber-600">
                                                    </div>
                                                    @endif
                                                    <div class="col-span-2 space-y-2">
                                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-emerald-600 ml-1">Estimasi Penerimaan Bersih (Net)</label>
                                                        @php
                                                            $diskontoUpfrontAmt = $selectedAccount->is_diskonto ? (float)($selectedAccount->diskonto_upfront_amount ?? 0) : 0;
                                                            $totalFees = $selectedAccount->provision_fee + $selectedAccount->admin_fee + $selectedAccount->insurance_fee + $selectedAccount->flagging_fee + $selectedAccount->stamp_duty_fee + $selectedAccount->prepaid_installment_amount + $selectedAccount->blocked_savings_amount + $diskontoUpfrontAmt;
                                                            $net = $selectedAccount->principal_amount - $totalFees;
                                                        @endphp
                                                        <input type="text" value="Rp {{ number_format($net, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-emerald-50 border border-emerald-100 rounded-2xl font-black text-sm text-emerald-700 shadow-sm shadow-emerald-900/5">
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                            <!-- Transaction History Feed (Mutasi) -->
                            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col">
                                <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-200 flex items-center justify-center text-slate-900">
                                            <span class="material-symbols-outlined text-sm">history</span>
                                        </div>
                                        <h4 class="text-xs font-black uppercase tracking-tight text-slate-900">Mutasi Rekening</h4>
                                    </div>
                                </div>
                                <div class="overflow-x-auto custom-scrollbar max-h-[500px] overflow-y-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-white border-b border-slate-100 sticky top-0 z-10">
                                                <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest w-24">TANGGAL</th>
                                                <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">KETERANGAN & REF</th>
                                                <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">NOMINAL</th>
                                                <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center w-24">TIPE</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            @forelse($transactionPaginator as $tx)
                                                <tr class="hover:bg-slate-50/50 transition-colors">
                                                    <td class="py-4 px-6">
                                                        <p class="text-[10px] font-black text-slate-900">{{ $tx->created_at->format('d/m/Y') }}</p>
                                                        <p class="text-[9px] font-bold text-slate-400 mt-0.5">{{ $tx->created_at->format('H:i') }}</p>
                                                    </td>
                                                    <td class="py-4 px-6">
                                                        <p class="text-[11px] font-bold text-slate-900">{{ $tx->description }}</p>
                                                        @if(((float) $tx->amount_principal > 0) || ((float) $tx->amount_interest > 0) || ((float) $tx->amount_penalty > 0))
                                                            <div class="flex flex-wrap gap-1.5 mt-2">
                                                                @if((float) $tx->amount_principal > 0)
                                                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600 text-[8px] font-black uppercase tracking-widest">Pokok Rp {{ number_format($tx->amount_principal, 2, ',', '.') }}</span>
                                                                @endif
                                                                @if((float) $tx->amount_interest > 0)
                                                                    <span class="px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-600 text-[8px] font-black uppercase tracking-widest">Kewajiban Bunga Rp {{ number_format($tx->amount_interest, 2, ',', '.') }}</span>
                                                                @endif
                                                                @if((float) $tx->amount_penalty > 0)
                                                                    <span class="px-2 py-0.5 rounded-lg bg-amber-50 text-amber-600 text-[8px] font-black uppercase tracking-widest">Denda Rp {{ number_format($tx->amount_penalty, 2, ',', '.') }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ $tx->reference_number }}</p>
                                                    </td>
                                                    <td class="py-4 px-6 text-right">
                                                        <p class="text-xs font-black tracking-tight {{ in_array($tx->transaction_type, ['DISBURSEMENT', 'REVERSAL']) ? 'text-rose-600' : 'text-emerald-600' }}">
                                                            {{ in_array($tx->transaction_type, ['DISBURSEMENT', 'REVERSAL']) ? '-' : '+' }} Rp {{ number_format(abs($tx->total_amount), 2, ',', '.') }}
                                                        </p>
                                                    </td>
                                                    <td class="py-4 px-6 text-center">
                                                        <span @class([
                                                            'px-2 py-1 text-[8px] font-black rounded uppercase tracking-widest border whitespace-nowrap',
                                                            'bg-emerald-50 text-emerald-600 border-emerald-100' => in_array($tx->transaction_type, ['DISBURSEMENT', 'REPAYMENT_MANUAL', 'REPAYMENT_AUTO']),
                                                            'bg-rose-50 text-rose-600 border-rose-100' => $tx->transaction_type === 'REVERSAL',
                                                            'bg-slate-50 text-slate-600 border-slate-200' => !in_array($tx->transaction_type, ['DISBURSEMENT', 'REPAYMENT_MANUAL', 'REPAYMENT_AUTO', 'REVERSAL']),
                                                        ])>
                                                            {{ str_replace('_', ' ', $tx->transaction_type) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-16 text-center opacity-40">
                                                        <span class="material-symbols-outlined text-4xl mb-2">history</span>
                                                        <p class="text-[10px] font-black uppercase tracking-widest">Belum ada mutasi</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if($transactionPaginator && $transactionPaginator->hasPages())
                                    <x-table-pagination :paginator="$transactionPaginator" page-action="gotoTransactionPage" />
                                @endif
                            </div>
                            <!-- AMORTIZATION SCHEDULE TABLE -->
                            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col">
                                <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-200 flex items-center justify-center text-slate-900">
                                            <span class="material-symbols-outlined text-sm">calendar_month</span>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-black uppercase tracking-tight text-slate-900 leading-none">Jadwal Angsuran</h4>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Rincian rencana pembayaran pokok & bunga</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="overflow-x-auto custom-scrollbar">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-white border-b border-slate-100">
                                                <th class="py-5 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center w-16">KE</th>
                                                <th class="py-5 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">TGL TAGIH</th>
                                                <th class="py-5 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">POKOK</th>
                                                <th class="py-5 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">BUNGA</th>
                                                <th class="py-5 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">TOTAL TAGIHAN</th>
                                                <th class="py-5 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">BAKI DEBET</th>
                                                <th class="py-5 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">SISA BUNGA</th>
                                                <th class="py-5 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">STATUS</th>
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
                                                <td class="py-4 px-6 text-[11px] font-black text-slate-400 text-center">0</td>
                                                <td class="py-4 px-6 text-[11px] font-bold text-slate-900">{{ $selectedAccount->disbursement_date?->format('d/m/Y') ?? '-' }}</td>
                                                <td class="py-4 px-6 text-[11px] font-bold text-slate-600 text-right">-</td>
                                                <td class="py-4 px-6 text-[11px] font-bold text-slate-600 text-right">-</td>
                                                <td class="py-4 px-6 text-[11px] font-black text-indigo-600 text-right">Realisasi</td>
                                                <td class="py-4 px-6 text-[11px] font-bold text-slate-600 text-right">Rp {{ number_format($selectedAccount->principal_amount, 2, ',', '.') }}</td>
                                                <td class="py-4 px-6 text-[11px] font-bold text-slate-600 text-right">Rp {{ number_format($totalScheduleInterest, 2, ',', '.') }}</td>
                                                <td class="py-4 px-6 text-center">
                                                    <span class="px-2 py-0.5 text-[8px] font-black rounded uppercase tracking-widest border bg-indigo-50 text-indigo-600 border-indigo-100">REALISASI</span>
                                                </td>
                                            </tr>
                                            @foreach($schedulePaginator as $sched)
                                                <tr class="hover:bg-slate-50/50 transition-colors">
                                                    <td class="py-4 px-6 text-[11px] font-black text-slate-400 text-center">{{ $sched->installment_number }}</td>
                                                    <td class="py-4 px-6 text-[11px] font-bold text-slate-900">{{ $sched->due_date->format('d/m/Y') }}</td>
                                                    <td class="py-4 px-6 text-[11px] font-bold text-slate-600 text-right">Rp {{ number_format($sched->principal_amount, 2, ',', '.') }}</td>
                                                    <td class="py-4 px-6 text-[11px] font-bold text-slate-600 text-right">Rp {{ number_format($sched->interest_amount, 2, ',', '.') }}</td>
                                                    <td class="py-4 px-6 text-[11px] font-black text-indigo-600 text-right">Rp {{ number_format($sched->principal_amount + $sched->interest_amount, 2, ',', '.') }}</td>
                                                    <td class="py-4 px-6 text-[11px] font-bold text-slate-600 text-right">Rp {{ number_format($scheduleBalances[$sched->id]['principal'] ?? 0, 2, ',', '.') }}</td>
                                                    <td class="py-4 px-6 text-[11px] font-bold text-slate-600 text-right">Rp {{ number_format($scheduleBalances[$sched->id]['interest'] ?? 0, 2, ',', '.') }}</td>
                                                    <td class="py-4 px-6 text-center">
                                                        <span @class([
                                                            'px-2 py-0.5 text-[8px] font-black rounded uppercase tracking-widest border',
                                                            'bg-emerald-50 text-emerald-600 border-emerald-100' => $sched->status === 'PAID',
                                                            'bg-amber-50 text-amber-600 border-amber-100' => $sched->status === 'PARTIAL',
                                                            'bg-slate-50 text-slate-400 border-slate-100' => $sched->status === 'UNPAID',
                                                            'bg-rose-50 text-rose-600 border-rose-100' => $sched->status === 'OVERDUE',
                                                        ])>
                                                            {{ $sched->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($schedulePaginator && $schedulePaginator->hasPages())
                                    <x-table-pagination :paginator="$schedulePaginator" page-action="gotoSchedulePage" />
                                @endif
                            </div>

                            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col">
                                <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-white rounded-xl shadow-sm border border-slate-200 flex items-center justify-center text-slate-900">
                                            <span class="material-symbols-outlined text-sm">folder_open</span>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-black uppercase tracking-tight text-slate-900 leading-none">Credit Dokumen</h4>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Daftar dokumen terunggah pinjaman</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="overflow-x-auto custom-scrollbar">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-white border-b border-slate-100">
                                                <th class="py-5 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Tipe</th>
                                                <th class="py-5 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Nama Dokumen</th>
                                                <th class="py-5 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                                <th class="py-5 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            @forelse($documentPaginator as $doc)
                                                <tr class="hover:bg-slate-50/50 transition-colors">
                                                    <td class="py-4 px-6 text-[10px] font-black text-indigo-600 uppercase tracking-widest">{{ $doc->document_type_label }}</td>
                                                    <td class="py-4 px-6">
                                                        <p class="text-[11px] font-bold text-slate-900">{{ $doc->document_name }}</p>
                                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ $doc->file_original_name }}</p>
                                                    </td>
                                                    <td class="py-4 px-6 text-center">
                                                        <span @class([
                                                            'px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest border',
                                                            'bg-amber-50 text-amber-600 border-amber-100' => $doc->status == 'PENDING',
                                                            'bg-emerald-50 text-emerald-600 border-emerald-100' => $doc->status == 'VERIFIED',
                                                            'bg-rose-50 text-rose-600 border-rose-100' => $doc->status == 'REJECTED',
                                                        ])>{{ $doc->status }}</span>
                                                    </td>
                                                    <td class="py-4 px-6">
                                                        <div class="flex items-center justify-center gap-2">
                                                            <a href="{{ route('loans.documents.view', $doc->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">View</a>
                                                            <a href="{{ route('loans.documents.download', $doc->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">Download</a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-14 text-center opacity-40">
                                                        <span class="material-symbols-outlined text-4xl mb-2">folder_open</span>
                                                        <p class="text-[10px] font-black uppercase tracking-widest">Belum ada dokumen</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if($documentPaginator && $documentPaginator->hasPages())
                                    <x-table-pagination :paginator="$documentPaginator" page-action="gotoDocumentPage" />
                                @endif
                            </div>
                        </div>

                        <!-- Right Pillar: Stats & History -->
                        <div class="col-span-12 lg:col-span-4 space-y-8">
                            <!-- Financial Summary Card (Dark) -->
                            <div class="p-8 bg-slate-900 rounded-[2.5rem] shadow-xl shadow-slate-900/20 text-white relative overflow-hidden">
                                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/5 rounded-full blur-3xl"></div>
                                <div class="relative z-10">
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/50 mb-1">Total Saldo Terutang</p>
                                    <h3 class="text-3xl font-black tracking-tight mb-8">Rp {{ number_format($selectedAccount->outstanding_total, 2, ',', '.') }}</h3>
                                    
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center group/item">
                                            <span class="text-[10px] font-bold text-white/40 uppercase tracking-widest group-hover/item:text-white/60 transition-colors">Sisa Pokok</span>
                                            <span class="text-sm font-black tracking-tight">Rp {{ number_format($selectedAccount->outstanding_principal, 2, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center group/item">
                                            <span class="text-[10px] font-bold text-white/40 uppercase tracking-widest group-hover/item:text-white/60 transition-colors">Sisa Bunga</span>
                                            <span class="text-sm font-black text-rose-400 tracking-tight">Rp {{ number_format($selectedAccount->outstanding_interest, 2, ',', '.') }}</span>
                                        </div>
                                        @if($selectedAccount->is_diskonto)
                                        <div class="flex justify-between items-center group/item">
                                            <span class="text-[10px] font-bold text-white/40 uppercase tracking-widest group-hover/item:text-white/60 transition-colors">Bunga di Muka</span>
                                            <span class="text-sm font-black text-amber-300 tracking-tight">Rp {{ number_format($selectedAccount->diskonto_upfront_amount, 2, ',', '.') }}</span>
                                        </div>
                                        @endif
                                        <div class="pt-4 border-t border-white/10 flex justify-between items-center group/item">
                                            <span class="text-[10px] font-bold text-white/40 uppercase tracking-widest group-hover/item:text-white/60 transition-colors">Total Denda</span>
                                            <span class="text-sm font-black text-amber-400 tracking-tight">Rp {{ number_format($selectedAccount->outstanding_penalty, 2, ',', '.') }}</span>
                                        </div>
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
