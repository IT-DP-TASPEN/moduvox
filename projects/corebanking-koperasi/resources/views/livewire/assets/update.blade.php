<div class="p-0">
    <x-header title="Perubahan Inventaris" subtitle="Cari aset terlebih dahulu sebelum melakukan koreksi data" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <a href="{{ route('assets.inquiry') }}" wire:navigate class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Daftar Inventaris</span>
            </a>
        </x-slot>
    </x-header>

    <div class="p-10">
        @if(session('success'))
            <div class="mb-6 px-5 py-3 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-700 text-xs font-bold flex items-center space-x-2">
                <span class="material-symbols-outlined text-sm">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(!$asset)
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pencarian Inventaris</p>
                        <h3 class="text-sm font-black text-slate-900 mt-1">Pilih aset yang akan dikoreksi</h3>
                    </div>
                    <div class="relative w-full max-w-md">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                        <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari nama, kode, atau serial..." class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:outline-none focus:border-slate-900">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50">
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center w-20">Opsi</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Kode & Nama</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Golongan</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Cabang</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Harga</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($assets as $item)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-4 px-6 text-center">
                                        <button wire:click="selectAsset({{ $item->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-slate-900 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto">
                                            <span class="material-symbols-outlined text-sm">edit_square</span>
                                        </button>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="text-xs font-black text-slate-900">{{ $item->name }}</p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $item->asset_code }} • {{ $item->serial_number ?: 'Tanpa serial' }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-[10px] font-bold text-slate-600">{{ $item->category?->name ?? '-' }}</td>
                                    <td class="py-4 px-6 text-[10px] font-bold text-slate-600">{{ $item->branch?->name ?? '-' }}</td>
                                    <td class="py-4 px-6 text-right text-xs font-black text-slate-900">Rp {{ number_format($item->purchase_price, 2, ',', '.') }}</td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="px-2 py-0.5 text-[9px] font-black rounded uppercase tracking-widest border bg-slate-50 text-slate-500 border-slate-200">{{ $item->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-24 text-center text-slate-300">
                                        <span class="material-symbols-outlined text-6xl mb-4 opacity-50">{{ strlen(trim($search)) >= 2 ? 'inventory_2' : 'search' }}</span>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                            {{ strlen(trim($search)) >= 2 ? 'Tidak ada inventaris ditemukan' : 'Ketik minimal 2 karakter untuk mencari inventaris' }}
                                        </p>
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
            <form wire:submit.prevent="save" class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="p-8 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Koreksi Inventaris</p>
                        <h3 class="text-lg font-black text-slate-900 mt-1">{{ $asset->asset_code }} - {{ $asset->name }}</h3>
                    </div>
                    <button type="button" wire:click="closeForm" class="w-10 h-10 flex items-center justify-center bg-white text-slate-400 hover:text-slate-900 rounded-xl border border-slate-200">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>

                <div class="p-8 space-y-10">
                    <div>
                        <div class="border-b border-slate-200 pb-2 mb-6">
                            <p class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-slate-400">inventory_2</span>
                                Identitas Aset
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Aset</label>
                                <input wire:model="name" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                @error('name') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Golongan / Kategori</label>
                                <select wire:model="asset_category_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                    <option value="">Pilih kategori...</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->parent?->name ? $category->parent->name . ' / ' : '' }}{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('asset_category_id') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Cabang</label>
                                <select wire:model="branch_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                    <option value="">Pilih cabang...</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. Seri / IMEI</label>
                                <input wire:model="serial_number" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Lokasi Penempatan</label>
                                <input wire:model="location" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Vendor/Pemasok</label>
                                <input wire:model="vendor" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kondisi Fisik</label>
                                <select wire:model="condition" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                    <option value="GOOD">GOOD - Baik</option>
                                    <option value="FAIR">FAIR - Cukup</option>
                                    <option value="POOR">POOR - Perlu Perbaikan</option>
                                </select>
                            </div>
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Keterangan</label>
                                <textarea wire:model="description" rows="2" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all"></textarea>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="border-b border-slate-200 pb-2 mb-6">
                            <p class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-slate-400">payments</span>
                                Nilai Perolehan
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Perolehan</label>
                                <input wire:model="purchase_date" type="date" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                @error('purchase_date') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Harga Perolehan</label>
                                <input wire:model="purchase_price" type="number" step="0.01" min="0" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                @error('purchase_price') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nilai Residu</label>
                                <input wire:model="salvage_value" type="number" step="0.01" min="0" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                @error('salvage_value') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-5 border-t border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Perubahan akan masuk antrean approval bila tata kelola aktif</p>
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="closeForm" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs uppercase tracking-widest">
                            Batal
                        </button>
                        <button type="submit" class="px-8 py-3 bg-slate-900 text-white hover:shadow-lg hover:shadow-slate-900/20 font-bold text-xs rounded-xl transition-all active:scale-95 flex items-center space-x-2">
                            <span class="material-symbols-outlined text-sm">save</span>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
