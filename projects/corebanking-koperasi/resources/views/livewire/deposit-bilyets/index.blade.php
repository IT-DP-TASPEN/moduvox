<div class="min-h-screen">
    <!-- Header Section -->
    <x-header title="Manajemen Bilyet Simpanan Berjangka" subtitle="Pengelolaan stok dan pendaftaran bilyet fisik simpanan berjangka" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot:actions>
            @if($viewMode === 'list')
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm">search</span>
                        <input wire:model.live="search" type="text" placeholder="Cari nomor bilyet..." class="pl-10 pr-4 py-2 bg-surface border border-surface-dim rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all w-48 font-medium">
                    </div>
                    @can('deposit-bilyets.create')
                    <button wire:click="setView('register')" class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-xl hover:bg-primary-dim transition-all shadow-sm active:scale-95">
                        <span class="material-symbols-outlined text-sm">add_box</span>
                        <span class="text-xs font-bold uppercase tracking-wider">Register Range</span>
                    </button>
                    @endcan
                </div>
            @else
                <button wire:click="setView('list')" class="flex items-center space-x-2 bg-surface border border-surface-dim text-primary px-4 py-2 rounded-xl hover:bg-surface-dim transition-all shadow-sm active:scale-95">
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
            <!-- Filters -->
            <div class="max-w-[1400px] mx-auto mb-6 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex flex-col space-y-1">
                        <label class="text-[9px] font-black text-outline uppercase tracking-widest ml-1">Status</label>
                        <select wire:model.live="statusFilter" class="bg-white border border-surface-dim px-4 py-2 rounded-xl text-xs font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                            <option value="">Semua Status</option>
                            <option value="AVAILABLE">Tersedia (Available)</option>
                            <option value="USED">Terpakai (Used)</option>
                            <option value="CANCELLED">Dibatalkan (Cancelled)</option>
                            <option value="LOST">Hilang (Lost)</option>
                        </select>
                    </div>
                    
                    @if($role === 'Admin')
                    <div class="flex flex-col space-y-1">
                        <label class="text-[9px] font-black text-outline uppercase tracking-widest ml-1">Cabang</label>
                        <select wire:model.live="branchFilter" class="bg-white border border-surface-dim px-4 py-2 rounded-xl text-xs font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                <div class="text-right">
                    <p class="text-[10px] text-outline font-bold uppercase tracking-widest">Total Record: {{ $bilyets->total() }}</p>
                </div>
            </div>

            <!-- Bilyet List Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-surface-dim overflow-hidden max-w-[1400px] mx-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-surface border-b border-surface-dim uppercase text-[10px] tracking-widest font-bold text-outline">
                            <th class="px-6 py-4">No. Fisik</th>
                            <th class="px-6 py-4">Kode Referensi</th>
                            <th class="px-6 py-4">Cabang</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Terdaftar Oleh</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-dim">
                        @forelse($bilyets as $b)
                            <tr class="group hover:bg-surface/30 transition-all">
                                <td class="px-6 py-4">
                                    <span class="text-[11px] font-black text-primary tracking-[2px] bg-primary/5 px-2 py-1 rounded-lg border border-primary/20">{{ $b->bilyet_number }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-[10px] font-bold text-outline uppercase tracking-wider">{{ $b->kode_bilyet }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-[10px] font-bold text-outline uppercase">{{ $b->branch->name ?? 'Global' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider
                                        @if($b->status === 'AVAILABLE') bg-emerald-50 text-emerald-600 border border-emerald-100
                                        @elseif($b->status === 'USED') bg-blue-50 text-blue-600 border border-blue-100
                                        @elseif($b->status === 'CANCELLED') bg-rose-50 text-rose-600 border border-rose-100
                                        @else bg-amber-50 text-amber-600 border border-amber-100 @endif">
                                        {{ $b->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-[10px] font-bold text-primary">{{ $b->creator->name ?? 'System' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-[10px] font-medium text-outline uppercase tracking-tighter">{{ $b->created_at->format('d M Y, H:i') }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($b->status === 'AVAILABLE')
                                        <div class="flex justify-end space-x-1">
                                            <button wire:click="updateStatus({{ $b->id }}, 'CANCELLED')" class="p-2 text-outline hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Batalkan">
                                                <span class="material-symbols-outlined text-sm">block</span>
                                            </button>
                                            <button wire:click="updateStatus({{ $b->id }}, 'LOST')" class="p-2 text-outline hover:text-amber-600 hover:bg-amber-50 rounded-xl transition-all" title="Laporkan Hilang">
                                                <span class="material-symbols-outlined text-sm">inventory_2</span>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center space-y-3 opacity-20">
                                        <span class="material-symbols-outlined text-6xl">inventory</span>
                                        <p class="text-xs font-black uppercase tracking-[0.3em]">Data Bilyet Kosong</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($bilyets->hasPages())
                    <div class="px-6 py-4 border-t border-surface-dim bg-surface/30">
                        {{ $bilyets->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Register Range Form -->
            <div class="max-w-2xl mx-auto pb-20">
                <div class="bg-white rounded-[2.5rem] shadow-xl border border-surface-dim overflow-hidden">
                    <div class="p-12 space-y-10">
                        <div class="flex items-center space-x-4">
                            <span class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-xl">library_add</span>
                            </span>
                            <div>
                                <h3 class="text-sm font-black text-primary uppercase tracking-widest">Register Range Bilyet</h3>
                                <p class="text-[10px] text-outline font-bold">Daftarkan bilyet fisik dalam jumlah banyak sekaligus</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-12 gap-6">
                            <div class="col-span-12 md:col-span-6 space-y-2">
                                <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Prefix Kode</label>
                                <input wire:model="prefix" type="text" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-black focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all uppercase" placeholder="e.g. KSM/SB">
                                @error('prefix') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-6 md:col-span-3 space-y-2">
                                <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Bulan (MM)</label>
                                <input wire:model="month" type="text" maxlength="2" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-black text-center focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" placeholder="04">
                                @error('month') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-6 md:col-span-3 space-y-2">
                                <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Tahun (YYYY)</label>
                                <input wire:model="year" type="text" maxlength="4" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-black text-center focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" placeholder="2026">
                                @error('year') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Sequence Mulai</label>
                                <input wire:model="start_sequence" type="number" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-black focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" placeholder="e.g. 1">
                                @error('start_sequence') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Sequence Akhir</label>
                                <input wire:model="end_sequence" type="number" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-black focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all" placeholder="e.g. 100">
                                @error('end_sequence') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Padding Digit</label>
                                <input wire:model="padding" type="number" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-black focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                                <p class="text-[8px] text-outline font-bold italic opacity-60">Contoh: Padding 5 = 00001</p>
                                @error('padding') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Cabang Pemilik</label>
                                <select wire:model="branch_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-black focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all cursor-pointer">
                                    <option value="">Pilih Cabang...</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="p-6 bg-primary/[0.02] border border-primary/10 rounded-3xl space-y-3">
                            <h4 class="text-[10px] font-black text-primary uppercase tracking-widest flex items-center space-x-2">
                                <span class="material-symbols-outlined text-sm">visibility</span>
                                <span>Preview Format</span>
                            </h4>
                            <div class="bg-white p-4 rounded-2xl border border-surface-dim flex items-center justify-between">
                                <div class="space-y-1">
                                    <p class="text-[8px] text-outline font-black uppercase">No. Fisik Pertama</p>
                                    <p class="text-[11px] font-black text-primary">{{ str_pad($start_sequence ?: 1, $padding ?: 5, '0', STR_PAD_LEFT) }}</p>
                                </div>
                                <div class="h-8 w-[1px] bg-surface-dim"></div>
                                <div class="space-y-1 text-right">
                                    <p class="text-[8px] text-outline font-black uppercase">Kode Referensi</p>
                                    <p class="text-[11px] font-black text-primary">{{ $prefix }}{{ str_pad($start_sequence ?: 1, $padding ?: 5, '0', STR_PAD_LEFT) }}/{{ $month }}/{{ $year }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-8 flex justify-end items-center space-x-6">
                            <div wire:loading class="flex items-center space-x-2 text-primary">
                                <span class="material-symbols-outlined animate-spin text-sm">cycle</span>
                                <span class="text-[9px] font-black uppercase tracking-widest">Memproses...</span>
                            </div>
                            <button wire:click="registerBilyets" class="bg-primary hover:bg-primary-dim text-white px-10 py-5 rounded-[1.5rem] font-black text-xs uppercase tracking-widest shadow-xl shadow-primary/20 transition-all active:scale-95 flex items-center space-x-3">
                                <span class="material-symbols-outlined text-sm">send</span>
                                <span>Daftarkan Bilyet</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
