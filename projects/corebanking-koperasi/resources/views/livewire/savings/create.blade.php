<div class="p-0">
    <x-header title="Buka Rekening Baru" subtitle="Pendaftaran rekening simpanan anggota baru" :user="$user"
        :role="$role">
        <x-slot name="actions">
            <a href="{{ route('savings.inquiry') }}"
                class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all"
                wire:navigate>
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Kembali ke Daftar</span>
            </a>
        </x-slot>
    </x-header>

    <div class="p-10">
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col">
            <form wire:submit.prevent="save" class="flex flex-col">
                <div class="p-8 bg-white space-y-12">

                    <!-- SECTION 1: Cek Data Anggota -->
                    <div>
                        <div class="border-b border-slate-200 pb-2 mb-6">
                            <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span
                                    class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">person_search</span>
                                Cek Data Anggota (CIF)</p>
                        </div>

                        <div class="col-span-2 space-y-4">
                            <div class="relative">
                                <span
                                    class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                                <input wire:model.live.debounce.300ms="searchCif" type="text"
                                    class="w-full pl-12 pr-6 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm"
                                    placeholder="Cari Nama Anggota atau Nomor CIF (Min 3 Karakter)...">

                                @if($cifResults)
                                <div
                                    class="absolute z-10 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                                    @foreach($cifResults as $res)
                                    <button type="button" wire:click="selectCif({{ $res->id }})"
                                        class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-all border-b border-slate-50 last:border-0 group">
                                        <div class="text-left">
                                            <p class="text-xs font-black text-slate-900 uppercase">{{ $res->name }}</p>
                                            <p class="text-[10px] text-slate-500 font-bold tracking-widest">{{
                                                $res->cif_no }} | {{ $res->nik }}</p>
                                        </div>
                                        <span
                                            class="material-symbols-outlined text-slate-300 group-hover:text-slate-900 transition-all">add_circle</span>
                                    </button>
                                    @endforeach
                                </div>
                                @endif
                                @error('cif_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message
                                    }}</span> @enderror
                            </div>

                            @if($selectedCif)
                            <div
                                class="p-6 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between animate-in zoom-in-95 duration-300">
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                                        <span class="material-symbols-outlined">person</span>
                                    </div>
                                    <div class="space-y-0.5">
                                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight">{{
                                            $selectedCif->name }}</h4>
                                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{
                                            $selectedCif->cif_no }} • {{ $selectedCif->phone }}</p>
                                    </div>
                                </div>
                                <button type="button" wire:click="$set('selectedCif', null)"
                                    class="text-[10px] font-black uppercase tracking-widest text-rose-500 hover:text-rose-600 underline">Ganti
                                    Anggota</button>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- SECTION 2: Produk & Setoran -->
                    <div>
                        <div class="border-b border-slate-200 pb-2 mb-6">
                            <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span
                                    class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">inventory_2</span>
                                Produk & Setoran</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Produk
                                    Simpanan <span class="text-rose-500">*</span></label>
                                <select wire:model.live="saving_product_id"
                                    class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                    <option value="">Pilih Jenis Tabungan...</option>
                                    @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (Min. Rp {{
                                        number_format($p->min_initial_deposit, 2, ',', '.') }})</option>
                                    @endforeach
                                </select>
                                @error('saving_product_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{
                                    $message }}</span> @enderror
                            </div>

                            <div class="col-span-1 md:col-span-1 space-y-2 mt-2">
                                <label
                                    class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Keterangan
                                    / Memo (Opsional)</label>
                                <textarea wire:model="note" rows="3"
                                    class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700 resize-none"
                                    placeholder="Catatan tambahan..."></textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <div
                    class="px-8 py-5 border-t border-slate-100 flex justify-between items-center bg-slate-50/50 mt-auto">
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
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Konfirmasi
                                Identitas & Nominal Setoran</p>
                        </div>
                        @endif
                    </div>
                    <button type="submit"
                        class="px-8 py-3 bg-slate-900 text-white hover:shadow-lg hover:shadow-slate-900/20 font-bold text-xs rounded-xl transition-all active:scale-95 flex items-center">
                        <div wire:loading wire:target="save"
                            class="w-4 h-4 border-2 border-slate-400 border-t-white rounded-full animate-spin mr-2">
                        </div>
                        <span wire:loading.remove wire:target="save"
                            class="material-symbols-outlined text-sm mr-2">verified_user</span>
                        <span>Buka Rekening</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>