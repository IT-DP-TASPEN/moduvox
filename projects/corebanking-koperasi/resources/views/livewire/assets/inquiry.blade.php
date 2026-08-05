<div class="p-0">
    <x-header title="Daftar Inventaris Kantor" subtitle="Manajemen aset dan tracking nilai buku" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <div class="flex items-center space-x-3">
                <select wire:model.live="filterCategory" class="pl-3 pr-10 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none font-bold text-slate-700 appearance-none shadow-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterStatus" class="pl-3 pr-10 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none font-bold text-slate-700 appearance-none shadow-sm">
                    <option value="">Semua Status</option>
                    <option value="ACTIVE">ACTIVE</option>
                    <option value="RENTED">RENTED</option>
                    <option value="DISPOSED">DISPOSED</option>
                </select>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari nama, kode aset..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 w-56 shadow-sm focus:outline-none">
                </div>
                <a href="{{ route('assets.create') }}" wire:navigate class="flex items-center space-x-2 bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-sm">add_box</span>
                    <span>Tambah Aset</span>
                </a>
            </div>
        </x-slot>
    </x-header>

    <div class="p-10">
        @if(session('success'))
            <div class="mb-6 px-5 py-3 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-700 text-xs font-bold flex items-center space-x-2">
                <span class="material-symbols-outlined text-sm">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($viewMode === 'grid')
        <!-- GRID: Asset List -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center w-16">OPSI</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Kode & Nama Aset</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Kategori</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Harga Perolehan</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Nilai Buku</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Metode</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Kondisi</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="py-4 px-6 text-center">
                                <button wire:click="viewAsset({{ $asset->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-slate-900 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto">
                                    <span class="material-symbols-outlined text-sm">visibility</span>
                                </button>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-xs font-black text-slate-900">{{ $asset->name }}</p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $asset->asset_code }} • {{ $asset->location ?? 'Lokasi tidak diatur' }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-[10px] font-bold text-slate-600">{{ $asset->category->name ?? '-' }}</span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <p class="text-xs font-bold text-slate-500">Rp {{ number_format($asset->purchase_price, 2, ',', '.') }}</p>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <p class="text-xs font-black text-slate-900">Rp {{ number_format($asset->current_book_value, 2, ',', '.') }}</p>
                                @php $pct = $asset->purchase_price > 0 ? ($asset->current_book_value / $asset->purchase_price * 100) : 0; @endphp
                                <div class="w-20 h-1 bg-slate-100 rounded-full mt-1.5 ml-auto">
                                    <div class="h-1 rounded-full {{ $pct > 50 ? 'bg-emerald-400' : ($pct > 20 ? 'bg-amber-400' : 'bg-rose-400') }}" style="width: {{ min($pct, 100) }}%"></div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                @if($asset->depreciation_method === 'PERCENTAGE')
                                    <span class="px-2 py-0.5 text-[9px] font-black rounded uppercase tracking-widest bg-blue-50 text-blue-600 border border-blue-100">{{ format_percent($asset->getEffectiveMonthlyRate()) }}/bln</span>
                                @else
                                    <span class="px-2 py-0.5 text-[9px] font-black rounded uppercase tracking-widest bg-purple-50 text-purple-600 border border-purple-100">Rp {{ number_format($asset->getEffectiveMonthlyNominal(), 2, ',', '.') }}/bln</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span @class(['px-2 py-0.5 text-[9px] font-black rounded uppercase tracking-widest border',
                                    'bg-emerald-50 text-emerald-600 border-emerald-100' => $asset->condition === 'GOOD',
                                    'bg-amber-50 text-amber-600 border-amber-100' => $asset->condition === 'FAIR',
                                    'bg-rose-50 text-rose-600 border-rose-100' => $asset->condition === 'POOR'
                                ])>{{ $asset->condition }}</span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span @class(['px-2 py-0.5 text-[9px] font-black rounded uppercase tracking-widest border',
                                    'bg-emerald-50 text-emerald-600 border-emerald-100' => $asset->status === 'ACTIVE',
                                    'bg-blue-50 text-blue-600 border-blue-100' => $asset->status === 'RENTED',
                                    'bg-slate-100 text-slate-500 border-slate-200' => $asset->status === 'DISPOSED'
                                ])>{{ $asset->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-20 text-center text-slate-300">
                                <span class="material-symbols-outlined text-6xl mb-4 opacity-50">inventory_2</span>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Belum ada data aset. Klik "Tambah Aset" untuk mendaftarkan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($assets->hasPages())
                <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                    {{ $assets->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </div>

        @else
        <!-- DETAIL VIEW -->
        @if($selectedAsset)
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
            <!-- Top Bar -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 px-8 py-6 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <button wire:click="closeView" class="p-2 bg-white hover:bg-slate-200 rounded-xl transition-all shadow-sm border border-slate-200">
                        <span class="material-symbols-outlined text-sm">arrow_back</span>
                    </button>
                    <div>
                        <h2 class="font-extrabold text-sm text-slate-900 uppercase tracking-wide">{{ $selectedAsset->name }}</h2>
                        <p class="text-[10px] uppercase font-bold text-slate-500 tracking-widest mt-0.5">{{ $selectedAsset->asset_code }} • {{ $selectedAsset->category->name ?? '-' }}</p>
                    </div>
                </div>
                <span @class(['px-4 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-xl border',
                    'bg-emerald-50 text-emerald-600 border-emerald-100' => $selectedAsset->status === 'ACTIVE',
                    'bg-blue-50 text-blue-600 border-blue-100' => $selectedAsset->status === 'RENTED',
                    'bg-slate-100 text-slate-500 border-slate-200' => $selectedAsset->status === 'DISPOSED'
                ])>{{ $selectedAsset->status }}</span>
            </div>

            <div class="grid grid-cols-12 gap-8">
                <!-- Left: Detail Form -->
                <div class="col-span-12 lg:col-span-8 space-y-8">
                    <div class="p-10 bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60">
                        <fieldset disabled class="m-0 p-0 border-0">
                            <div class="space-y-10">
                                @php($inventory = $this->inventoryRow($selectedAsset, 1))
                                <div>
                                    <div class="border-b border-slate-200 pb-2 mb-6 text-indigo-600">
                                        <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                            <span class="material-symbols-outlined text-sm">inventory_2</span>
                                            Identitas Aset
                                        </p>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kode Aset</label>
                                            <input type="text" value="{{ $selectedAsset->asset_code }}" class="w-full px-5 py-3.5 bg-indigo-50 border border-indigo-100 rounded-2xl font-black text-sm text-indigo-700">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Aset</label>
                                            <input type="text" value="{{ $selectedAsset->name }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Lokasi</label>
                                            <input type="text" value="{{ $selectedAsset->location ?? '-' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. Seri</label>
                                            <input type="text" value="{{ $selectedAsset->serial_number ?? '-' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Vendor</label>
                                            <input type="text" value="{{ $selectedAsset->vendor ?? '-' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Perolehan</label>
                                            <input type="text" value="{{ $selectedAsset->purchase_date->format('d/m/Y') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="border-b border-slate-200 pb-2 mb-6 text-slate-900">
                                        <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                            <span class="material-symbols-outlined text-sm">fact_check</span>
                                            Informasi Daftar Inventaris
                                        </p>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nomor Rekening/Seri</label>
                                            <input type="text" value="{{ $inventory[1] }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Usia Pakai</label>
                                            <input type="text" value="{{ $inventory[4] }} Bulan" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Habis Buku</label>
                                            <input type="text" value="{{ $inventory[5] }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nilai Buku Bulan Lalu</label>
                                            <input type="text" value="Rp {{ number_format($inventory[7], 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-slate-900">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nilai Penyusutan</label>
                                            <input type="text" value="Rp {{ number_format($inventory[8], 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-rose-50 border border-rose-100 rounded-2xl font-black text-sm text-rose-700">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Akumulasi Penyusutan</label>
                                            <input type="text" value="Rp {{ number_format($inventory[9], 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-slate-900">
                                        </div>
                                        <div class="space-y-2 md:col-span-3">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nilai Buku Bulan Sekarang</label>
                                            <input type="text" value="Rp {{ number_format($inventory[10], 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-emerald-50 border border-emerald-100 rounded-2xl font-black text-sm text-emerald-700">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="border-b border-slate-200 pb-2 mb-6 text-emerald-600">
                                        <p class="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                            <span class="material-symbols-outlined text-sm">trending_down</span>
                                            Konfigurasi Penyusutan
                                        </p>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Harga Perolehan</label>
                                            <input type="text" value="Rp {{ number_format($selectedAsset->purchase_price, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-slate-900 border border-slate-900 rounded-2xl font-black text-sm text-white">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nilai Buku Saat Ini</label>
                                            <input type="text" value="Rp {{ number_format($selectedAsset->current_book_value, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-emerald-50 border border-emerald-100 rounded-2xl font-black text-sm text-emerald-700">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nilai Sisa</label>
                                            <input type="text" value="Rp {{ number_format($selectedAsset->salvage_value, 2, ',', '.') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Masa Manfaat</label>
                                            <input type="text" value="{{ $selectedAsset->useful_life_months }} Bulan" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Metode</label>
                                            <input type="text" value="{{ $selectedAsset->depreciation_method }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-indigo-600 uppercase">
                                        </div>
                                        @php($effectiveDepreciationNominal = $selectedAsset->getEffectiveMonthlyNominal())
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Rate / Nominal</label>
                                            <input type="text" value="{{ $selectedAsset->depreciation_method === 'PERCENTAGE' ? format_percent($selectedAsset->getEffectiveMonthlyRate()) . ' / bulan' : 'Rp ' . number_format($effectiveDepreciationNominal, 2, ',', '.') . ' / bulan' }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <!-- Depreciation Log Table -->
                    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="material-symbols-outlined text-slate-900">trending_down</span>
                                <h4 class="text-xs font-black uppercase tracking-widest text-slate-900">Riwayat Penyusutan</h4>
                            </div>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $selectedAsset->depreciations->count() }} Periode Tereksekusi</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-white border-b border-slate-100">
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Periode</th>
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Nilai Awal</th>
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Penyusutan</th>
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Nilai Akhir</th>
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Metode</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @forelse($selectedAsset->depreciations as $dep)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="py-4 px-6 text-[11px] font-black text-slate-900">{{ $dep->period_year_month }}</td>
                                        <td class="py-4 px-6 text-[11px] font-bold text-slate-600 text-right">Rp {{ number_format((float) $dep->book_value_after + (float) $dep->depreciation_amount, 2, ',', '.') }}</td>
                                        <td class="py-4 px-6 text-[11px] font-black text-rose-600 text-right">- Rp {{ number_format($dep->depreciation_amount, 2, ',', '.') }}</td>
                                        <td class="py-4 px-6 text-[11px] font-black text-emerald-600 text-right">Rp {{ number_format($dep->book_value_after, 2, ',', '.') }}</td>
                                        <td class="py-4 px-6">
                                            <span class="text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-widest bg-purple-50 text-purple-600 border border-purple-100">{{ $selectedAsset->depreciation_method }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-slate-300">
                                            <p class="text-[10px] font-black uppercase tracking-widest">Belum ada penyusutan yang dieksekusi.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right: Summary Card -->
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    <div class="p-8 bg-slate-900 rounded-[2.5rem] shadow-xl shadow-slate-900/20 text-white relative overflow-hidden">
                        <div class="absolute -right-8 -top-8 w-36 h-36 bg-white/5 rounded-full blur-3xl"></div>
                        <div class="relative z-10">
                            <p class="text-[10px] font-black uppercase tracking-widest text-white/40 mb-1">Nilai Buku Saat Ini</p>
                            <h3 class="text-3xl font-black tracking-tight mb-6">Rp {{ number_format($selectedAsset->current_book_value, 2, ',', '.') }}</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-[10px] text-white/40 font-bold uppercase tracking-widest">Harga Perolehan</span>
                                    <span class="text-sm font-black">Rp {{ number_format($selectedAsset->purchase_price, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[10px] text-white/40 font-bold uppercase tracking-widest">Total Penyusutan</span>
                                    <span class="text-sm font-black text-rose-400">- Rp {{ number_format($selectedAsset->accumulated_depreciation, 2, ',', '.') }}</span>
                                </div>
                                <div class="pt-3 border-t border-white/10 flex justify-between">
                                    <span class="text-[10px] text-white/40 font-bold uppercase tracking-widest">Nilai Sisa</span>
                                    <span class="text-sm font-black text-amber-400">Rp {{ number_format($selectedAsset->salvage_value, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($selectedAsset->rentals->count() > 0)
                    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Riwayat Sewa</p>
                        <div class="space-y-3">
                            @foreach($selectedAsset->rentals as $rental)
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                                <p class="text-xs font-black text-slate-900">{{ $rental->rekanan->name }}</p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $rental->contract_no }}</p>
                                <p class="text-[9px] text-slate-500 font-bold mt-1">{{ $rental->rental_start_date->format('d/m/Y') }} – {{ $rental->rental_end_date->format('d/m/Y') }}</p>
                                <span @class(['inline-block mt-1 px-2 py-0.5 text-[8px] font-black rounded uppercase tracking-widest border',
                                    'bg-emerald-50 text-emerald-600 border-emerald-100' => $rental->status === 'ACTIVE',
                                    'bg-slate-100 text-slate-500 border-slate-200' => $rental->status !== 'ACTIVE'
                                ])>{{ $rental->status }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
        @endif
    </div>
</div>
