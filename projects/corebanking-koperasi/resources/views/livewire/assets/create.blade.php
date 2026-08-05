<div class="p-0">
    <x-header title="Daftarkan Aset Baru" subtitle="Inventarisasi aset kantor dengan konfigurasi penyusutan" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <a href="{{ route('assets.inquiry') }}" wire:navigate class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Kembali ke Daftar</span>
            </a>
        </x-slot>
    </x-header>

    <div class="p-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- FORM -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                    <form wire:submit.prevent="save" class="flex flex-col">
                        <div class="p-8 space-y-10">

                            <!-- SECTION 1: Identitas Aset -->
                            <div>
                                <div class="border-b border-slate-200 pb-2 mb-6">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                        <span class="material-symbols-outlined text-sm text-slate-400">inventory_2</span>
                                        Identitas Aset
                                    </p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2 space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Aset <span class="text-rose-500">*</span></label>
                                        <input wire:model="name" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all" placeholder="Cth: Laptop Dell Latitude 5520">
                                        @error('name') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kategori <span class="text-rose-500">*</span></label>
                                        <select wire:model.live="asset_category_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                            <option value="">Pilih Kategori...</option>
                                            @foreach($categories as $parent)
                                                <optgroup label="{{ $parent->name }}">
                                                    @foreach($parent->children as $child)
                                                        <option value="{{ $child->id }}">{{ $child->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        @error('asset_category_id') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Cabang <span class="text-rose-500">*</span></label>
                                        <select wire:model="branch_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                            <option value="">Pilih Cabang...</option>
                                            @foreach($branches as $b)
                                                <option value="{{ $b->id }}">{{ $b->name }}</option>
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
                                        <input wire:model="location" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all" placeholder="Cth: Ruang Server Lantai 2">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Vendor/Pemasok</label>
                                        <input wire:model="vendor" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kondisi Fisik <span class="text-rose-500">*</span></label>
                                        <select wire:model="condition" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                            <option value="GOOD">GOOD – Baik</option>
                                            <option value="FAIR">FAIR – Cukup</option>
                                            <option value="POOR">POOR – Perlu Perbaikan</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2 space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Keterangan</label>
                                        <textarea wire:model="description" rows="2" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 2: Nilai & Perolehan -->
                            <div>
                                <div class="border-b border-slate-200 pb-2 mb-6">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                        <span class="material-symbols-outlined text-sm text-slate-400">payments</span>
                                        Nilai Perolehan
                                    </p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Perolehan <span class="text-rose-500">*</span></label>
                                        <input wire:model="purchase_date" type="date" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                        @error('purchase_date') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Harga Perolehan (Rp) <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">Rp</span>
                                            <input wire:model="purchase_price" type="text" class="w-full pl-10 pr-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all" placeholder="0">
                                        </div>
                                        @error('purchase_price') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nilai Sisa / Residu (Rp)</label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">Rp</span>
                                            <input wire:model="salvage_value" type="text" class="w-full pl-10 pr-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all" placeholder="0">
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Sumber Pembayaran <span class="text-rose-500">*</span></label>
                                        <select wire:model.live="payment_channel" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                            <option value="CASH">Tunai (Kas)</option>
                                            <option value="ABA">Antar Bank Aktiva (ABA)</option>
                                            <option value="COA">COA Manual</option>
                                        </select>
                                        @error('payment_channel') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                                    </div>
                                    @if($payment_channel === 'CASH' && $cashCoas->count() > 1)
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Sub-Akun Kas</label>
                                            <select wire:model="cash_coa_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                                <option value="">Ikuti COA kas kategori</option>
                                                @foreach($cashCoas as $coa)
                                                    <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('cash_coa_id') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                                        </div>
                                    @elseif($payment_channel === 'ABA' && $abaCoas->count() > 1)
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Sub-Akun Bank</label>
                                            <select wire:model="bank_coa_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                                <option value="">Pilih bank / fallback kategori</option>
                                                @foreach($abaCoas as $coa)
                                                    <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('bank_coa_id') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                                        </div>
                                    @elseif($payment_channel === 'COA')
                                        <div class="space-y-2 md:col-span-2">
                                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">COA Manual <span class="text-rose-500">*</span></label>
                                            <select wire:model="manual_coa_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                                <option value="">Pilih COA kredit pembelian aset</option>
                                                @foreach($manualCoas as $coa)
                                                    <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ $coa->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('manual_coa_id') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- PREVIEW RULES (Dynamic) -->
                            @if($asset_category_id)
                                @php
                                    $cat = \App\Models\AssetCategory::find($asset_category_id);
                                @endphp
                                @if($cat)
                                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-200/60 relative overflow-hidden group hover:bg-white hover:shadow-xl hover:shadow-slate-200/40 transition-all">
                                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-slate-200/20 rounded-full blur-2xl group-hover:scale-150 transition-all duration-700"></div>
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white shadow-lg shadow-slate-900/20">
                                            <span class="material-symbols-outlined text-sm">settings_suggest</span>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-black text-slate-900">Konfigurasi Otomatis</h4>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Aturan Berdasarkan {{ $cat->parent?->name ?? 'Kategori' }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div class="space-y-1">
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Metode</p>
                                            <p class="text-xs font-black text-slate-900">{{ $cat->getEffectiveRule('depreciation_method') === 'PERCENTAGE' ? 'Saldo Menurun' : 'Garis Lurus' }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Masa Manfaat</p>
                                            <p class="text-xs font-black text-slate-900">{{ $cat->getEffectiveRule('useful_life_months') }} Bulan</p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Tarif Tahunan</p>
                                            <p class="text-xs font-black text-slate-900">{{ format_percent($cat->getEffectiveRule('depreciation_rate_annual')) }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Status</p>
                                            <p class="text-xs font-black text-emerald-600 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Terkoneksi
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endif


                        </div>

                        <!-- Footer -->
                        <div class="px-8 py-5 border-t border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <div>
                                @if($errors->any())
                                    <div class="flex items-center text-rose-500">
                                        <span class="material-symbols-outlined text-sm mr-2">error</span>
                                        <span class="text-xs font-bold">{{ $errors->first() }}</span>
                                    </div>
                                @else
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Kode aset akan dibuat otomatis oleh sistem</p>
                                @endif
                            </div>
                            <button type="submit" class="px-8 py-3 bg-slate-900 text-white hover:shadow-lg hover:shadow-slate-900/20 font-bold text-xs rounded-xl transition-all active:scale-95 flex items-center space-x-2">
                                <div wire:loading wire:target="save" class="w-4 h-4 border-2 border-slate-400 border-t-white rounded-full animate-spin"></div>
                                <span class="material-symbols-outlined text-sm" wire:loading.remove wire:target="save">save</span>
                                <span>Daftarkan Aset</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- INFO CARD -->
            <div class="space-y-6">
                <div class="p-8 bg-slate-900 rounded-[2rem] shadow-xl shadow-slate-900/20 text-white relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/5 rounded-full blur-3xl"></div>
                    <div class="absolute -left-6 -bottom-6 w-32 h-32 bg-indigo-500/10 rounded-full blur-3xl"></div>
                    
                    <p class="text-[10px] font-black uppercase tracking-widest text-white/40 mb-6 flex items-center gap-2">
                        <span class="w-4 h-[1px] bg-white/20"></span>
                        Info Golongan Pajak
                    </p>
                    
                    <div class="space-y-8 relative z-10">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-6 h-6 rounded-lg bg-white/10 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[14px] text-white">account_tree</span>
                                </div>
                                <p class="text-xs font-black text-white uppercase tracking-tighter">Struktur Hierarki</p>
                            </div>
                            <p class="text-[10px] font-medium text-white/50 leading-relaxed pl-9">
                                Sub-kategori mewarisi aturan penyusutan dari kategori induk (Golongan I-IV). Memudahkan standardisasi laporan pajak.
                            </p>
                        </div>

                        <div class="border-t border-white/5 pt-6">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-6 h-6 rounded-lg bg-white/10 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[14px] text-white">verified_user</span>
                                </div>
                                <p class="text-xs font-black text-white uppercase tracking-tighter">Verifikasi & Approval</p>
                            </div>
                            <p class="text-[10px] font-medium text-white/50 leading-relaxed pl-9">
                                Setiap pendaftaran aset baru harus melalui tahap persetujuan Admin/Super Admin sebelum jurnal pembelian dicatat secara otomatis.
                            </p>
                        </div>

                        <div class="border-t border-white/5 pt-6">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-6 h-6 rounded-lg bg-white/10 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[14px] text-white">history</span>
                                </div>
                                <p class="text-xs font-black text-white uppercase tracking-tighter">Audit Trail</p>
                            </div>
                            <p class="text-[10px] font-medium text-white/50 leading-relaxed pl-9">
                                Seluruh perubahan status aset dan eksekusi penyusutan tercatat di sistem Log Aktivitas untuk keperluan audit.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
