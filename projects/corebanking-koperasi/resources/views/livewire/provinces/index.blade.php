<div class="p-0">
    <x-header title="Data Provinsi" subtitle="Kelola daftar provinsi di Indonesia" :user="$user" :role="$role">
        <x-slot:actions>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input wire:model.live="search" type="text" placeholder="Cari provinsi..."
                    class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-medium">
            </div>
            @can('provinces.create')
            <button wire:click="create"
                class="flex items-center space-x-2 bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Tambah Provinsi</span>
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
                        <th class="px-10 py-6">ID Provinsi</th>
                        <th class="px-10 py-6">Nama Provinsi</th>
                        <th class="px-10 py-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-10 py-6">
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-bold font-mono tracking-wider border border-slate-200/50">{{ $item->id }}</span>
                        </td>
                        <td class="px-10 py-6">
                            <span class="text-sm font-bold text-slate-900">{{ $item->nama }}</span>
                        </td>
                        <td class="px-10 py-6 text-right">
                            <div class="flex justify-end space-x-2">
                                @can('provinces.update')
                                <button wire:click="edit({{ $item->id }})" class="w-9 h-9 flex items-center justify-center bg-slate-50 hover:bg-slate-900 hover:text-white rounded-xl text-slate-400 transition-all duration-300 border border-slate-100 shadow-sm">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                @endcan
                                
                                @can('provinces.delete')
                                <button wire:confirm="Yakin ingin menghapus provinsi ini?" wire:click="delete({{ $item->id }})" class="w-9 h-9 flex items-center justify-center bg-slate-50 hover:bg-rose-500 hover:text-white rounded-xl text-slate-400 transition-all duration-300 border border-slate-100 shadow-sm">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-10 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <span class="material-symbols-outlined text-5xl text-slate-200 mb-4">folder_open</span>
                                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Data provinsi belum tersedia</p>
                            </div>
                        </td>
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
        <div @click.away="open = false" class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden animate-slide-up">
            <div class="pearl-gradient p-8 text-white flex justify-between items-center relative">
                <div class="relative z-10">
                    <h3 class="text-2xl font-headline font-bold tracking-tight">{{ $editingId ? 'Edit Provinsi' : 'Tambah Provinsi' }}</h3>
                    <p class="text-white/60 text-[10px] font-extrabold uppercase tracking-[0.2em] mt-1">Master Data Geografis</p>
                </div>
                <button @click="open = false" class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors relative z-10">
                    <span class="material-symbols-outlined">close</span>
                </button>
                <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            </div>
            
            <form wire:submit.prevent="save" class="p-8 space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Provinsi</label>
                    <input type="text" wire:model="nama" 
                        class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900"
                        placeholder="Contoh: JAWA BARAT">
                    @error('nama') <span class="text-[10px] text-rose-500 font-bold ml-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="pt-4 flex items-center space-x-3">
                    <button type="button" @click="open = false" class="flex-1 py-4 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl font-bold transition-all text-xs uppercase tracking-widest">
                        Batal
                    </button>
                    <button type="submit" class="flex-[2] py-4 bg-slate-900 hover:shadow-lg hover:shadow-slate-900/20 text-white rounded-2xl font-bold transition-all text-xs uppercase tracking-widest">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
