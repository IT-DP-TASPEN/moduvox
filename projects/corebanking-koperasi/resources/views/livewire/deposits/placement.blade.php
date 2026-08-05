<div class="p-0 print:bg-white print:m-0">
    <div class="print:hidden">
        <x-header title="Penempatan Simpanan Berjangka Baru" subtitle="Penempatan simpanan berjangka baru untuk anggota"
            :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
            <x-slot name="actions">
                <a href="{{ route('deposits.inquiry') }}"
                    class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm"
                    wire:navigate>
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Kembali ke Daftar</span>
                </a>
            </x-slot>
        </x-header>
    </div>

    <div class="p-10 pb-20 print:p-0">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 print:block">

            <!-- FORM SECTIONS -->
            <div class="lg:col-span-2 print:hidden">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                    <form wire:submit.prevent="save">
                        <div class="p-8 bg-white space-y-12">

                            <!-- SECTION 1: Cek Data Anggota -->
                            <div>
                                <div class="border-b border-slate-200 pb-2 mb-6">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span
                                            class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">person_search</span>
                                        Cek Data Anggota (CIF)</p>
                                </div>

                                <div class="col-span-2 space-y-4" wire:key="container-cif-search">
                                    <div class="relative">
                                        <span
                                            class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                                        <input wire:model.live.debounce.300ms="searchCif" type="text"
                                            class="w-full pl-12 pr-6 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900"
                                            placeholder="Cari Nama Anggota atau Nomor CIF (Min 3 Karakter)...">

                                        @if(count($cifResults) > 0)
                                            <div
                                                class="absolute z-10 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                                                @foreach($cifResults as $res)
                                                    <button type="button" wire:click="selectCif({{ $res->id }})"
                                                        class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-all border-b border-slate-50 last:border-0 group">
                                                        <div class="text-left">
                                                            <p class="text-xs font-black text-slate-900 uppercase">
                                                                {{ $res->name }}</p>
                                                            <p class="text-[10px] text-slate-500 font-bold tracking-widest">
                                                                {{ $res->cif_no }} | {{ $res->nik }}</p>
                                                        </div>
                                                        <span
                                                            class="material-symbols-outlined text-slate-300 group-hover:text-slate-900 transition-all">add_circle</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                        @error('cif_id') <span
                                            class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    @if($selectedCif)
                                        <div wire:key="selected-cif-{{ $selectedCif->id }}"
                                            class="p-6 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between animate-in zoom-in-95 duration-300">
                                            <div class="flex items-center space-x-4">
                                                <div
                                                    class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                                                    <span class="material-symbols-outlined">person</span>
                                                </div>
                                                <div class="space-y-0.5">
                                                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight">
                                                        {{ $selectedCif->name }}</h4>
                                                    <p
                                                        class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">
                                                        {{ $selectedCif->cif_no }} • NIK: {{ $selectedCif->nik }}</p>
                                                </div>
                                            </div>
                                            <button type="button" wire:click="$set('selectedCif', null)"
                                                class="text-[10px] font-black uppercase tracking-widest text-rose-500 hover:text-rose-600 underline">Ganti
                                                Anggota</button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- SECTION 2: Produk & Penempatan -->
                            <div>
                                <div class="border-b border-slate-200 pb-2 mb-6">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span
                                            class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">inventory_2</span>
                                        Produk & Penempatan</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2" wire:key="container-produk">
                                        <label
                                            class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Produk
                                            Simpanan Berjangka <span class="text-rose-500">*</span></label>
                                        <select wire:model.live="deposit_product_id"
                                            class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            <option value="">Pilih Produk Simpanan Berjangka...</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->product_code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('deposit_product_id') <span
                                            class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    @if($selectedProduct)
                                        <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-3" wire:key="selected-product-rules-{{ $selectedProduct->id }}">
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Nominal Produk</p>
                                                <p class="mt-1 text-[11px] font-black text-slate-900">
                                                    Rp {{ number_format((float) $selectedProduct->min_amount, 0, ',', '.') }}
                                                    -
                                                    {{ $selectedProduct->max_amount ? 'Rp ' . number_format((float) $selectedProduct->max_amount, 0, ',', '.') : 'Tanpa batas' }}
                                                </p>
                                            </div>
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Tenor Produk</p>
                                                <p class="mt-1 text-[11px] font-black text-slate-900">
                                                    {{ $selectedProduct->min_term }}
                                                    -
                                                    {{ $selectedProduct->max_term ?: 'Tanpa batas' }}
                                                    {{ str_starts_with(strtoupper((string) $selectedProduct->term_unit), 'DAY') ? 'hari' : 'bulan' }}
                                                </p>
                                            </div>
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Bunga Produk</p>
                                                <p class="mt-1 text-[11px] font-black text-slate-900">
                                                    {{ format_percent($selectedProduct->min_interest_rate) }}
                                                    -
                                                    {{ format_percent($selectedProduct->max_interest_rate) }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="space-y-2" wire:key="container-tanggal">
                                        <label
                                            class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal
                                            Penempatan <span class="text-rose-500">*</span></label>
                                        <input wire:model.live="placement_date" type="date"
                                            class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                        @error('placement_date') <span
                                            class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="space-y-2 col-span-2" wire:key="container-nominal">
                                        <label
                                            class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nominal
                                            Pokok Penempatan <span class="text-rose-500">*</span></label>
                                        <div class="relative" x-data="{ 
                                            display: '',
                                            raw: @entangle('amount').live,
                                            digits(v) { let s = (v ?? '').toString(); if (/^\d+\.\d{1,2}$/.test(s)) s = s.split('.')[0]; return s.replace(/\D/g, ''); },
                                            format(v) { return this.digits(v).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
                                            init() { this.display = this.format(this.raw || 0); this.$watch('raw', v => this.display = this.format(v || 0)); }
                                        }">
                                            <div
                                                class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">
                                                Rp</div>
                                            <input type="text" x-model="display"
                                                @input="display = format($event.target.value); raw = digits($event.target.value)"
                                                class="w-full pl-12 pr-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-lg text-slate-900"
                                                placeholder="0">
                                        </div>
                                        @error('amount') <span
                                            class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="space-y-2" wire:key="container-tenor">
                                        <label
                                            class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tenor
                                            ({{ $selectedProduct && str_starts_with(strtoupper((string) $selectedProduct->term_unit), 'DAY') ? 'Hari' : 'Bulan' }}) <span class="text-rose-500">*</span></label>
                                        <input wire:model.live="tenor" type="number"
                                            class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                        @error('tenor') <span
                                            class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="space-y-2" wire:key="container-bunga">
                                        <label
                                            class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Suku
                                            Bunga (% p.a) <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <input wire:model.live="interest_rate" type="number" step="0.01"
                                                class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700 pr-10">
                                            <span
                                                class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">%</span>
                                        </div>
                                        @error('interest_rate') <span
                                            class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="space-y-2 col-span-2" wire:key="container-metode">
                                        <label
                                            class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Metode
                                            Bunga <span class="text-rose-500">*</span></label>
                                        <select wire:model.live="interest_calculation_type"
                                            class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            <option value="MONTHLY">Bunga Tetap Bulanan (Flat / 12)</option>
                                            <option value="DAILY">Bunga Harian (Aktual Hari / 360)</option>
                                        </select>
                                        @error('interest_calculation_type') <span
                                            class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="space-y-2 col-span-2" wire:key="container-alasan">
                                        <label
                                            class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Alasan
                                            Penempatan / Tujuan Simpanan Berjangka</label>
                                        <textarea wire:model="reason" rows="2"
                                            class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700"
                                            placeholder="Jelaskan tujuan atau alasan penempatan simpanan berjangka ini..."></textarea>
                                        @error('reason') <span
                                            class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                </div>
                            </div>

                            <!-- SECTION 3: Admin & Rekening -->
                            <div>
                                <div class="border-b border-slate-200 pb-2 mb-6">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span
                                            class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">admin_panel_settings</span>
                                        Administrasi & Rekening</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2 col-span-2" wire:key="container-saluran">
                                        <label
                                            class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Saluran Setoran <span class="text-rose-500">*</span></label>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            @foreach(['INTERNAL' => 'Setoran dari Simpanan', 'CASH' => 'Setoran Tunai', 'ABA' => 'Setoran ABA'] as $val => $label)
                                                <label wire:key="channel-{{ $val }}"
                                                    class="flex items-center p-4 rounded-2xl border-2 cursor-pointer transition-all group {{ $deposit_channel === $val ? 'border-slate-900 bg-slate-50 shadow-sm' : 'border-slate-100 hover:border-slate-200 hover:bg-slate-50/50' }}">
                                                    <input type="radio" wire:model.live="deposit_channel"
                                                        value="{{ $val }}" class="sr-only">
                                                    <div
                                                        class="w-10 h-10 rounded-xl flex items-center justify-center mr-4 shrink-0 {{ $deposit_channel === $val ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200' }}">
                                                        <span
                                                            class="material-symbols-outlined text-sm">{{ $val === 'CASH' ? 'payments' : ($val === 'ABA' ? 'account_balance' : 'savings') }}</span>
                                                    </div>
                                                    <div class="flex-grow">
                                                        <p class="text-[10px] font-black text-slate-900 uppercase tracking-tight leading-none mb-1">{{ $label }}</p>
                                                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $val === 'CASH' ? 'Kas Kantor' : ($val === 'ABA' ? 'Transfer Bank' : 'Rekening CIF') }}</p>
                                                    </div>
                                                    @if($deposit_channel === $val)
                                                        <span
                                                            class="material-symbols-outlined text-slate-900 text-xl ml-2 shrink-0">check_circle</span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('deposit_channel') <span
                                            class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- SUB-AKUN BANK (muncul saat ABA dipilih) --}}
                                    @if($deposit_channel === 'ABA' && $abaCoas->isNotEmpty())
                                        <div class="space-y-2 col-span-2 animate-in fade-in slide-in-from-top-2 duration-300" wire:key="container-bank-coa">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">
                                                Pilih Rekening Bank (ABA) <span class="text-rose-500">*</span>
                                            </label>
                                            <div class="grid grid-cols-1 gap-2">
                                                @foreach($abaCoas as $bankCoa)
                                                    <label wire:key="bank-coa-{{ $bankCoa->id }}"
                                                        class="flex items-center p-3.5 rounded-xl border-2 cursor-pointer transition-all group {{ $bank_coa_id == $bankCoa->id ? 'border-blue-600 bg-blue-50' : 'border-slate-100 hover:border-slate-200 hover:bg-slate-50' }}">
                                                        <input type="radio" wire:model.live="bank_coa_id"
                                                            value="{{ $bankCoa->id }}" class="sr-only">
                                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 shrink-0 {{ $bank_coa_id == $bankCoa->id ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-400' }}">
                                                            <span class="material-symbols-outlined text-sm">account_balance</span>
                                                        </div>
                                                        <div class="flex-grow">
                                                            <p class="text-[10px] font-black text-slate-900 uppercase tracking-tight leading-none">{{ $bankCoa->name }}</p>
                                                            <p class="text-[9px] text-slate-400 font-bold tracking-widest mt-0.5">{{ $bankCoa->coa_code }}</p>
                                                        </div>
                                                        @if($bank_coa_id == $bankCoa->id)
                                                            <span class="material-symbols-outlined text-blue-600 text-lg ml-2 shrink-0">check_circle</span>
                                                        @endif
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('bank_coa_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>@enderror
                                        </div>
                                    @endif

                                    {{-- SUB-AKUN KAS (muncul saat CASH dipilih & ada lebih dari 1 opsi) --}}
                                    @if($deposit_channel === 'CASH' && $cashCoas->count() > 1)
                                        <div class="space-y-2 col-span-2 animate-in fade-in slide-in-from-top-2 duration-300" wire:key="container-cash-coa">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">
                                                Pilih Rekening Kas <span class="text-rose-500">*</span>
                                            </label>
                                            <div class="grid grid-cols-2 gap-2">
                                                @foreach($cashCoas as $kasCoa)
                                                    <label wire:key="cash-coa-{{ $kasCoa->id }}"
                                                        class="flex items-center p-3.5 rounded-xl border-2 cursor-pointer transition-all group {{ $cash_coa_id == $kasCoa->id ? 'border-slate-900 bg-slate-50 shadow-sm' : 'border-slate-100 hover:border-slate-200' }}">
                                                        <input type="radio" wire:model.live="cash_coa_id"
                                                            value="{{ $kasCoa->id }}" class="sr-only">
                                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 shrink-0 {{ $cash_coa_id == $kasCoa->id ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400' }}">
                                                            <span class="material-symbols-outlined text-sm">payments</span>
                                                        </div>
                                                        <div class="flex-grow">
                                                            <p class="text-[10px] font-black text-slate-900 uppercase tracking-tight leading-none">{{ $kasCoa->name }}</p>
                                                            <p class="text-[9px] text-slate-400 font-bold tracking-widest mt-0.5">{{ $kasCoa->coa_code }}</p>
                                                        </div>
                                                        @if($cash_coa_id == $kasCoa->id)
                                                            <span class="material-symbols-outlined text-slate-900 text-lg ml-2 shrink-0">check_circle</span>
                                                        @endif
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('cash_coa_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>@enderror
                                        </div>
                                    @endif

                                    @if($deposit_channel === 'INTERNAL')
                                        <div class="space-y-2 col-span-2 animate-in fade-in slide-in-from-top-2 duration-300" wire:key="container-source-saving">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">
                                                Pilih Rekening Simpanan Sumber Dana <span class="text-rose-500">*</span>
                                            </label>
                                            <div class="grid grid-cols-1 gap-2">
                                                @forelse($savingAccounts as $acc)
                                                    <label wire:key="source-saving-{{ $acc->id }}"
                                                        class="flex items-center p-3.5 rounded-xl border-2 cursor-pointer transition-all group {{ $source_saving_account_id == $acc->id ? 'border-slate-900 bg-slate-50 shadow-sm' : 'border-slate-100 hover:border-slate-200 hover:bg-slate-50' }}">
                                                        <input type="radio" wire:model.live="source_saving_account_id" value="{{ $acc->id }}" class="sr-only">
                                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 shrink-0 {{ $source_saving_account_id == $acc->id ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400' }}">
                                                            <span class="material-symbols-outlined text-sm">savings</span>
                                                        </div>
                                                        <div class="flex-grow">
                                                            <p class="text-[10px] font-black text-slate-900 uppercase tracking-tight leading-none">{{ $acc->account_no }} - {{ $acc->product->name }}</p>
                                                            <p class="text-[9px] text-slate-400 font-bold tracking-widest mt-0.5">Saldo Efektif: Rp {{ number_format($acc->effective_balance, 2, ',', '.') }}</p>
                                                        </div>
                                                        @if($source_saving_account_id == $acc->id)
                                                            <span class="material-symbols-outlined text-slate-900 text-lg ml-2 shrink-0">check_circle</span>
                                                        @endif
                                                    </label>
                                                @empty
                                                    <div class="p-4 rounded-xl border border-rose-100 bg-rose-50 text-[10px] font-black text-rose-600 uppercase tracking-widest">
                                                        Tidak ada rekening simpanan aktif untuk CIF ini.
                                                    </div>
                                                @endforelse
                                            </div>
                                            @error('source_saving_account_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>@enderror
                                        </div>
                                    @endif

                                    <div class="space-y-2 col-span-2" wire:key="container-bilyet">
                                        <label
                                            class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Alokasi
                                            Bilyet Fisik <span class="text-rose-500">*</span></label>
                                        <select wire:model="deposit_bilyet_id"
                                            class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            <option value="">Pilih Nomor Seri Bilyet Fisik...</option>
                                            @foreach($availableBilyets as $b)
                                                <option value="{{ $b->id }}">{{ $b->kode_bilyet }} (Seri:
                                                    {{ $b->bilyet_number }}) - Tersedia</option>
                                            @endforeach
                                        </select>
                                        @error('deposit_bilyet_id') <span
                                            class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="space-y-4 col-span-2" wire:key="container-aro">
                                        <label
                                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tipe
                                            ARO (Auto Roll-Over) <span class="text-rose-500">*</span></label>
                                        <div class="space-y-3">
                                            <label class="flex items-center space-x-3 cursor-pointer group">
                                                <input type="radio" wire:model.live="rollover_type" value="NONE"
                                                    class="w-5 h-5 text-slate-900 border-slate-200 focus:ring-slate-900 focus:ring-2">
                                                <div class="text-xs">
                                                    <span
                                                        class="font-bold text-slate-900 block group-hover:text-slate-600">Non-ARO
                                                        (Cair Ke Rekening)</span>
                                                    <span class="text-[10px] font-medium text-slate-500">Pokok & Bunga
                                                        ditransfer cair saat jatuh tempo</span>
                                                </div>
                                            </label>
                                            <label class="flex items-center space-x-3 cursor-pointer group">
                                                <input type="radio" wire:model.live="rollover_type" value="PRINCIPAL"
                                                    class="w-5 h-5 text-slate-900 border-slate-200 focus:ring-slate-900 focus:ring-2">
                                                <div class="text-xs">
                                                    <span
                                                        class="font-bold text-slate-900 block group-hover:text-slate-600">ARO
                                                        Pokok Saja</span>
                                                    <span class="text-[10px] font-medium text-slate-500">Hanya Pokok
                                                        yang diperpanjang, Bunga dicairkan</span>
                                                </div>
                                            </label>
                                            <label class="flex items-center space-x-3 cursor-pointer group">
                                                <input type="radio" wire:model.live="rollover_type"
                                                    value="PRINCIPAL_INTEREST"
                                                    class="w-5 h-5 text-slate-900 border-slate-200 focus:ring-slate-900 focus:ring-2">
                                                <div class="text-xs">
                                                    <span
                                                        class="font-bold text-slate-900 block group-hover:text-slate-600">ARO
                                                        Pokok + Bunga</span>
                                                    <span class="text-[10px] font-medium text-slate-500">Otomatis tambah
                                                        pokok saat diperpanjang</span>
                                                </div>
                                            </label>
                                        </div>
                                        @error('rollover_type') <span
                                            class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="space-y-2 animate-fade-in col-span-2" wire:key="saving-account-container">
                                        <label
                                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Rekening
                                            Simpanan (Tujuan Pencairan Saldo Bunga/Pokok) <span
                                                class="text-rose-500">*</span></label>
                                        <select wire:model="saving_account_id"
                                            class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            @if(!$cif_id)
                                                <option value="">Pilih Anggota Terlebih Dahulu</option>
                                            @elseif(count($savingAccounts) === 0)
                                                <option value="">Tidak ada rekening simpanan aktif</option>
                                            @else
                                                <option value="">Pilih Rekening Simpanan...</option>
                                                @foreach($savingAccounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_no }} -
                                                        {{ $acc->product->name }} (Sisa Saldo: Rp
                                                        {{ number_format($acc->balance, 2, ',', '.') }})</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <p class="text-[10px] text-slate-400 font-bold ml-1 mt-1 leading-relaxed">
                                            Rekening ini digunakan sebagai wadah/penampung saat simpanan berjangka jatuh tempo
                                            (Non-ARO) maupun sebagai tujuan pencairan bunga bulanan (ARO Pokok).
                                        </p>
                                        @error('saving_account_id') <span
                                            class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="space-y-2 col-span-2" wire:key="container-marketing">
                                        <label
                                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Marketing
                                            (Opsional)</label>
                                        <select wire:model="marketing_id"
                                            class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            <option value="">Pilih Marketing yang Membawa Nasabah...</option>
                                            @foreach($marketings as $mk)
                                                <option value="{{ $mk->id }}">{{ $mk->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Footer -->
                        <div class="px-8 py-8 border-t border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <div class="max-w-md">
                                @if($errors->any())
                                    <div class="flex items-center text-rose-500">
                                        <span class="material-symbols-outlined text-sm mr-2 text-rose-600">error</span>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Gagal: {{ $errors->first() }}</span>
                                    </div>
                                @elseif (session()->has('success'))
                                    <div class="flex items-center text-emerald-600">
                                        <span class="material-symbols-outlined text-sm mr-2">check_circle</span>
                                        <span class="text-[10px] font-black uppercase tracking-widest">{{ session('success') }}</span>
                                    </div>
                                @else
                                    <div class="space-y-1">
                                        <h4 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Konfirmasi Data</h4>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed">Pastikan semua informasi sudah benar sebelum membuka rekening baru.</p>
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center space-x-4">
                                <button type="submit"
                                    class="px-10 py-4 bg-slate-900 text-white hover:shadow-xl hover:shadow-slate-900/20 font-black text-[10px] uppercase tracking-[0.2em] rounded-2xl transition-all active:scale-95 flex items-center group">
                                    <div wire:loading wire:target="save"
                                        class="w-4 h-4 border-2 border-slate-400 border-t-white rounded-full animate-spin mr-3">
                                    </div>
                                    <span wire:loading.remove wire:target="save"
                                        class="material-symbols-outlined text-sm mr-3 group-hover:rotate-12 transition-transform">verified_user</span>
                                    <span>Buka Rekening</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PROJECTION PANEL (Right Sidebar) -->
            <div class="lg:col-span-1 print:block print:w-full lg:sticky lg:top-8 self-start space-y-6">
                @if($this->projection)
                    <div
                        class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden animate-slide-up print:border-none print:shadow-none print:rounded-none">
                        <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="material-symbols-outlined text-emerald-500 text-3xl">insights</span>
                                <div>
                                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Proyeksi Hasil
                                        Simpanan Berjangka</h3>
                                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Kalkulasi
                                        Cepat Pendapatan</p>
                                </div>
                            </div>
                            <button onclick="window.print()" type="button"
                                class="print:hidden flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-3 py-1.5 rounded-lg font-bold text-[10px] hover:bg-slate-50 transition-all shadow-sm">
                                <span class="material-symbols-outlined text-[14px]">print</span>
                                <span>Cetak</span>
                            </button>
                        </div>

                        <div class="p-6 space-y-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Nilai Jatuh
                                        Tempo</p>
                                    <p class="text-[10px] text-slate-400 font-medium tracking-tight">Maturity Date:
                                        {{ \Carbon\Carbon::parse($this->projection['maturity_date'])->format('d M Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xl font-black text-slate-900">Rp
                                        {{ number_format($this->projection['total_payout'], 2, ',', '.') }}</p>
                                </div>
                            </div>

                            <hr class="border-dashed border-slate-200">

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-500">Pokok Penempatan</p>
                                    <p class="text-xs font-black text-slate-900">Rp
                                        {{ number_format((float) $this->amount, 2, ',', '.') }}</p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-500">Est. Bunga Kotor</p>
                                    <p class="text-xs font-black text-slate-600">+ Rp
                                        {{ number_format($this->projection['gross_interest'], 2, ',', '.') }}</p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-500">Pajak Bunga</p>
                                    <p class="text-xs font-black text-rose-500">- Rp
                                        {{ number_format($this->projection['tax_amount'], 2, ',', '.') }}</p>
                                </div>
                            </div>

                            <div
                                class="p-4 bg-emerald-50 rounded-xl flex items-center justify-between border border-emerald-100">
                                <p class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">Bunga Bersih
                                </p>
                                <p class="text-sm font-black text-emerald-600">Rp
                                    {{ number_format($this->projection['net_interest'], 2, ',', '.') }}</p>
                            </div>

                        </div>

                        <div class="p-4 border-t border-slate-200 bg-slate-50 text-center">
                            <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest leading-relaxed">
                                Peringatan: Proyeksi ini merupakan hitungan simulasi. Tidak mengikat dengan keadaan nyata
                                saat penutupan rekening.</p>
                        </div>
                    </div>

                    <!-- SCHEDULE TABLE (IN SIDEBAR) -->
                    @if(!empty($this->projection['schedule']))
                        <div class="mt-6 animate-slide-up">
                            <div class="border-b border-slate-200 pb-2 mb-4">
                                <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest">
                                    <span
                                        class="material-symbols-outlined text-xs align-middle mr-1 text-slate-400">calendar_month</span>
                                    Jadwal Bunga
                                </p>
                            </div>

                            <div
                                class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm print:border-none print:shadow-none print:rounded-none">
                                <div
                                    class="max-h-[32rem] overflow-y-auto overflow-x-auto print:max-h-none print:overflow-visible custom-scrollbar">
                                    <table class="w-full text-left border-collapse min-w-[500px]">
                                        <thead class="bg-slate-50 border-b border-slate-200 sticky top-0 z-10 print:static">
                                            <tr>
                                                <th
                                                    class="py-2.5 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest">
                                                    Bulan</th>
                                                <th
                                                    class="py-2.5 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest">
                                                    Tgl</th>
                                                <th
                                                    class="py-2.5 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-center">
                                                    Hr</th>
                                                <th
                                                    class="py-2.5 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">
                                                    Kotor</th>
                                                <th
                                                    class="py-2.5 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">
                                                    Pajak</th>
                                                <th
                                                    class="py-2.5 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right border-l border-slate-100 bg-emerald-50/30">
                                                    Bersih</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($this->projection['schedule'] as $item)
                                                <tr class="hover:bg-slate-50 transition-colors print:break-inside-avoid">
                                                    <td class="py-2.5 px-3 text-[10px] font-bold text-slate-900">
                                                        {{ $item['month'] }}</td>
                                                    <td class="py-2.5 px-3 text-[10px] font-bold text-slate-500">
                                                        {{ \Carbon\Carbon::parse($item['date'])->format('d/m/y') }}</td>
                                                    <td class="py-2.5 px-3 text-[10px] font-bold text-slate-400 text-center">
                                                        {{ $item['days'] ?? '-' }}</td>
                                                    <td class="py-2.5 px-3 text-[10px] font-bold text-slate-700 text-right">
                                                        Rp{{ number_format($item['gross_interest'], 2, ',', '.') }}</td>
                                                    <td class="py-2.5 px-3 text-[10px] font-bold text-rose-500 text-right">
                                                        Rp{{ number_format($item['tax'], 2, ',', '.') }}</td>
                                                    <td
                                                        class="py-2.5 px-3 text-[10px] font-black text-emerald-600 text-right border-l border-emerald-50 bg-emerald-50/10">
                                                        Rp{{ number_format($item['net_interest'], 2, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div
                        class="bg-slate-50/50 rounded-[2rem] border border-dashed border-slate-200 h-64 flex flex-col items-center justify-center text-center p-8">
                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-4 animate-bounce">insights</span>
                        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Proyeksi Menunggu Data
                        </h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed">Masukkan
                            Produk, Nominal & Tenor secara lengkap untuk melihat perhitungan dan proyeksi keuntungan.</p>
                    </div>
                @endif
            </div>


        </div>
    </div>
</div>
