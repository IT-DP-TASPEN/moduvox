<div x-data="{
    showModal:  @entangle('showModal').live,
    showDelete: @entangle('confirmingDeletion').live
}">

    {{-- ===== HEADER ===== --}}
    <x-header
        title="Kategori Aset"
        subtitle="Kelola kategori inventaris dengan mapping COA jurnal otomatis"
        :user="auth()->user()"
        :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live="search" type="text" placeholder="Cari kategori..."
                        class="pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all w-48 font-medium">
                </div>
                @can('assets.categories.create')
                <button @click="showModal = true; $wire.openCreate()"
                    class="flex items-center space-x-2 bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-sm">add</span>
                    <span>Tambah Kategori</span>
                </button>
                @endcan
            </div>
        </x-slot>
    </x-header>

    <div class="p-8">

        {{-- Flash Messages --}}
        @if (session()->has('success'))
        <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-2xl border border-emerald-100 flex items-center mb-6 animate-fade-in shadow-sm">
            <span class="material-symbols-outlined mr-3 text-lg">check_circle</span>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
        @endif
        @if (session()->has('error'))
        <div class="bg-rose-50 text-rose-700 px-6 py-4 rounded-2xl border border-rose-100 flex items-center mb-6 animate-fade-in shadow-sm">
            <span class="material-symbols-outlined mr-3 text-lg">error</span>
            <span class="font-bold text-sm">{{ session('error') }}</span>
        </div>
        @endif

        {{-- Info Banner: COA belum lengkap --}}
        @php
            $unmapped = $categories->getCollection()->filter(fn($c) => !$c->hasCompleteCOAMapping())->count();
        @endphp
        @if($unmapped > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl px-6 py-4 flex items-start space-x-3 mb-6">
            <span class="material-symbols-outlined text-amber-500 mt-0.5">warning</span>
            <div>
                <p class="text-xs font-black text-amber-800 uppercase tracking-wider">Mapping COA Belum Lengkap</p>
                <p class="text-xs text-amber-700 font-medium mt-0.5">
                    <strong>{{ $unmapped }}</strong> kategori pada halaman ini belum memiliki mapping COA lengkap.
                    Jurnal otomatis tidak akan dibuat untuk aset/penyusutan pada kategori tersebut.
                </p>
            </div>
        </div>
        @endif

        {{-- TABLE --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/60 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 uppercase text-[10px] tracking-widest font-bold text-slate-400">
                        <th class="px-6 py-4">Kategori / Golongan</th>
                        <th class="px-6 py-4 text-center">Aturan Penyusutan</th>
                        <th class="px-6 py-4">COA Aset & Akumulasi</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $cat)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500">
                                    <span class="material-symbols-outlined">category</span>
                                </div>
                                <div>
                                    <p class="font-black text-sm text-slate-900">
                                        @if($cat->parent)
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest block">{{ $cat->parent->name }}</span>
                                        @endif
                                        {{ $cat->name }}
                                    </p>
                                    @if($cat->description)
                                    <p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate max-w-[180px]">{{ $cat->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 uppercase">{{ $cat->depreciation_method_label }}</span>
                                    <span class="text-[9px] font-black text-slate-400">{{ $cat->useful_life_months }} Bln</span>
                                </div>
                                <p class="text-xs font-bold text-slate-700">{{ format_percent($cat->depreciation_rate_annual) }} <span class="text-[9px] text-slate-400 font-medium">/ Tahun</span></p>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center text-[10px] font-bold">A</div>
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $cat->coaAset->coa_code ?? '-' }}</span>
                                        <span class="text-[10px] font-bold text-slate-700 truncate max-w-[120px]">{{ $cat->coaAset->name ?? 'Belum dipetakan' }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 rounded-md bg-rose-50 text-rose-600 flex items-center justify-center text-[10px] font-bold">Σ</div>
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $cat->coaAkumPenyusutan->coa_code ?? '-' }}</span>
                                        <span class="text-[10px] font-bold text-slate-700 truncate max-w-[120px]">{{ $cat->coaAkumPenyusutan->name ?? 'Belum dipetakan' }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4 text-center">
                            @if($cat->is_active)
                            <span class="inline-flex items-center bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-bold border border-emerald-100">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span>AKTIF
                            </span>
                            @else
                            <span class="inline-flex items-center bg-slate-50 text-slate-400 px-3 py-1 rounded-full text-[10px] font-bold border border-slate-100">
                                <span class="w-1.5 h-1.5 bg-slate-300 rounded-full mr-1.5"></span>NONAKTIF
                            </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                @can('assets.categories.update')
                                <button wire:click="openEdit({{ $cat->id }})" @click="showModal = true"
                                    class="p-2 hover:bg-slate-100 rounded-xl text-slate-500 hover:text-slate-900 transition-colors" title="Edit">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                @endcan
                                @can('assets.categories.delete')
                                <button wire:click="confirmDelete({{ $cat->id }}, '{{ addslashes($cat->name) }}')" @click="showDelete = true"
                                    class="p-2 hover:bg-rose-50 rounded-xl text-slate-400 hover:text-rose-600 transition-colors" title="Hapus">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center space-y-3">
                                <span class="material-symbols-outlined text-5xl text-slate-200">category</span>
                                <p class="text-sm text-slate-400 font-bold">Belum ada kategori aset</p>
                                <p class="text-xs text-slate-300">Klik "Tambah Kategori" untuk memulai</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                {{ $categories->links() }}
            </div>
        </div>
    </div>

    {{-- ===== MODAL: CREATE / EDIT ===== --}}
    <div x-show="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/40 backdrop-blur-sm"
        x-cloak x-transition>
        <div @click.away="showModal = false; $wire.set('showModal', false)"
            class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden animate-slide-up max-h-[90vh] flex flex-col">

            {{-- Modal Header --}}
            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
                <div>
                    <h3 class="text-lg font-black text-slate-900">
                        {{ $editingId ? 'Edit Kategori Aset' : 'Tambah Kategori Aset' }}
                    </h3>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">
                        Atur mapping COA agar jurnal dapat dibuat otomatis
                    </p>
                </div>
                <button @click="showModal = false; $wire.set('showModal', false)"
                    class="p-2 rounded-xl hover:bg-slate-100 text-slate-400 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            {{-- Modal Body --}}
            <form wire:submit.prevent="save" class="flex flex-col overflow-hidden">
                <div class="px-8 py-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">

                    {{-- Identitas --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kategori Induk / Golongan</label>
                            <select wire:model="parent_id"
                                class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                <option value="">— Jadikan Kategori Utama (Golongan)</option>
                                @foreach($rootCategories as $root)
                                    @if($editingId !== $root->id)
                                        <option value="{{ $root->id }}">{{ $root->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('parent_id') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">
                                Nama Kategori / Sub-Kategori <span class="text-rose-500">*</span>
                            </label>
                            <input wire:model="name" type="text"
                                class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all"
                                placeholder="Cth: Komputer, Printer, Meja Kerja">
                            @error('name') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Metode Penyusutan <span class="text-rose-500">*</span></label>
                            <select wire:model="depreciation_method"
                                class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                <option value="STRAIGHT_LINE">Garis Lurus (Straight Line)</option>
                                <option value="PERCENTAGE">Saldo Menurun (Double Declining)</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tarif Tahunan (%) <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input wire:model="depreciation_rate_annual" type="number" step="0.01"
                                    class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all"
                                    placeholder="Cth: 25">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">%</span>
                            </div>
                            @error('depreciation_rate_annual') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Masa Manfaat (Bulan) <span class="text-rose-500">*</span></label>
                            <input wire:model="useful_life_months" type="number"
                                class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all"
                                placeholder="Cth: 48">
                            @error('useful_life_months') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status Aktif</label>
                            <div class="flex items-center space-x-4 px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl">
                                <button type="button" wire:click.prevent="$toggle('is_active')"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $is_active ? 'bg-emerald-500' : 'bg-slate-300' }}">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                                <span class="text-xs font-black {{ $is_active ? 'text-emerald-600' : 'text-slate-400' }}">{{ $is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </div>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Deskripsi</label>
                            <textarea wire:model="description" rows="2"
                                class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all resize-none"></textarea>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-slate-100 pt-2">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined text-sm text-slate-400">account_tree</span>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Mapping Chart of Accounts (COA)</p>
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-4 mb-4">
                            <p class="text-[10px] text-slate-500 font-medium leading-relaxed">
                                💡 Mapping COA memungkinkan sistem mencatat jurnal secara otomatis.
                                <strong class="text-slate-700">COA Aset Tetap + Kas/Bank</strong> digunakan saat pendaftaran aset baru.
                                <strong class="text-slate-700">COA Beban + Akumulasi Penyusutan</strong> digunakan saat eksekusi penyusutan bulanan.
                                Jika tidak diisi, aset/penyusutan tetap tercatat namun <em>tanpa jurnal otomatis</em>.
                            </p>
                        </div>
                    </div>

                    {{-- COA Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- COA Aset Tetap --}}
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">
                                COA Aset Tetap
                                <span class="text-slate-300 font-medium">(type: ASSET)</span>
                            </label>
                            <select wire:model="coa_aset_id"
                                class="w-full px-5 py-3 bg-white border border-slate-200 rounded-2xl font-bold text-xs text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                <option value="">— Tidak dipetakan</option>
                                @foreach($coaAsetOptions as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->coa_code }} – {{ $coa->name }}</option>
                                @endforeach
                            </select>
                            @error('coa_aset_id') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                        </div>

                        {{-- COA Kas/Bank --}}
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">
                                COA Kas / Bank
                                <span class="text-slate-300 font-medium">(untuk pembelian)</span>
                            </label>
                            <select wire:model="coa_kas_id"
                                class="w-full px-5 py-3 bg-white border border-slate-200 rounded-2xl font-bold text-xs text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                <option value="">— Tidak dipetakan</option>
                                @foreach($coaAllLeaf as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->coa_code }} – {{ $coa->name }}</option>
                                @endforeach
                            </select>
                            @error('coa_kas_id') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                        </div>

                        {{-- COA Akumulasi Penyusutan --}}
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">
                                COA Akumulasi Penyusutan
                                <span class="text-slate-300 font-medium">(contra ASSET)</span>
                            </label>
                            <select wire:model="coa_akum_penyusutan_id"
                                class="w-full px-5 py-3 bg-white border border-slate-200 rounded-2xl font-bold text-xs text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                <option value="">— Tidak dipetakan</option>
                                @foreach($coaAsetOptions as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->coa_code }} – {{ $coa->name }}</option>
                                @endforeach
                            </select>
                            @error('coa_akum_penyusutan_id') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                        </div>

                        {{-- COA Beban Penyusutan --}}
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">
                                COA Beban Penyusutan
                                <span class="text-slate-300 font-medium">(type: EXPENSE)</span>
                            </label>
                            <select wire:model="coa_beban_penyusutan_id"
                                class="w-full px-5 py-3 bg-white border border-slate-200 rounded-2xl font-bold text-xs text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                <option value="">— Tidak dipetakan</option>
                                @foreach($coaExpenseOptions as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->coa_code }} – {{ $coa->name }}</option>
                                @endforeach
                            </select>
                            @error('coa_beban_penyusutan_id') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-8 py-5 border-t border-slate-100 flex space-x-3 flex-shrink-0 bg-slate-50">
                    <button type="button" @click="showModal = false; $wire.set('showModal', false)"
                        class="flex-1 py-3 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-600 hover:bg-slate-50 transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-[2] py-3 bg-slate-900 text-white rounded-2xl font-bold text-sm hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95 flex items-center justify-center space-x-2">
                        <div wire:loading wire:target="save" class="w-4 h-4 border-2 border-slate-400 border-t-white rounded-full animate-spin"></div>
                        <span class="material-symbols-outlined text-sm" wire:loading.remove wire:target="save">save</span>
                        <span>{{ $editingId ? 'Simpan Perubahan' : 'Tambah Kategori' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL: DELETE CONFIRM ===== --}}
    <div x-show="showDelete"
        class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-black/40 backdrop-blur-sm"
        x-cloak x-transition>
        <div @click.away="showDelete = false"
            class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl p-8 text-center animate-slide-up">
            <div class="w-16 h-16 bg-rose-50 rounded-3xl flex items-center justify-center mx-auto mb-5">
                <span class="material-symbols-outlined text-3xl text-rose-500">category</span>
            </div>
            <h3 class="text-lg font-black text-slate-900 mb-2">Hapus Kategori?</h3>
            <p class="text-sm text-slate-500 font-medium mb-6 leading-relaxed">
                Kategori <span class="font-black text-rose-600">"{{ $deletingName }}"</span> akan dihapus permanen.
                Pastikan tidak ada aset yang menggunakan kategori ini.
            </p>
            <div class="flex flex-col space-y-3">
                <button wire:click="deleteCategory"
                    class="w-full bg-rose-500 text-white py-3.5 rounded-2xl font-bold text-sm hover:bg-rose-600 hover:shadow-lg hover:shadow-rose-500/30 transition-all active:scale-95">
                    Ya, Hapus Kategori
                </button>
                <button @click="showDelete = false"
                    class="w-full bg-slate-50 text-slate-600 py-3.5 rounded-2xl font-bold text-sm hover:bg-slate-100 transition-all">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>
