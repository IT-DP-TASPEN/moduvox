<div x-data="{ activeTab: 'general' }" class="min-h-screen">
    <!-- Header Section -->
    <x-header title="Produk Simpanan" subtitle="Konfigurasi parameter dan pemetaan akuntansi produk tabungan" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot:actions>
            @if($viewMode === 'list')
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm">search</span>
                        <input wire:model.live="search" type="text" placeholder="Cari produk..." class="pl-10 pr-4 py-2 bg-surface border border-surface-dim rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all w-48 font-medium">
                    </div>
                    @can('saving-products.create')
                    <button wire:click="create" class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-xl hover:bg-primary-dim transition-all shadow-sm active:scale-95">
                        <span class="material-symbols-outlined text-sm">add</span>
                        <span class="text-xs font-bold uppercase tracking-wider">Tambah Produk</span>
                    </button>
                    @endcan
                </div>
            @else
                <button wire:click="$set('viewMode', 'list')" class="flex items-center space-x-2 bg-surface border border-surface-dim text-primary px-4 py-2 rounded-xl hover:bg-surface-dim transition-all shadow-sm active:scale-95">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span class="text-xs font-bold uppercase tracking-wider">Kembali</span>
                </button>
            @endif
        </x-slot:actions>
    </x-header>

    <div class="p-8">
        @if (session()->has('success'))
            <div class="max-w-5xl mx-auto mb-6 bg-emerald-50 border border-emerald-100 p-4 rounded-2xl flex items-center space-x-3 text-emerald-700 animate-slide-up">
                <span class="material-symbols-outlined text-sm">check_circle</span>
                <span class="text-xs font-bold uppercase tracking-widest">{{ session('success') }}</span>
            </div>
        @endif

        @if($viewMode === 'list')
            <!-- Products List (Grid/Table) -->
            <div class="bg-white rounded-3xl shadow-sm border border-surface-dim overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-surface border-b border-surface-dim uppercase text-[10px] tracking-widest font-bold text-outline">
                            <th class="px-6 py-4">Kode</th>
                            <th class="px-6 py-4">Nama Produk</th>
                            <th class="px-6 py-4 text-center">Bunga</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-dim">
                        @forelse($products as $p)
                            <tr class="hover:bg-surface/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <span class="bg-primary/5 text-primary text-[10px] font-black px-2 py-1 rounded-lg border border-primary/10 tracking-widest uppercase">{{ $p->product_code }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs font-bold text-primary">{{ $p->name }}</p>
                                    <p class="text-[9px] text-outline font-medium tracking-tight uppercase">{{ str_replace('_', ' ', $p->interest_calculation_type) }} CALCULATION</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-bold text-primary">{{ format_percent($p->interest_rate) }}</span>
                                        <span class="text-[8px] text-outline uppercase font-bold tracking-tighter">per tahun</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $p->is_active ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100' }}">
                                        {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @can('saving-products.update')
                                    <button wire:click="edit({{ $p->id }})" class="p-2 text-outline hover:text-primary hover:bg-primary/10 rounded-xl transition-all mr-2" title="Edit">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center space-y-3 opacity-30">
                                        <span class="material-symbols-outlined text-5xl text-outline">inbox</span>
                                        <p class="text-xs font-bold text-outline">Belum ada produk terdaftar</p>
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
                                <span class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                    <span class="material-symbols-outlined text-sm">info</span>
                                </span>
                                <h3 class="text-xs font-black uppercase tracking-widest text-primary">Informasi Dasar</h3>
                            </div>
                            <div class="grid grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Kode Produk</label>
                                    <input wire:model="product_code" type="text" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase" placeholder="e.g. SIBER">
                                    @error('product_code') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Nama Produk</label>
                                    <input wire:model="name" type="text" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" placeholder="e.g. Simpanan Berjangka">
                                    @error('name') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-6 bg-surface/50 rounded-2xl border border-surface-dim">
                                <div>
                                    <h4 class="text-xs font-black text-primary uppercase">Status Aktif</h4>
                                    <p class="text-[10px] text-outline font-medium">Aktifkan produk agar bisa digunakan di pendaftaran simpanan baru</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                    <div class="w-11 h-6 bg-surface-dim rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary border border-surface-dim"></div>
                                </label>
                            </div>
                        </div>

                        <hr class="border-surface-dim">

                        <!-- 2. Interest Rules -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-3">
                                <span class="w-8 h-8 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-600">
                                    <span class="material-symbols-outlined text-sm">percent</span>
                                </span>
                                <h3 class="text-xs font-black uppercase tracking-widest text-primary">Aturan Bunga</h3>
                            </div>
                            <div class="grid grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Metode Hitung Bunga</label>
                                    <select wire:model="interest_calculation_type" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none cursor-pointer">
                                        <option value="DAILY">Daily Balance (Harian)</option>
                                        <option value="MINIMUM">Minimum Balance (Terendah)</option>
                                        <option value="AVERAGE">Average Balance (Rata-rata)</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Suku Bunga (% per tahun)</label>
                                    <div class="relative">
                                        <input wire:model="interest_rate" type="number" step="0.01" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all pr-12">
                                        <span class="absolute right-5 top-1/2 -translate-y-1/2 text-primary font-black text-xs">%</span>
                                    </div>
                                    @error('interest_rate') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2 border-t border-surface-dim pt-6 mt-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Periode Pembayaran Bunga</label>
                                    <select wire:model="interest_payment_period" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none cursor-pointer">
                                        <option value="MONTHLY">Bulanan (Setiap Akhir Bulan)</option>
                                        <option value="QUARTERLY">Triwulan (Setiap 3 Bulan)</option>
                                        <option value="ANNUALLY">Tahunan (Setiap Akhir Tahun)</option>
                                    </select>
                                </div>
                                <div class="space-y-2 border-t border-surface-dim pt-6 mt-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Dikenakan Pajak Bunga?</label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <select wire:model.live="has_tax_on_interest" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none cursor-pointer">
                                            <option value="1">Ya (Kena Pajak)</option>
                                            <option value="0">Tidak</option>
                                        </select>
                                        <div class="relative">
                                            <input wire:model="tax_rate" type="number" step="0.01" {{ $has_tax_on_interest ? '' : 'disabled' }} class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all pr-12 disabled:opacity-50 disabled:bg-slate-50">
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-primary font-black text-xs">%</span>
                                        </div>
                                    </div>
                                    @error('tax_rate') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-surface-dim">

                        <!-- 3. Limits & Fees -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-3">
                                <span class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                                    <span class="material-symbols-outlined text-sm">payments</span>
                                </span>
                                <h3 class="text-xs font-black uppercase tracking-widest text-primary">Limit & Biaya</h3>
                            </div>
                            <div class="grid grid-cols-2 gap-x-12 gap-y-10">
                                <!-- Col 1: Min Deposit -->
                                <div class="space-y-4" x-data="{ 
                                    display: '',
                                    raw: @entangle('min_initial_deposit'),
                                    format(v) { return v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
                                    init() { this.display = this.format(this.raw || 0); }
                                }">
                                    <div class="flex flex-col space-y-1.5">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Setoran Awal Minimum</label>
                                        <input type="text" 
                                               x-model="display"
                                               @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                               class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                    </div>
                                    @error('min_initial_deposit') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>

                                <!-- Col 2: Min Balance -->
                                <div class="space-y-4" x-data="{ 
                                    display: '',
                                    raw: @entangle('min_balance'),
                                    format(v) { return v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
                                    init() { this.display = this.format(this.raw || 0); }
                                }">
                                    <div class="flex flex-col space-y-1.5">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Saldo Mengendap Minimum</label>
                                        <input type="text" 
                                               x-model="display"
                                               @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                               class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                    </div>
                                    @error('min_balance') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>

                                <!-- Col 1: Max Balance -->
                                <div class="space-y-4" x-data="{ 
                                    display: '',
                                    raw: @entangle('max_balance'),
                                    format(v) { return v ? v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.') : ''; },
                                    init() { this.display = this.format(this.raw || ''); }
                                }">
                                    <div class="flex flex-col space-y-1.5">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Saldo Maksimum (Opsional)</label>
                                        <input type="text" 
                                               x-model="display"
                                               @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                               class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all"
                                               placeholder="Tanpa Limit">
                                    </div>
                                    @error('max_balance') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>

                                <!-- Col 2: Overdraft -->
                                <div class="space-y-4">
                                    <div class="flex flex-col space-y-1.5">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Dapat Overdraft (Saldo Minus)?</label>
                                        <select wire:model="has_overdraft" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none cursor-pointer">
                                            <option value="0">Tidak Diizinkan</option>
                                            <option value="1">Ya, Diizinkan</option>
                                        </select>
                                    </div>
                                    @error('has_overdraft') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>

                                <!-- Col 1: Admin Fee -->
                                <div class="space-y-4">
                                    <div class="grid grid-cols-5 gap-3">
                                        <div class="col-span-2 space-y-1.5">
                                            <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Biaya Admin?</label>
                                            <select wire:model.live="has_admin_fee" class="w-full px-4 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none cursor-pointer">
                                                <option value="0">TIDAK</option>
                                                <option value="1">YA</option>
                                            </select>
                                        </div>
                                        <div class="col-span-3 space-y-1.5" x-data="{ 
                                            display: '',
                                            raw: @entangle('admin_fee'),
                                            format(v) { return v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
                                            init() { this.display = this.format(this.raw || 0); }
                                        }">
                                            <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Nominal Admin</label>
                                            <input type="text" 
                                                   :disabled="!@entangle('has_admin_fee')" 
                                                   x-model="display"
                                                   @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                                   class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all disabled:opacity-20 disabled:grayscale">
                                        </div>
                                    </div>
                                    @error('admin_fee') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>

                                <!-- Col 2: Closing Fee -->
                                <div class="space-y-4">
                                    <div class="grid grid-cols-5 gap-3">
                                        <div class="col-span-2 space-y-1.5">
                                            <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Biaya Tutup?</label>
                                            <select wire:model.live="has_closing_fee" class="w-full px-4 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none cursor-pointer">
                                                <option value="0">TIDAK</option>
                                                <option value="1">YA</option>
                                            </select>
                                        </div>
                                        <div class="col-span-3 space-y-1.5" x-data="{ 
                                            display: '',
                                            raw: @entangle('closed_fee'),
                                            format(v) { return v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
                                            init() { this.display = this.format(this.raw || 0); }
                                        }">
                                            <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Nominal Tutup</label>
                                            <input type="text" 
                                                   :disabled="!@entangle('has_closing_fee')" 
                                                   x-model="display"
                                                   @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                                   class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all disabled:opacity-20 disabled:grayscale">
                                        </div>
                                    </div>
                                    @error('closed_fee') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- New Penalty Section -->
                            <div class="grid grid-cols-2 gap-x-12 pt-10 border-t border-surface-dim">
                                <div class="space-y-4" x-data="{ 
                                    display: '',
                                    raw: @entangle('min_balance_penalty'),
                                    format(v) { return v ? v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.') : ''; },
                                    init() { this.display = this.format(this.raw || 0); }
                                }">
                                    <div class="flex flex-col space-y-1.5">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Denda Saldo di Bawah Minimum</label>
                                        <input type="text" 
                                               x-model="display"
                                               @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                               class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                    </div>
                                    @error('min_balance_penalty') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-4">
                                    <div class="flex flex-col space-y-1.5">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Periode Penalti (Hari)</label>
                                        <input wire:model="min_balance_penalty_period" type="number" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" placeholder="Misal: 30">
                                    </div>
                                    @error('min_balance_penalty_period') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-surface-dim">

                        <!-- 4. Dormancy -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-3">
                                <span class="w-8 h-8 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-600">
                                    <span class="material-symbols-outlined text-sm">bedtime</span>
                                </span>
                                <h3 class="text-xs font-black uppercase tracking-widest text-primary">Status Dormant</h3>
                            </div>
                            <div class="p-6 bg-indigo-50/50 rounded-2xl border border-indigo-100 flex items-center justify-between">
                                <div>
                                    <h4 class="text-xs font-black text-indigo-900 uppercase">Otomatis Dormant</h4>
                                    <p class="text-[10px] text-indigo-800/70 font-medium">Ubah status menjadi dormant jika tidak ada transaksi dalam periode tertentu</p>
                                </div>
                                <input type="checkbox" wire:model="has_automatic_dormant" class="w-6 h-6 rounded-lg border-indigo-100 text-indigo-600 focus:ring-indigo-500">
                            </div>

                            <div class="grid grid-cols-2 gap-8" x-show="@entangle('has_automatic_dormant')" x-transition>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Batas Bulan (Tanpa Transaksi)</label>
                                    <input wire:model="no_transaction_monthly_terms" type="number" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" placeholder="Misal: 6 bulan">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Masa Tenggang Penalti (Bulan)</label>
                                    <input wire:model="dormant_penalty_grace_period" type="number" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" placeholder="Opsional">
                                </div>
                                <div class="space-y-2" x-data="{ 
                                    display: '',
                                    raw: @entangle('dormant_penalty_amount'),
                                    format(v) { return v ? v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.') : ''; },
                                    init() { this.display = this.format(this.raw || 0); }
                                }">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Biaya Penalti Dormant</label>
                                    <input type="text" 
                                           x-model="display"
                                           @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                           class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                    @error('dormant_penalty_amount') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2" x-data="{ 
                                    display: '',
                                    raw: @entangle('no_transaction_penalty'),
                                    format(v) { return v ? v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.') : ''; },
                                    init() { this.display = this.format(this.raw || 0); }
                                }">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Biaya Admin Pasif (Tanpa Transaksi)</label>
                                    <input type="text" 
                                           x-model="display"
                                           @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                           class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                    @error('no_transaction_penalty') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-surface-dim">

                        <!-- 5. Custom Fees (New Section) -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-3">
                                <span class="w-8 h-8 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-600">
                                    <span class="material-symbols-outlined text-sm">settings_suggest</span>
                                </span>
                                <h3 class="text-xs font-black uppercase tracking-widest text-primary">Biaya Kustom / Lainnya</h3>
                            </div>
                            <div class="grid grid-cols-3 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Nama Biaya</label>
                                    <input wire:model="fee_name" type="text" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" placeholder="Contoh: Biaya Materai">
                                </div>
                                <div class="space-y-2" x-data="{ 
                                    display: '',
                                    raw: @entangle('fee_amount'),
                                    format(v) { return v ? v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.') : ''; },
                                    init() { this.display = this.format(this.raw || 0); }
                                }">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Nominal Biaya</label>
                                    <input type="text" 
                                           x-model="display"
                                           @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                           class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 opacity-60">Tipe / Periode</label>
                                    <select wire:model="fee_type" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none cursor-pointer">
                                        <option value="">Pilih Tipe...</option>
                                        <option value="DAILY">Harian</option>
                                        <option value="MONTHLY">Bulanan</option>
                                        <option value="YEARLY">Tahunan</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr class="border-surface-dim">

                        <!-- 5. Accounting Rules (Grid Layout as requested) -->
                        <div class="space-y-8">
                            <div class="flex items-center space-x-3">
                                <span class="w-8 h-8 rounded-full bg-rose-500/10 flex items-center justify-center text-rose-600">
                                    <span class="material-symbols-outlined text-sm">account_balance</span>
                                </span>
                                <div>
                                    <h3 class="text-xs font-black uppercase tracking-widest text-primary">Accounting Rules</h3>
                                    <p class="text-[10px] text-outline font-medium">Tentukan akun akuntansi untuk setiap transaksi produk ini.</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-x-12 gap-y-10">
                                <!-- Row 1 -->
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Balance (Liability/Equity)<span class="text-red-500">*</span></label>
                                    <select wire:model="liability_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($liabilityEquityCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('liability_coa_id') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Income - Adm Fee (Revenue)<span class="text-red-500">*</span></label>
                                    <select wire:model="admin_fee_revenue_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($revenueCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('admin_fee_revenue_coa_id') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>

                                <!-- Row 2 -->
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Interest Expense (Expense)<span class="text-red-500">*</span></label>
                                    <select wire:model="interest_expense_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($expenseCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('interest_expense_coa_id') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Accrued Interest (Liability)<span class="text-red-500">*</span></label>
                                    <select wire:model="accrued_interest_payable_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($liabilityCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Row 3 -->
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Interest Tax Payable (Liability)<span class="text-red-500">*</span></label>
                                    <select wire:model="tax_liability_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($liabilityCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Default Kas Tunai (Asset)<span class="text-red-500">*</span></label>
                                    <select wire:model="default_cash_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($assetCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('default_cash_coa_id') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <!-- Row 4 -->
                            <div class="grid grid-cols-2 gap-x-12 gap-y-10 mt-10">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Default Bank (Asset)</label>
                                    <select wire:model="default_bank_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        <option value="">Pilih Akun Bank...</option>
                                        @foreach($assetCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('default_bank_coa_id') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">ABA Transit / Giro (Asset)</label>
                                    <select wire:model="aba_transit_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        <option value="">Pilih Akun ABA...</option>
                                        @foreach($assetCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('aba_transit_coa_id') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Section -->
                        <div class="mt-20 pt-10 border-t border-surface-dim flex justify-between items-center">
                            <div class="space-y-1">
                                <h4 class="text-xs font-black text-primary uppercase">Simpan Konfigurasi</h4>
                                <p class="text-[10px] text-outline font-medium">Perubahan yang diajukan akan masuk ke antrean persetujuan/approval.</p>
                            </div>
                            <div class="flex items-center space-x-6">
                                <div wire:loading class="flex items-center space-x-2 text-primary">
                                    <span class="material-symbols-outlined animate-spin text-sm">cycle</span>
                                    <span class="text-[9px] font-black uppercase tracking-widest">Memproses...</span>
                                </div>
                                <button wire:click="save" class="bg-primary hover:bg-primary-dim text-white px-12 py-5 rounded-[1.5rem] font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-primary/20 transition-all active:scale-95 flex items-center space-x-3">
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
