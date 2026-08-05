import sys

content = """<div class="p-0">
    <x-header title="Buka Rekening Deposito Baru" subtitle="Penempatan simpanan berjangka untuk anggota" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <a href="{{ route('deposits.inquiry') }}"
                class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm"
                wire:navigate>
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Kembali ke Daftar</span>
            </a>
        </x-slot>
    </x-header>

    <div class="p-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- FORM SECTIONS -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col h-full">
                    <form wire:submit.prevent="save" class="flex flex-col flex-grow">
                        <div class="p-8 bg-white space-y-12 flex-grow">

                            <!-- SECTION 1: Cek Data Anggota -->
                            <div>
                                <div class="border-b border-slate-200 pb-2 mb-6">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span
                                            class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">person_search</span>
                                        Cek Data Anggota (CIF)</p>
                                </div>

                                <div class="col-span-2 space-y-4">
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                                        <input wire:model.live.debounce.300ms="searchCif" type="text"
                                            class="w-full pl-12 pr-6 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900"
                                            placeholder="Cari Nama Anggota atau Nomor CIF (Min 3 Karakter)...">

                                        @if($cifResults)
                                        <div class="absolute z-10 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                                            @foreach($cifResults as $res)
                                            <button type="button" wire:click="selectCif({{ $res->id }})"
                                                class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-all border-b border-slate-50 last:border-0 group">
                                                <div class="text-left">
                                                    <p class="text-xs font-black text-slate-900 uppercase">{{ $res->name }}</p>
                                                    <p class="text-[10px] text-slate-500 font-bold tracking-widest">{{ $res->cif_no }} | {{ $res->nik }}</p>
                                                </div>
                                                <span class="material-symbols-outlined text-slate-300 group-hover:text-slate-900 transition-all">add_circle</span>
                                            </button>
                                            @endforeach
                                        </div>
                                        @endif
                                        @error('cif_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    @if($selectedCif)
                                    <div class="p-6 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between animate-in zoom-in-95 duration-300">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                                                <span class="material-symbols-outlined">person</span>
                                            </div>
                                            <div class="space-y-0.5">
                                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ $selectedCif->name }}</h4>
                                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $selectedCif->cif_no }} • NIK: {{ $selectedCif->nik }}</p>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="$set('selectedCif', null)"
                                            class="text-[10px] font-black uppercase tracking-widest text-rose-500 hover:text-rose-600 underline">Ganti Anggota</button>
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
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Produk Deposito <span class="text-rose-500">*</span></label>
                                        <select wire:model.live="deposit_product_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            <option value="">Pilih Produk Deposito...</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->product_code }})</option>
                                            @endforeach
                                        </select>
                                        @error('deposit_product_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Penempatan <span class="text-rose-500">*</span></label>
                                        <input wire:model="placement_date" type="date" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                        @error('placement_date') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="space-y-2 col-span-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nominal Pokok Penempatan <span class="text-rose-500">*</span></label>
                                        <div class="relative" x-data="{ 
                                            display: '',
                                            raw: @entangle('amount'),
                                            format(v) { return v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
                                            init() { this.display = this.format(this.raw || 0); }
                                        }">
                                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</div>
                                            <input type="text" x-model="display"
                                                @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                                class="w-full pl-12 pr-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-lg text-slate-900"
                                                placeholder="0">
                                        </div>
                                        @error('amount') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tenor (Bulan) <span class="text-rose-500">*</span></label>
                                        <input wire:model.live="tenor" type="number" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                        @error('tenor') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Suku Bunga (% p.a) <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <input wire:model.live="interest_rate" type="number" step="0.01" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700 pr-10">
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">%</span>
                                        </div>
                                        @error('interest_rate') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="space-y-2 col-span-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Metode Bunga <span class="text-rose-500">*</span></label>
                                        <select wire:model.live="interest_calculation_type" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            <option value="MONTHLY">Bunga Tetap Bulanan (Flat / 12)</option>
                                            <option value="DAILY">Bunga Harian (Aktual Hari / 360)</option>
                                        </select>
                                        @error('interest_calculation_type') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
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
                                    <div class="space-y-2 col-span-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Alokasi Bilyet Fisik <span class="text-rose-500">*</span></label>
                                        <select wire:model="deposit_bilyet_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            <option value="">Pilih Nomor Seri Bilyet Fisik...</option>
                                            @foreach($availableBilyets as $b)
                                                <option value="{{ $b->id }}">{{ $b->kode_bilyet }} (Seri: {{ $b->bilyet_number }}) - Tersedia</option>
                                            @endforeach
                                        </select>
                                        @error('deposit_bilyet_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="space-y-4">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tipe ARO (Auto Roll-Over) <span class="text-rose-500">*</span></label>
                                        <div class="space-y-3">
                                            <label class="flex items-center space-x-3 cursor-pointer group">
                                                <input type="radio" wire:model.live="rollover_type" value="NONE" class="w-5 h-5 text-slate-900 border-slate-200 focus:ring-slate-900 focus:ring-2">
                                                <div class="text-xs">
                                                    <span class="font-bold text-slate-900 block group-hover:text-slate-600">Non-ARO (Cair Ke Rekening)</span>
                                                    <span class="text-[10px] font-medium text-slate-500">Pokok & Bunga ditransfer cair saat jatuh tempo</span>
                                                </div>
                                            </label>
                                            <label class="flex items-center space-x-3 cursor-pointer group">
                                                <input type="radio" wire:model.live="rollover_type" value="PRINCIPAL" class="w-5 h-5 text-slate-900 border-slate-200 focus:ring-slate-900 focus:ring-2">
                                                <div class="text-xs">
                                                    <span class="font-bold text-slate-900 block group-hover:text-slate-600">ARO Pokok Saja</span>
                                                    <span class="text-[10px] font-medium text-slate-500">Hanya Pokok yang diperpanjang, Bunga dicairkan</span>
                                                </div>
                                            </label>
                                            <label class="flex items-center space-x-3 cursor-pointer group">
                                                <input type="radio" wire:model.live="rollover_type" value="PRINCIPAL_INTEREST" class="w-5 h-5 text-slate-900 border-slate-200 focus:ring-slate-900 focus:ring-2">
                                                <div class="text-xs">
                                                    <span class="font-bold text-slate-900 block group-hover:text-slate-600">ARO Pokok + Bunga</span>
                                                    <span class="text-[10px] font-medium text-slate-500">Otomatis tambah pokok saat diperpanjang</span>
                                                </div>
                                            </label>
                                        </div>
                                        @error('rollover_type') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    @if($rollover_type !== 'NONE')
                                    <div class="space-y-2 animate-fade-in">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Rekening Simpanan (Penerima Pencairan Bunga) <span class="text-rose-500">*</span></label>
                                        <select wire:model="saving_account_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            @if(!$cif_id)
                                                <option value="">Pilih Anggota Terlebih Dahulu</option>
                                            @elseif(count($savingAccounts) === 0)
                                                <option value="">Tidak ada rekening simpanan aktif</option>
                                            @else
                                                <option value="">Pilih Rekening Tujuan Saldo Bunga...</option>
                                                @foreach($savingAccounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_no }} - {{ $acc->product->name }} (Sisa Saldo: Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <p class="text-[10px] text-slate-400 font-bold ml-1 mt-1 leading-relaxed">Rekening ini digunakan sebagai penampung saat bunga dicairkan sesuai jadwal pencairan, atau sisa dana ARO masuk ke sini.</p>
                                        @error('saving_account_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Form Footer -->
                        <div class="px-8 py-5 border-t border-slate-100 flex justify-between items-center bg-slate-50/50 mt-auto">
                            <div>
                                @if($errors->any())
                                <div class="flex items-center text-rose-500 animate-fade-in">
                                    <span class="material-symbols-outlined text-sm mr-2">error</span>
                                    <span class="text-xs font-bold">Terdapat isian yang masih kosong atau tidak valid.</span>
                                </div>
                                @elseif (session()->has('success'))
                                <div class="flex items-center text-emerald-600 animate-fade-in">
                                    <span class="material-symbols-outlined text-sm mr-2">check_circle</span>
                                    <span class="text-xs font-bold">{{ session('success') }}</span>
                                </div>
                                @else
                                <div class="space-y-0.5">
                                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight">Cek Kembali Data</h4>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Konfirmasi Instruksi Deposito dan Informasi Anggota Saat Ini</p>
                                </div>
                                @endif
                            </div>
                            <button type="submit" class="px-8 py-3 bg-slate-900 text-white hover:shadow-lg hover:shadow-slate-900/20 font-bold text-xs rounded-xl transition-all active:scale-95 flex items-center">
                                <div wire:loading wire:target="save" class="w-4 h-4 border-2 border-slate-400 border-t-white rounded-full animate-spin mr-2"></div>
                                <span wire:loading.remove wire:target="save" class="material-symbols-outlined text-sm mr-2">verified_user</span>
                                <span>Buka Rekening Deposito</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PROJECTION PANEL (Right Sidebar) -->
            <div class="lg:col-span-1">
                @if($this->projection)
                    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden sticky top-8 animate-slide-up">
                        <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center space-x-3">
                            <span class="material-symbols-outlined text-emerald-500">insights</span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Proyeksi Hasil Deposito</h3>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Kalkulasi Cepat Pendapatan</p>
                            </div>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Nilai Jatuh Tempo</p>
                                    <p class="text-[10px] text-slate-400 font-medium tracking-tight">Maturity Date: {{ \Carbon\Carbon::parse($this->projection['maturity_date'])->format('d M Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xl font-black text-slate-900">Rp {{ number_format($this->projection['total_payout'], 2, ',', '.') }}</p>
                                </div>
                            </div>

                            <hr class="border-dashed border-slate-200">

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-500">Pokok Penempatan</p>
                                    <p class="text-xs font-black text-slate-900">Rp {{ number_format((float)$this->amount, 2, ',', '.') }}</p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-500">Est. Bunga Kotor</p>
                                    <p class="text-xs font-black text-slate-600">+ Rp {{ number_format($this->projection['gross_interest'], 2, ',', '.') }}</p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-500">Pajak</p>
                                    <p class="text-xs font-black text-rose-500">- Rp {{ number_format($this->projection['tax_amount'], 2, ',', '.') }}</p>
                                </div>
                            </div>

                            <div class="p-4 bg-emerald-50 rounded-xl flex items-center justify-between border border-emerald-100">
                                <p class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">Bunga Bersih</p>
                                <p class="text-sm font-black text-emerald-600">Rp {{ number_format($this->projection['net_interest'], 2, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="p-4 border-t border-slate-200 bg-slate-50 text-center">
                            <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest leading-relaxed">Peringatan: Proyeksi ini merupakan hitungan simulasi. Tidak mengikat dengan keadaan nyata saat penutupan rekening.</p>
                        </div>
                    </div>
                @else
                    <div class="bg-slate-50/50 rounded-[2rem] border border-dashed border-slate-200 h-64 flex flex-col items-center justify-center text-center p-8 sticky top-8">
                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-4 animate-bounce">insights</span>
                        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Proyeksi Bermutasi</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed">Masukkan Produk, Nominal & Tenor di samping untuk melihat perkiraan hasil.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
"""

with open("resources/views/livewire/deposits/placement.blade.php", "w") as f:
    f.write(content)
