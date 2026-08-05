<div>
    <!-- Header -->
    <x-header title="Chart of Accounts" subtitle="Manajemen bagan akun dan struktur hierarki keuangan" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first()">
        <x-slot:actions>
             @if($viewMode === 'list')
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm">search</span>
                        <input wire:model.live="search" type="text" placeholder="Cari akun..." class="pl-10 pr-4 py-2 bg-surface border border-surface-dim rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all w-48 font-medium">
                    </div>
                    @can('coa.create')
                    <button wire:click="create()" class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-xl hover:bg-primary-dim transition-all shadow-sm active:scale-95">
                        <span class="material-symbols-outlined text-sm">add</span>
                        <span class="text-xs font-bold uppercase tracking-wider">Akun Utama</span>
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
        @if($viewMode === 'list')
            <div class="mb-4 flex items-center justify-between px-2">
                <span class="text-[10px] font-black text-outline uppercase tracking-[0.2em]">Total Akun: {{ \App\Models\Coa::count() }}</span>
            </div>
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-surface-dim overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface border-b border-surface-dim uppercase text-[10px] tracking-widest font-black text-outline">
                            <th class="px-6 py-6 w-[240px]">Kode Akun</th>
                            <th class="px-6 py-6">Nama Akun</th>
                            <th class="px-6 py-6 w-[180px]">Tipe</th>
                            <th class="px-6 py-6 w-[160px]">Karakter</th>
                            <th class="px-6 py-6 text-right w-[120px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-dim">
                        @foreach($coas as $coa)
                            @include('livewire.coa.coa-row', ['coa' => $coa, 'level' => 0, 'renderChildren' => blank($search)])
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <!-- Form Card -->
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-[2.5rem] shadow-xl border border-surface-dim overflow-hidden">
                    <div class="p-12 space-y-8">
                        <div class="grid grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Kode Akun</label>
                                <input wire:model="coa_code" type="text" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 transition-all uppercase" placeholder="e.g. 1100">
                                @error('coa_code') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Nama Akun</label>
                                <input wire:model="name" type="text" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 transition-all" placeholder="e.g. Kas Besar">
                                @error('name') <span class="text-[9px] text-red-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Tipe Akun</label>
                                <select wire:model="type" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 transition-all cursor-pointer">
                                    <option value="ASSET">ASET</option>
                                    <option value="LIABILITY">KEWAJIBAN</option>
                                    <option value="EQUITY">MODAL</option>
                                    <option value="REVENUE">PENDAPATAN</option>
                                    <option value="EXPENSE">BEBAN</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Parent Akun (Header)</label>
                                <select wire:model="parent_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 transition-all cursor-pointer">
                                    <option value="">-- Akun Utama --</option>
                                    @foreach($allParents as $p)
                                        <option value="{{ $p->id }}">{{ $p->coa_code }} - {{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                             <div class="flex items-center justify-between p-5 bg-surface rounded-2xl border border-surface-dim">
                                <div>
                                    <h4 class="text-[10px] font-black text-primary uppercase">Bisa Transaksi?</h4>
                                    <p class="text-[9px] text-outline font-medium">Bisa dipilih di jurnal jika sebagai 'Leaf'</p>
                                </div>
                                <input type="checkbox" wire:model="is_leaf" class="w-5 h-5 rounded-lg border-surface-dim text-primary focus:ring-primary">
                            </div>
                            <div class="flex items-center justify-between p-5 bg-surface rounded-2xl border border-surface-dim">
                                <div>
                                    <h4 class="text-[10px] font-black text-primary uppercase">Akun Kas/Bank?</h4>
                                    <p class="text-[9px] text-outline font-medium">Masuk dalam kategori Likuiditas</p>
                                </div>
                                <input type="checkbox" wire:model="is_cash" class="w-5 h-5 rounded-lg border-surface-dim text-primary focus:ring-primary">
                            </div>
                        </div>

                        <div class="pt-8 border-t border-surface-dim flex justify-end">
                            <button wire:click="save" class="bg-primary hover:bg-primary-dim text-white px-10 py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-primary/20 transition-all active:scale-95">
                                Simpan Akun
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
