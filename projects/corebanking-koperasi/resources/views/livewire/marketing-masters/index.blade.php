<div class="p-0">
    <x-header title="Marketing Master" subtitle="Kelola data tenaga pemasaran cabang" :user="$user" :role="$role">
        <x-slot:actions>
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <select wire:model.live="filter_branch" 
                        class="pl-3 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-bold text-slate-600 appearance-none">
                        <option value="">Semua Cabang</option>
                        @foreach(App\Models\Branch::where('is_active', true)->get() as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                        <span class="material-symbols-outlined text-sm">expand_more</span>
                    </div>
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live="search" type="text" placeholder="Cari marketing..."
                        class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-medium">
                </div>
                @can('marketing-masters.create')
                <button wire:click="create"
                    class="flex items-center space-x-2 bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-sm">add</span>
                    <span>Tambah Marketing</span>
                </button>
                @endcan
            </div>
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
                        <th class="px-10 py-6">Marketing Code</th>
                        <th class="px-6 py-6">Nama</th>
                        <th class="px-6 py-6">Cabang</th>
                        <th class="px-6 py-6">Telepon</th>
                        <th class="px-6 py-6">Status</th>
                        <th class="px-10 py-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-10 py-6">
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-bold font-mono tracking-wider border border-slate-200/50">{{ $item->marketing_code }}</span>
                        </td>
                        <td class="px-6 py-6">
                            <span class="text-sm font-bold text-slate-900">{{ $item->name }}</span>
                        </td>
                        <td class="px-6 py-6">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ $item->branch->name ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-6">
                            <span class="text-xs font-bold text-slate-600">{{ $item->phone ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-6">
                            @if($item->is_active)
                                <span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-lg text-[10px] font-black tracking-widest uppercase border border-emerald-100">Aktif</span>
                            @else
                                <span class="bg-rose-50 text-rose-600 px-3 py-1 rounded-lg text-[10px] font-black tracking-widest uppercase border border-rose-100">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-10 py-6 text-right">
                            <div class="flex justify-end space-x-2">
                                @can('marketing-masters.update')
                                <button wire:click="edit({{ $item->id }})" class="w-9 h-9 flex items-center justify-center bg-slate-50 hover:bg-slate-900 hover:text-white rounded-xl text-slate-400 transition-all duration-300 border border-slate-100 shadow-sm">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                @endcan
                                
                                @can('marketing-masters.delete')
                                <button wire:confirm="Yakin ingin menghapus marketing ini? Data yang terkait mungkin akan ikut bermasalah." wire:click="delete({{ $item->id }})" class="w-9 h-9 flex items-center justify-center bg-slate-50 hover:bg-rose-500 hover:text-white rounded-xl text-slate-400 transition-all duration-300 border border-slate-100 shadow-sm">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-10 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <span class="material-symbols-outlined text-5xl text-slate-200 mb-4">folder_open</span>
                                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Data marketing belum tersedia</p>
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
                    <h3 class="text-2xl font-headline font-bold tracking-tight">{{ $editingId ? 'Edit Marketing' : 'Tambah Marketing' }}</h3>
                    <p class="text-white/60 text-[10px] font-extrabold uppercase tracking-[0.2em] mt-1">Master Data Karyawan</p>
                </div>
                <button @click="open = false" class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors relative z-10">
                    <span class="material-symbols-outlined">close</span>
                </button>
                <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            </div>
            
            <form wire:submit.prevent="save" class="p-8 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Lengkap</label>
                    <input type="text" wire:model="name" 
                        class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900"
                        placeholder="Nama Staff Marketing">
                    @error('name') <span class="text-[10px] text-rose-500 font-bold ml-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Telepon (Opsional)</label>
                    <input type="text" wire:model="phone" 
                        class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900"
                        placeholder="08123456789">
                    @error('phone') <span class="text-[10px] text-rose-500 font-bold ml-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Cabang</label>
                    <div class="relative">
                        <select wire:model="branch_master_id" class="w-full pl-5 pr-12 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900 appearance-none">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                            <span class="material-symbols-outlined text-lg">expand_more</span>
                        </div>
                    </div>
                    @error('branch_master_id') <span class="text-[10px] text-rose-500 font-bold ml-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status Aktivasi</label>
                    <div class="flex items-center space-x-4 p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                        <span class="text-xs font-bold {{ $is_active ? 'text-slate-900' : 'text-slate-400' }}">{{ $is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        <button type="button" wire:click.prevent="$toggle('is_active')" 
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $is_active ? 'bg-slate-900' : 'bg-slate-200' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
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
