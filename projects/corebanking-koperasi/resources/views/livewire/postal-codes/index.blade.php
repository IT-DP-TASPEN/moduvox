<div class="p-0">
    <x-header title="Data Kode Pos" subtitle="Manajemen kode pos tingkat nasional" :user="$user" :role="$role">
        <x-slot:actions>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input wire:model.live="search" type="text" placeholder="Cari Kode Pos..."
                    class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all w-48 font-medium">
            </div>
            @can('postal-codes.create')
            <button wire:click="create"
                class="flex items-center space-x-2 bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Tambah Kode Pos</span>
            </button>
            @endcan
        </x-slot:actions>
    </x-header>

    <div class="p-10">
        @if (session()->has('success'))
        <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-[2rem] border border-emerald-100 flex items-center mb-10 animate-fade-in shadow-sm">
            <span class="material-symbols-outlined mr-3 text-lg">check_circle</span>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
        @endif

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 text-[10px] uppercase tracking-[0.2em] font-extrabold">
                        <th class="px-10 py-6">ID / Kode Pos</th>
                        <th class="px-6 py-6">Kelurahan & Kecamatan</th>
                        <th class="px-6 py-6">Kota / Provinsi</th>
                        <th class="px-10 py-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-10 py-6">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-slate-900 leading-tight">{{ $item->code }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">ID: {{ $item->id }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-6">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-900 mb-1">{{ $item->subdistrict->nama ?? 'N/A' }}</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Kec. {{ $item->subdistrict->district->nama ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-6">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-900 mb-1">{{ $item->subdistrict->district->city->nama ?? 'N/A' }}</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ $item->subdistrict->district->city->province->nama ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="px-10 py-6 text-right">
                            <div class="flex justify-end space-x-2">
                                @can('postal-codes.update')
                                <button wire:click="edit({{ $item->id }})" class="w-9 h-9 flex items-center justify-center bg-slate-50 hover:bg-slate-900 hover:text-white rounded-xl text-slate-400 transition-all duration-300 border border-slate-100 shadow-sm">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                @endcan
                                
                                @can('postal-codes.delete')
                                <button wire:confirm="Yakin?" wire:click="delete({{ $item->id }})" class="w-9 h-9 flex items-center justify-center bg-slate-50 hover:bg-rose-500 hover:text-white rounded-xl text-slate-400 transition-all duration-300 border border-slate-100 shadow-sm">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-10 py-20 text-center text-slate-400 font-bold uppercase tracking-widest text-xs italic">Data belum tersedia</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($items->hasPages())
            <div class="px-10 py-6 bg-slate-50 border-t border-slate-200">
                {{ $items->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Form -->
    <div x-data="{ open: @entangle('showModal') }" x-show="open" 
        class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm"
        x-transition x-cloak>
        <div @click.away="open = false" class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-slide-up">
            <div class="pearl-gradient p-10 text-white flex justify-between items-center relative">
                <div class="relative z-10">
                    <h3 class="text-2xl font-headline font-bold tracking-tight">{{ $editingId ? 'Edit Kode Pos' : 'Registrasi Kode Pos' }}</h3>
                    <p class="text-white/60 text-[10px] font-extrabold uppercase tracking-[0.2em] mt-1">Sistem Manajemen Geografis Nasional</p>
                </div>
                <button @click="open = false" class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors relative z-10">
                    <span class="material-symbols-outlined">close</span>
                </button>
                <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            </div>
            
            <form wire:submit.prevent="save" class="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Provinsi</label>
                        <select wire:model.live="province_id" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900 shadow-sm">
                            <option value="">-- Pilih --</option>
                            @foreach($provinces as $p) <option value="{{ $p->id }}">{{ $p->nama }}</option> @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kota / Kabupaten</label>
                        <select wire:model.live="city_id" @disabled(!$province_id) class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900 shadow-sm disabled:opacity-50">
                            <option value="">-- Pilih --</option>
                            @foreach($cities as $c) <option value="{{ $c->id }}">{{ $c->nama }}</option> @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kecamatan</label>
                        <select wire:model.live="district_id" @disabled(!$city_id) class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900 shadow-sm disabled:opacity-50">
                            <option value="">-- Pilih --</option>
                            @foreach($districts as $d) <option value="{{ $d->id }}">{{ $d->nama }}</option> @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kelurahan</label>
                        <select wire:model.live="subdistrict_id" @disabled(!$district_id) class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900 shadow-sm disabled:opacity-50">
                            <option value="">-- Pilih --</option>
                            @foreach($subdistricts as $s) <option value="{{ $s->id }}">{{ $s->nama }}</option> @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 pt-4 border-t border-slate-100">
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-900 ml-1">Kode Pos (5 Digit)</label>
                        <input type="text" wire:model="code" maxlength="5" class="w-full px-5 py-4 bg-slate-900 text-white border-none rounded-2xl focus:ring-8 focus:ring-slate-900/10 transition-all font-black text-xl text-center tracking-[0.3em]" placeholder="00000">
                    </div>
                </div>

                <div class="pt-8 flex items-center space-x-3">
                    <button type="button" @click="open = false" class="flex-1 py-4 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl font-bold transition-all text-xs uppercase tracking-widest">
                        Batal
                    </button>
                    <button type="submit" class="flex-[2] py-4 bg-slate-900 hover:shadow-lg hover:shadow-slate-900/20 text-white rounded-2xl font-bold transition-all text-xs uppercase tracking-widest text-center">
                        Simpan Master Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
