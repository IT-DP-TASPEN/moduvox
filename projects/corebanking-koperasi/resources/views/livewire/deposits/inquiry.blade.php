<div class="p-0">
    <x-header title="Inquiry Simpanan Berjangka" subtitle="Pencarian profil, status bilyet, dan mutasi rekening simpanan berjangka" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
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
                    <input wire:model.live="search" type="text" placeholder="No Rekening, Bilyet atau Nama..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 w-64 shadow-sm">
                </div>
                @can('deposits.placement')
                    <a href="{{ route('deposits.placement') }}" wire:navigate class="flex items-center space-x-2 bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                        <span class="material-symbols-outlined text-sm">add_card</span>
                        <span>Penempatan Baru</span>
                    </a>
                @endcan
            </div>
        </x-slot>
    </x-header>

    <div class="p-10">
        @if($viewMode === 'grid')
            <!-- GRID VIEW: List of Accounts -->
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
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">ARO</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($items as $item)
                                <tr wire:key="deposit-row-{{ $item->id }}" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="py-4 px-6 text-center">
                                        <button wire:click="viewAccount({{ $item->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-slate-900 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto">
                                            <span class="material-symbols-outlined text-sm">visibility</span>
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
                                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest italic">{{ format_percent($item->interest_rate) }} P.A | {{ $item->tenor }} BLN</p>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black text-slate-900 tracking-widest">{{ $item->maturity_date->format('d/m/Y') }}</span>
                                            @php 
                                                $days = now()->diffInDays($item->maturity_date, false);
                                            @endphp
                                            <span @class([
                                                'text-[9px] font-black uppercase tracking-tighter',
                                                'text-emerald-600' => $days > 30,
                                                'text-amber-600' => $days <= 30 && $days > 0,
                                                'text-rose-600' => $days <= 0,
                                            ])>
                                                {{ $days > 0 ? "H - $days Hari" : ($days == 0 ? 'Hari Ini' : 'Lewat') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        @php
                                            $aroLabel = match($item->rollover_type) {
                                                'PRINCIPAL' => 'Pokok',
                                                'PRINCIPAL_INTEREST' => 'Pokok + Bunga',
                                                default => 'Non-ARO',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded border bg-slate-50 text-slate-600 border-slate-100">{{ $aroLabel }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        @php
                                            $statusClass = match($item->status) {
                                                'ACTIVE' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                                'PENDING' => 'bg-amber-50 text-amber-600 border-amber-100',
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
                                    <td colspan="8" class="py-32 text-center text-slate-300">
                                        @if(!$search && !$filter_branch)
                                            <span class="material-symbols-outlined text-6xl mb-4 opacity-50">search</span>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Silakan cari nomor rekening atau nama anggota<br>untuk menampilkan data simpanan berjangka</p>
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

        @else
            <!-- DETAIL VIEW: Detailed Information -->
            @if($selectedAccount)
                <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
                    <!-- TOP BAR: Navigation & Quick Stats -->
                    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col mb-10">
                        <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                            <div class="flex items-center space-x-4">
                                <button wire:click="closeView" class="p-2 bg-white hover:bg-slate-200 rounded-xl transition-all shadow-sm border border-slate-200">
                                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                                </button>
                                @php
                                    $selectedAroLabel = match($selectedAccount->rollover_type) {
                                        'PRINCIPAL' => 'ARO Pokok',
                                        'PRINCIPAL_INTEREST' => 'ARO Pokok + Bunga',
                                        default => 'Non-ARO',
                                    };
                                @endphp
                                <div>
                                    <h2 class="font-extrabold text-sm text-slate-900 tracking-wider uppercase">
                                        Data Rekening: {{ $selectedAccount->account_no }}
                                    </h2>
                                    <p class="text-[10px] uppercase font-bold text-slate-500 tracking-widest mt-1">
                                        Produk: <span class="text-indigo-600">{{ $selectedAccount->product->name }}</span>
                                        | Status: <span class="{{ $selectedAccount->status === 'ACTIVE' ? 'text-emerald-500' : 'text-rose-500' }}">{{ $selectedAccount->status }}</span>
                                        | Instruksi: <span class="text-slate-900">{{ $selectedAroLabel }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FORM VIEW: Structured Information -->
                    <div class="p-10 bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 overflow-hidden">
                        <form class="flex flex-col">
                            <fieldset disabled class="m-0 p-0 border-0">
                                <div class="space-y-12">
                                    <!-- SECTION 1: Informasi Rekening -->
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
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Produk Investasi</label>
                                                <input type="text" value="{{ $selectedAccount->product->name }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-indigo-600 uppercase">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nominal Pokok Penempatan</label>
                                                <div class="relative">
                                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-sm font-bold text-white/50">Rp</span>
                                                    <input type="text" value="{{ number_format($selectedAccount->amount, 2, ',', '.') }}" class="w-full pl-12 pr-5 py-3.5 bg-slate-900 border border-slate-900 rounded-2xl font-black text-sm text-white shadow-lg shadow-slate-900/20">
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kode Bilyet</label>
                                                <input type="text" value="{{ $selectedAccount->bilyet?->kode_bilyet ?? '-' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-amber-600 uppercase">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Pembukaan</label>
                                                <input type="text" value="{{ $selectedAccount->placement_date->format('d/m/Y') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status Rekening</label>
                                                <input type="text" value="{{ $selectedAccount->status }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm uppercase {{ $selectedAccount->status === 'ACTIVE' ? 'text-emerald-500' : 'text-rose-500' }}">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Sumber Dana</label>
                                                <input type="text" value="{{ $selectedAccount->source_of_funds ?? '-' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                            </div>
                                            <div class="md:col-span-2 space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Alasan Penempatan / Tujuan Simpanan Berjangka</label>
                                                <textarea class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-700" rows="2">{{ $selectedAccount->reason ?? '-' }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SECTION 2: Linked CIF Profile -->
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
                                                <input type="text" readonly value="{{ $cif_no }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-indigo-600">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Cabang</label>
                                                <input type="text" readonly value="{{ $selectedAccount->cif->branch->name ?? '-' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status CIF</label>
                                                <input type="text" readonly value="{{ $selectedAccount->cif->status }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm {{ $selectedAccount->cif->status === 'ACTIVE' ? 'text-emerald-600' : 'text-rose-600' }}">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SECTION 3: Investment Details -->
                                    <div>
                                        <div class="border-b border-slate-200 pb-2 mb-6 text-emerald-600 flex items-center justify-between">
                                            <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                                <span class="material-symbols-outlined text-sm">payments</span>
                                                Detail Konfigurasi Bunga & Payout
                                            </p>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Suku Bunga (% P.A)</label>
                                                <input type="text" value="{{ format_percent($selectedAccount->interest_rate) }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-slate-900">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tenor Investasi (Bulan)</label>
                                                <input type="text" value="{{ $selectedAccount->tenor }} BULAN" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-slate-900">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Estimasi Tanggal Jatuh Tempo</label>
                                                <input type="text" value="{{ $selectedAccount->maturity_date->format('d/m/Y') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-rose-600">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Instruksi Jatuh Tempo (ARO)</label>
                                                <input type="text" value="{{ $selectedAroLabel }}" class="w-full px-5 py-3.5 bg-emerald-50 border border-emerald-100 rounded-2xl font-black text-sm text-emerald-600 uppercase tracking-tighter">
                                            </div>
                                            <div class="col-span-2 space-y-2">
                                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Rekening Pencairan Bunga & Pokok</label>
                                                <input type="text" value="{{ $selectedAccount->savingAccount?->account_no ?? 'TUNAI / CASH' }} - {{ $selectedAccount->savingAccount?->product->name ?? 'TANPA TARGET TABUNGAN' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm uppercase">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                    </div>

                    <!-- Rekening Pencairan / Autodebet Simpanan Berjangka -->
                    @if($selectedAccount->savingAccount)
                    <div class="mb-6">
                        <div class="border-b border-slate-200 pb-2 mb-6 text-amber-600 flex items-center justify-between">
                            <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">savings</span>
                                Rekening Pencairan / Autodebet Simpanan Berjangka
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. Rekening</label>
                                <input type="text" readonly value="{{ $selectedAccount->savingAccount->account_no }}" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-amber-600">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Cabang</label>
                                <input type="text" readonly value="{{ $selectedAccount->savingAccount->branch->name ?? '-' }}" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status</label>
                                <input type="text" readonly value="{{ $selectedAccount->savingAccount->status }}" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm {{ $selectedAccount->savingAccount->status === 'ACTIVE' ? 'text-emerald-600' : 'text-rose-600' }}">
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- BOTTOM GRID: Interest Schedule & History -->
                    <div class="grid grid-cols-12 gap-6">
                        <!-- Table: Interest Schedule -->
                        <div class="col-span-12 lg:col-span-8 bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col">
                            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span class="material-symbols-outlined text-slate-900 text-xl">event_list</span>
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-900">Jadwal Pembayaran Bunga</h4>
                                </div>
                                <div class="px-3 py-1 bg-emerald-50 rounded-lg border border-emerald-100 flex items-center">
                                    <span class="text-[8px] font-black uppercase tracking-widest text-emerald-600 mr-2">Bunga Terbayar:</span>
                                    <span class="text-xs font-black text-emerald-700 tracking-tight">{{ $paidMonths }} / {{ $totalScheduleMonths ?: $selectedAccount->tenor }} Bln</span>
                                </div>
                            </div>
                            <div class="overflow-x-auto flex-grow custom-scrollbar">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-white sticky top-0 z-10 shadow-sm border-b border-slate-100">
                                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center w-16">Bln</th>
                                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Tgl Cair</th>
                                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Kotor</th>
                                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Pajak</th>
                                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Bunga Bersih</th>
                                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach($schedules as $row)
                                            @php 
                                                // Handle both model objects and fallback stdClass objects from component
                                                $isPaid = isset($row->status) && $row->status === 'PAID';
                                                
                                                // If we have paidMonths count (for fallback), use it
                                                if (!$isPaid && isset($paidMonths) && $row->month_index <= $paidMonths) {
                                                    $isPaid = true;
                                                }
                                            @endphp
                                            <tr class="hover:bg-slate-50/50 transition-colors {{ $isPaid ? 'bg-emerald-50/10' : '' }}">
                                                <td class="py-4 px-6 text-[11px] font-black text-slate-400 text-center">{{ $row->month_index }}</td>
                                                <td class="py-4 px-6 text-[11px] font-bold text-slate-900">{{ Carbon\Carbon::parse($row->schedule_date)->format('d/m/Y') }}</td>
                                                <td class="py-4 px-6 text-[11px] font-bold text-slate-600 text-right">Rp {{ number_format($row->gross_interest, 2, ',', '.') }}</td>
                                                <td class="py-4 px-6 text-[11px] font-bold text-rose-500 text-right">Rp {{ number_format($row->tax_amount ?? $row->tax, 2, ',', '.') }}</td>
                                                <td class="py-4 px-6 text-[11px] font-black text-emerald-600 text-right">Rp {{ number_format($row->net_interest, 2, ',', '.') }}</td>
                                                <td class="py-4 px-6 text-center">
                                                    @if($isPaid)
                                                        <span class="inline-flex items-center px-2 py-0.5 bg-emerald-500 text-white text-[8px] font-black rounded-lg tracking-widest uppercase shadow-sm shadow-emerald-500/10">PAID</span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 bg-slate-100 text-slate-400 text-[8px] font-black rounded-lg tracking-widest uppercase">DUE</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($schedules instanceof \Illuminate\Pagination\LengthAwarePaginator && $schedules->hasPages())
                                <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                                    {{ $schedules->links() }}
                                </div>
                            @endif
                        </div>

                        <!-- Table: Transaction History -->
                        <div class="col-span-12 lg:col-span-4 bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col">
                            <div class="p-6 border-b border-slate-100 bg-slate-50/50 space-y-4">
                                <div class="flex items-center space-x-3">
                                    <span class="material-symbols-outlined text-slate-900 text-xl">history</span>
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-900">Riwayat Mutasi</h4>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black uppercase tracking-widest text-slate-400">Dari</label>
                                        <input type="date" wire:model.live="mutation_date_from" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-[10px] font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[8px] font-black uppercase tracking-widest text-slate-400">Sampai</label>
                                        <input type="date" wire:model.live="mutation_date_to" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-[10px] font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900">
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow overflow-y-auto max-h-[400px] custom-scrollbar">
                                <div class="divide-y divide-slate-50">
                                    @forelse($history as $tx)
                                        <div class="p-5 hover:bg-slate-50/50 transition-colors space-y-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div @class([
                                                        'w-8 h-8 rounded-lg flex items-center justify-center',
                                                        'bg-emerald-50 text-emerald-600' => in_array($tx->type, ['PLACEMENT', 'INTEREST_PAYMENT', 'ROLLOVER']),
                                                        'bg-rose-50 text-rose-600' => $tx->type === 'WITHDRAWAL',
                                                        'bg-slate-50 text-slate-400' => !in_array($tx->type, ['PLACEMENT', 'WITHDRAWAL', 'INTEREST_PAYMENT', 'ROLLOVER'])
                                                    ])>
                                                        <span class="material-symbols-outlined text-sm">
                                                            @if($tx->type === 'PLACEMENT') add_circle
                                                            @elseif($tx->type === 'WITHDRAWAL') payments
                                                            @elseif($tx->type === 'INTEREST_PAYMENT') account_balance_wallet
                                                            @elseif($tx->type === 'ROLLOVER') sync
                                                            @else history
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <span class="text-[9px] font-black text-slate-400 tracking-widest uppercase">{{ $tx->transaction_date->format('d/m/y H:i') }}</span>
                                                        <p class="text-[10px] text-slate-700 font-bold uppercase tracking-tight">{{ str_replace('_', ' ', $tx->type) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pl-11 space-y-2">
                                                <p class="text-[10px] text-slate-400 font-medium italic line-clamp-2 pr-4 leading-relaxed">
                                                    @if($tx->type === 'INTEREST_PAYMENT' && $tx->interestSchedule)
                                                        Bunga Simpanan Berjangka Bulan ke-{{ $tx->interestSchedule->month_index }} — {{ $selectedAccount->account_no }}
                                                    @else
                                                        {{ $tx->description }}
                                                    @endif
                                                </p>
                                                <p @class([
                                                    'text-xs font-black tracking-tight',
                                                    'text-emerald-600' => in_array($tx->type, ['PLACEMENT', 'INTEREST_PAYMENT', 'ROLLOVER']),
                                                    'text-rose-600' => $tx->type === 'WITHDRAWAL'
                                                ])>
                                                    {{ in_array($tx->type, ['PLACEMENT', 'INTEREST_PAYMENT', 'ROLLOVER']) ? '+' : '-' }}
                                                    Rp {{ number_format($tx->amount, 2, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="py-20 text-center text-slate-300">
                                            <p class="text-[10px] font-black uppercase tracking-widest italic">Belum ada mutasi</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            @if($history->hasPages())
                                <div class="p-4 border-t border-slate-50 bg-slate-50/30">
                                    {{ $history->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
