<div x-data="{ activeTab: 'general' }" class="min-h-screen">
    <!-- Header Section -->
    <x-header title="Master Produk Kredit" subtitle="Konfigurasi parameter dan pemetaan akuntansi produk pinjaman/pembiayaan" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot:actions>
            @if($viewMode === 'list')
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm">search</span>
                        <input wire:model.live="search" type="text" placeholder="Cari produk..." class="pl-10 pr-4 py-2 bg-surface border border-surface-dim rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all w-48 font-medium">
                    </div>
                    @can('loan-products.create')
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
            <!-- Products List -->
            <div class="bg-white rounded-3xl shadow-sm border border-surface-dim overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-surface border-b border-surface-dim uppercase text-[10px] tracking-widest font-bold text-outline">
                            <th class="px-6 py-4">Kode</th>
                            <th class="px-6 py-4">Nama Produk</th>
                            <th class="px-6 py-4 text-center">Bunga / Metode</th>
                            <th class="px-6 py-4 text-center">Tenor</th>
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
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <p class="text-[9px] text-outline font-medium tracking-tight uppercase">LOAN PRODUCT</p>
                                        @if($p->is_diskonto)
                                        <span class="px-1.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider bg-amber-50 text-amber-600 border border-amber-200">DISKONTO</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-bold text-primary">{{ format_percent($p->interest_rate_min) }} - {{ format_percent($p->interest_rate_max) }}</span>
                                        <span class="text-[8px] text-outline uppercase font-bold tracking-tighter">{{ $p->calculation_method }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-xs font-bold text-outline">{{ $p->tenor_min }} - {{ $p->tenor_max }} {{ $p->tenor_type }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $p->is_active ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100' }}">
                                        {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @can('loan-products.update')
                                    <button wire:click="edit({{ $p->id }})" class="p-2 text-outline hover:text-primary hover:bg-primary/10 rounded-xl transition-all mr-2" title="Edit">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
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
                                    <input wire:model="product_code" type="text" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase" placeholder="e.g. MTR01">
                                    @error('product_code') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Nama Produk Kredit</label>
                                    <input wire:model="name" type="text" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" placeholder="e.g. Kredit Pemilikan Rumah">
                                    @error('name') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-6 bg-surface/50 rounded-2xl border border-surface-dim">
                                <div>
                                    <h4 class="text-xs font-black text-primary uppercase">Status Aktif</h4>
                                    <p class="text-[10px] text-outline font-medium">Aktifkan produk agar bisa digunakan di pengajuan kredit baru</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                    <div class="w-11 h-6 bg-surface-dim rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary border border-surface-dim"></div>
                                </label>
                            </div>

                            {{-- Toggle Diskonto --}}
                            <div class="flex items-center justify-between p-6 rounded-2xl border-2 {{ $is_diskonto ? 'bg-amber-50/50 border-amber-200' : 'bg-surface/50 border-surface-dim' }}">
                                <div>
                                    <h4 class="text-xs font-black uppercase {{ $is_diskonto ? 'text-amber-700' : 'text-primary' }}">Produk Diskonto</h4>
                                    <p class="text-[10px] font-medium {{ $is_diskonto ? 'text-amber-600' : 'text-outline' }}">
                                        Bunga seluruh tenor (kecuali terakhir) dibayar di muka &bull; Hanya metode Flat &bull; Asuransi Flat only
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model.live="is_diskonto" class="sr-only peer">
                                    <div class="w-11 h-6 bg-surface-dim rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500 border border-surface-dim"></div>
                                </label>
                            </div>
                        </div>

                        <hr class="border-surface-dim">

                        <!-- 2. Loan Settings -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-3">
                                <span class="w-8 h-8 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-600">
                                    <span class="material-symbols-outlined text-sm">trending_up</span>
                                </span>
                                <h3 class="text-xs font-black uppercase tracking-widest text-primary">Parameter Kredit & Bunga</h3>
                            </div>
                            <div class="grid grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Metode Hitung Bunga</label>
                                    @if($is_diskonto)
                                        <div class="w-full px-5 py-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs font-black text-amber-700 flex items-center space-x-2">
                                            <span class="material-symbols-outlined text-sm">lock</span>
                                            <span>FLAT RATE (Diskonto)</span>
                                        </div>
                                        <input type="hidden" wire:model="calculation_method" value="FLAT">
                                    @else
                                        <select wire:model="calculation_method" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none cursor-pointer">
                                            <option value="FLAT">Flat Rate</option>
                                            <option value="EFFECTIVE">Effective Rate</option>
                                            <option value="ANNUITY">Annuity (Anuitas)</option>
                                        </select>
                                    @endif
                                    @error('calculation_method') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-span-2 grid grid-cols-2 gap-8 pt-4">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Bunga Minimum (% per tahun)</label>
                                        <div class="relative">
                                            <input wire:model="interest_rate_min" type="number" step="0.01" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all pr-12">
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-primary font-black text-xs">%</span>
                                        </div>
                                        @error('interest_rate_min') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Bunga Maksimum (% per tahun)</label>
                                        <div class="relative">
                                            <input wire:model="interest_rate_max" type="number" step="0.01" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all pr-12">
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-primary font-black text-xs">%</span>
                                        </div>
                                        @error('interest_rate_max') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                
                                <div class="col-span-2 grid grid-cols-3 gap-8 pt-4 border-t border-surface-dim">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Provisi (%)</label>
                                        <div class="relative">
                                            <input wire:model="provision_rate" type="number" step="0.01" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all pr-12">
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-primary font-black text-xs">%</span>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Biaya Admin (%)</label>
                                        <div class="relative">
                                            <input wire:model="admin_rate" type="number" step="0.01" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all pr-12">
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-primary font-black text-xs">%</span>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Denda Keterlambatan (%)</label>
                                        <div class="relative">
                                            <input wire:model="penalty_rate" type="number" step="0.01" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all pr-12">
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-rose-500 font-black text-xs">%</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-span-2 grid grid-cols-3 gap-8 pt-4 border-t border-surface-dim">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Tenor Minimum</label>
                                        <input wire:model="tenor_min" type="number" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        @error('tenor_min') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Tenor Maksimum</label>
                                        <input wire:model="tenor_max" type="number" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                        @error('tenor_max') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Satuan Tenor</label>
                                        <select wire:model="tenor_type" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none cursor-pointer">
                                            <option value="MONTHS">Bulan</option>
                                            <option value="DAYS">Hari</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <hr class="border-surface-dim">

                        <!-- 3. Accounting Mapping -->
                        <div class="space-y-8">
                            <div class="flex items-center space-x-3">
                                <span class="w-8 h-8 rounded-full bg-rose-500/10 flex items-center justify-center text-rose-600">
                                    <span class="material-symbols-outlined text-sm">account_balance</span>
                                </span>
                                <div>
                                    <h3 class="text-xs font-black uppercase tracking-widest text-primary">Pemetaan Akuntansi (GL)</h3>
                                    <p class="text-[10px] text-outline font-medium">Tentukan akun untuk pencairan, pendapatan, dan pencadangan (CKPN).</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-x-12 gap-y-10">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun Pokok Kredit (Asset)<span class="text-red-500">*</span></label>
                                    <select wire:model="principal_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($assetCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('principal_coa_id') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun Bunga Berjalan (Asset)</label>
                                    <select wire:model="accrued_interest_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($assetCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Pendapatan Bunga (Revenue)<span class="text-red-500">*</span></label>
                                    <select wire:model="interest_revenue_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($revenueCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('interest_revenue_coa_id') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">
                                        Bunga Diterima Dimuka (Liability) @if($is_diskonto)<span class="text-red-500">*</span>@endif
                                    </label>
                                    <select wire:model="deferred_interest_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($liabilityCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('deferred_interest_coa_id') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun Provisi (Revenue)</label>
                                    <select wire:model="provision_revenue_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($revenueCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun Admin Fee (Revenue)</label>
                                    <select wire:model="admin_fee_revenue_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($revenueCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun Pendapatan Asuransi (Revenue)</label>
                                    <select wire:model="insurance_revenue_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($revenueCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun Denda (Revenue)</label>
                                    <select wire:model="penalty_revenue_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($revenueCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Additional Revenue Accounts -->
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun Flagging Fee (Revenue)</label>
                                    <select wire:model="flagging_revenue_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($revenueCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Accrued Interest Receivable (Asset) -->
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun Bunga Piutang Berjalan (Asset)</label>
                                    <select wire:model="accrued_interest_receivable_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($assetCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Stamp Duty Payable (Liability) -->
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun Utang Materai (Liability)</label>
                                    <select wire:model="stamp_duty_payable_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($liabilityCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- CKPN / Credit Risk Reserve (Asset) -->
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun CKPN / Cadangan Kredit (Asset)</label>
                                    <select wire:model="ckpn_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($assetCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Settlement Transit Accounts -->
                                <div class="col-span-2 border-t border-surface-dim pt-6 mt-4 grid grid-cols-2 gap-8">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Akun Suspense (Transit Internal)</label>
                                        <select wire:model="suspense_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                            <option value="">Pilih Akun...</option>
                                            @foreach($assetCoas as $coa)
                                                <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1 text-outline/60">Akun ABA Transit (Transfer)</label>
                                        <select wire:model="aba_transit_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                            <option value="">Pilih Akun...</option>
                                            @foreach($assetCoas as $coa)
                                                <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="space-y-2 border-t border-surface-dim pt-6 mt-4">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun Kas Tunai (Disbursement)<span class="text-red-500">*</span></label>
                                    <select wire:model="default_cash_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($assetCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('default_cash_coa_id') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Akun Bank (Disbursement)<span class="text-red-500">*</span></label>
                                    <select wire:model="default_bank_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase">
                                        <option value="">Pilih Akun...</option>
                                        @foreach($assetCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('default_bank_coa_id') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Section -->
                        <div class="mt-20 pt-10 border-t border-surface-dim flex justify-between items-center">
                            <div class="space-y-1">
                                <h4 class="text-xs font-black text-primary uppercase">Simpan Konfigurasi</h4>
                                <p class="text-[10px] text-outline font-medium">Pengajuan produk baru akan masuk antrean persetujuan/approval.</p>
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
