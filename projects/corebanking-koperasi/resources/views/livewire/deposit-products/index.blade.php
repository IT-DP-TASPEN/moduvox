<div class="min-h-screen">
    <!-- Header Section -->
    <x-header title="Produk Simpanan Berjangka" subtitle="Konfigurasi parameter simpanan berjangka dan pemetaan akuntansi"
        :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot:actions>
            @if($viewMode === 'list')
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm">search</span>
                        <input wire:model.live="search" type="text" placeholder="Cari produk..."
                            class="pl-10 pr-4 py-2 bg-surface border border-surface-dim rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all w-48 font-medium">
                    </div>
                    @can('deposit-products.create')
                        <button wire:click="create"
                            class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-xl hover:bg-primary-dim transition-all shadow-sm active:scale-95">
                            <span class="material-symbols-outlined text-sm">add</span>
                            <span class="text-xs font-bold uppercase tracking-wider">Tambah Produk</span>
                        </button>
                    @endcan
                </div>
            @else
                <button wire:click="cancel"
                    class="flex items-center space-x-2 bg-surface border border-surface-dim text-primary px-4 py-2 rounded-xl hover:bg-surface-dim transition-all shadow-sm active:scale-95">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span class="text-xs font-bold uppercase tracking-wider">Kembali</span>
                </button>
            @endif
        </x-slot:actions>
    </x-header>

    <div class="p-8">
        @if (session()->has('success'))
            <div
                class="max-w-5xl mx-auto mb-6 bg-emerald-50 border border-emerald-100 p-4 rounded-2xl flex items-center space-x-3 text-emerald-700 animate-slide-up">
                <span class="material-symbols-outlined text-sm">check_circle</span>
                <span class="text-xs font-bold uppercase tracking-widest">{{ session('success') }}</span>
            </div>
        @endif

        @if($viewMode === 'list')
            <!-- Products List -->
            <div class="bg-white rounded-3xl shadow-sm border border-surface-dim overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr
                            class="bg-surface border-b border-surface-dim uppercase text-[10px] tracking-widest font-bold text-outline">
                            <th class="px-6 py-4">Kode</th>
                            <th class="px-6 py-4">Nama Produk</th>
                            <th class="px-6 py-4 text-center">Tenor (Min/Max)</th>
                            <th class="px-6 py-4 text-center">Bunga</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-dim">
                        @forelse($products as $p)
                            <tr class="hover:bg-surface/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <span
                                        class="bg-primary/5 text-primary text-[10px] font-black px-2 py-1 rounded-lg border border-primary/10 tracking-widest uppercase">{{ $p->product_code }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs font-bold text-primary">{{ $p->name }}</p>
                                    <p class="text-[9px] text-outline font-medium tracking-tight uppercase">
                                        {{ $p->interest_calculation_type === 'DAILY' ? 'HARIAN' : 'BULANAN' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-bold text-primary">{{ $p->min_term }} -
                                            {{ $p->max_term ?? '∞' }}</span>
                                            <span
                                                class="text-[8px] text-outline uppercase font-bold tracking-widest">{{ str_starts_with(strtoupper((string) $p->term_unit), 'DAY') ? 'HARI' : 'BULAN' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-bold text-primary">{{ format_percent($p->min_interest_rate) }} -
                                            {{ format_percent($p->max_interest_rate) }}</span>
                                        <span
                                            class="text-[8px] text-outline uppercase font-bold tracking-tighter">{{ strtolower($p->interest_period) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $p->is_active ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100' }}">
                                        {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @can('deposit-products.update')
                                        <button wire:click="edit({{ $p->id }})"
                                            class="p-2 text-outline hover:text-primary hover:bg-primary/10 rounded-xl transition-all"
                                            title="Edit">
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center space-y-3 opacity-30 text-outline">
                                        <span class="material-symbols-outlined text-5xl">inventory_2</span>
                                        <p class="text-xs font-bold uppercase tracking-widest">Belum ada produk simpanan berjangka
                                            terdaftar</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($products->hasPages())
                    <div class="px-6 py-4 bg-surface border-t border-surface-dim">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Create/Edit Form -->
            <div class="max-w-5xl mx-auto pb-20">
                <div class="bg-white rounded-[2.5rem] shadow-xl border border-surface-dim overflow-hidden">
                    <div class="p-12 space-y-12">

                        <!-- 1. General Information -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-3">
                                <span
                                    class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                    <span class="material-symbols-outlined text-sm">info</span>
                                </span>
                                <h3 class="text-xs font-black uppercase tracking-widest text-primary">Informasi Dasar</h3>
                            </div>
                            <div class="grid grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Kode
                                        Produk</label>
                                    <input wire:model="product_code" type="text"
                                        class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase"
                                        placeholder="e.g. DEP-01">
                                    @error('product_code') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Nama
                                        Produk</label>
                                    <input wire:model="name" type="text"
                                        class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all"
                                        placeholder="e.g. Simpanan Berjangka">
                                    @error('name') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div
                                class="flex items-center justify-between p-6 bg-surface/50 rounded-2xl border border-surface-dim">
                                <div>
                                    <h4 class="text-xs font-black text-primary uppercase">Status Aktif</h4>
                                    <p class="text-[10px] text-outline font-medium">Aktifkan produk agar bisa digunakan di
                                        pendaftaran simpanan berjangka baru</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-surface-dim rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary border border-surface-dim">
                                    </div>
                                </label>
                            </div>
                        </div>

                        <hr class="border-surface-dim">

                        <!-- 2. Deposit Rules (Tenor & Amount) -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-3">
                                <span
                                    class="w-8 h-8 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-600">
                                    <span class="material-symbols-outlined text-sm">schedule</span>
                                </span>
                                <h3 class="text-xs font-black uppercase tracking-widest text-primary">Aturan Tenor &
                                    Investasi</h3>
                            </div>
                            <div class="grid grid-cols-3 gap-8">
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Tenor
                                        Minimal</label>
                                    <input wire:model="min_term" type="number"
                                        class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                    @error('min_term') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Tenor
                                        Maksimal (Opsional)</label>
                                    <input wire:model="max_term" type="number"
                                        class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all"
                                        placeholder="Tanpa Limit">
                                    @error('max_term') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Satuan
                                        Waktu</label>
                                    <select wire:model="term_unit"
                                        class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none cursor-pointer">
                                        <option value="MONTH">BULAN (MONTH)</option>
                                        <option value="DAY">HARI (DAY)</option>
                                    </select>
                                    @error('term_unit') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-8 mt-6">
                                <div class="space-y-4" x-data="{ 
                                            display: '',
                                            raw: @entangle('min_amount'),
                                            digits(v) { let s = (v ?? '').toString(); if (/^\d+\.\d{1,2}$/.test(s)) s = s.split('.')[0]; return s.replace(/\D/g, ''); },
                                            format(v) { return this.digits(v).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
                                            init() { this.display = this.format(this.raw || 0); this.$watch('raw', v => this.display = this.format(v || 0)); }
                                        }">
                                    <div class="flex flex-col space-y-1.5">
                                        <label
                                            class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Nominal
                                            Investasi Minimal</label>
                                        <input type="text" x-model="display"
                                            @input="display = format($event.target.value); raw = digits($event.target.value)"
                                            class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                    </div>
                                    @error('min_amount') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="space-y-4" x-data="{ 
                                            display: '',
                                            raw: @entangle('max_amount'),
                                            digits(v) { let s = (v ?? '').toString(); if (/^\d+\.\d{1,2}$/.test(s)) s = s.split('.')[0]; return s.replace(/\D/g, ''); },
                                            format(v) { return v ? this.digits(v).replace(/\B(?=(\d{3})+(?!\d))/g, '.') : ''; },
                                            init() { this.display = this.format(this.raw || ''); this.$watch('raw', v => this.display = this.format(v || '')); }
                                        }">
                                    <div class="flex flex-col space-y-1.5">
                                        <label
                                            class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Nominal
                                            Maksimal (Opsional)</label>
                                        <input type="text" x-model="display"
                                            @input="display = format($event.target.value); raw = digits($event.target.value)"
                                            class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all"
                                            placeholder="Tanpa Limit">
                                    </div>
                                    @error('max_amount') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-surface-dim">

                        <!-- 3. Interest Rules -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-3">
                                <span
                                    class="w-8 h-8 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-600">
                                    <span class="material-symbols-outlined text-sm">percent</span>
                                </span>
                                <h3 class="text-xs font-black uppercase tracking-widest text-primary">Suku Bunga & Pajak
                                </h3>
                            </div>
                            <div class="grid grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Range
                                        Suku Bunga (% per tahun)</label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="relative">
                                            <input wire:model="min_interest_rate" type="number" step="0.01"
                                                class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all pr-12"
                                                placeholder="Min %">
                                            <span
                                                class="absolute right-5 top-1/2 -translate-y-1/2 text-primary font-black text-xs">%</span>
                                        </div>
                                        <div class="relative">
                                            <input wire:model="max_interest_rate" type="number" step="0.01"
                                                class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all pr-12"
                                                placeholder="Max %">
                                            <span
                                                class="absolute right-5 top-1/2 -translate-y-1/2 text-primary font-black text-xs">%</span>
                                        </div>
                                    </div>
                                    @error('min_interest_rate') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                    @error('max_interest_rate') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Metode
                                        Hitung Bunga</label>
                                    <select wire:model="interest_calculation_type"
                                        class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none cursor-pointer">
                                        <option value="MONTHLY">Bulanan (Flat / 12)</option>
                                        <option value="DAILY">Harian (Aktual Hari / 360)</option>
                                    </select>
                                    @error('interest_calculation_type') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Tarif Pajak Bunga (%)</label>
                                    <input wire:model="tax_rate" type="number" step="0.01" min="0" max="100"
                                        class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                    @error('tax_rate') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="p-6 bg-surface/50 rounded-2xl border border-surface-dim mt-4">
                                <div class="flex items-center justify-between">
                                    <div class="space-y-1">
                                        <label
                                            class="text-[10px] font-black text-outline uppercase tracking-widest text-outline/60">Periode
                                            Pembayaran Bunga</label>
                                        <p class="text-[9px] text-outline font-medium">Berapa kali bunga dibayarkan ke
                                            nasabah</p>
                                    </div>
                                    <select wire:model="interest_period"
                                        class="w-64 px-5 py-4 bg-white border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        <option value="MONTHLY">Bulanan (Monthly)</option>
                                        <option value="MATURITY">Saat Jatuh Tempo (At Maturity)</option>
                                    </select>
                                    @error('interest_period') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-surface-dim">

                        <!-- 4. Accounting Rules -->
                        <div class="space-y-8">
                            <div class="flex items-center space-x-3">
                                <span
                                    class="w-8 h-8 rounded-full bg-rose-500/10 flex items-center justify-center text-rose-600">
                                    <span class="material-symbols-outlined text-sm">account_balance</span>
                                </span>
                                <div>
                                    <h3 class="text-xs font-black uppercase tracking-widest text-primary">Accounting Rules
                                    </h3>
                                    <p class="text-[10px] text-outline font-medium">Tentukan akun akuntansi untuk setiap
                                        transaksi produk ini.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-x-12 gap-y-10">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun
                                        Kewajiban Simpanan Berjangka (Liability)<span class="text-red-500">*</span></label>
                                    <select wire:model="liability_coa_id"
                                        class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($liabilityCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('liability_coa_id') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun
                                        Beban Bunga (Expense)<span class="text-red-500">*</span></label>
                                    <select wire:model="interest_expense_coa_id"
                                        class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($expenseCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('interest_expense_coa_id') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun
                                        Kas Tunai (Asset)<span class="text-red-500">*</span></label>
                                    <select wire:model="kas_coa_id"
                                        class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($assetCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('kas_coa_id') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun
                                        Bank (Asset)<span class="text-red-500">*</span></label>
                                    <select wire:model="default_bank_coa_id"
                                        class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($assetCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('default_bank_coa_id') <span
                                        class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun
                                        Hutang Pajak Bunga (Liability)</label>
                                    <select wire:model="tax_liability_coa_id"
                                        class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($liabilityCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Section -->
                        <div class="mt-20 pt-10 border-t border-surface-dim flex justify-between items-center">
                            <div class="space-y-1">
                                <h4 class="text-xs font-black text-primary uppercase">Simpan Konfigurasi</h4>
                                <p class="text-[10px] text-outline font-medium">Perubahan yang diajukan akan masuk ke
                                    antrean persetujuan/approval.</p>
                            </div>
                            <div class="flex items-center space-x-6">
                                <div wire:loading class="flex items-center space-x-2 text-primary">
                                    <span class="material-symbols-outlined animate-spin text-sm">cycle</span>
                                    <span class="text-[9px] font-black uppercase tracking-widest">Memproses...</span>
                                </div>
                                <button wire:click="save"
                                    class="bg-primary hover:bg-primary-dim text-white px-12 py-5 rounded-[1.5rem] font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-primary/20 transition-all active:scale-95 flex items-center space-x-3">
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                    <span>Simpan Produk</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
