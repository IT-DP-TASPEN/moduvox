<div class="p-0">
    <x-header title="Master Rekanan" subtitle="Kelola data mitra dan pihak rekanan koperasi" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari nama, kode rekanan..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 w-56 shadow-sm focus:outline-none">
                </div>
                <button wire:click="openCreate" class="flex items-center space-x-2 bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-sm">person_add</span>
                    <span>Tambah Rekanan</span>
                </button>
            </div>
        </x-slot>
    </x-header>

    <div class="p-10 space-y-6">
        @if(session('success'))
            <div class="px-5 py-3 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-700 text-xs font-bold flex items-center space-x-2">
                <span class="material-symbols-outlined text-sm">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Form Slide-in -->
        @if($showForm)
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 p-8 animate-in slide-in-from-top-4 duration-300">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide">{{ $editId ? 'Edit Rekanan' : 'Tambah Rekanan Baru' }}</h3>
                <button wire:click="$set('showForm', false)" class="p-2 hover:bg-slate-100 rounded-xl transition-all">
                    <span class="material-symbols-outlined text-sm text-slate-400">close</span>
                </button>
            </div>
            <form wire:submit.prevent="save">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Basic Info -->
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Rekanan <span class="text-rose-500">*</span></label>
                        <input wire:model="name" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                        @error('name') <span class="text-[9px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Contact Person</label>
                        <input wire:model="contact_person" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. Telepon</label>
                        <input wire:model="phone" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Email</label>
                        <input wire:model="email" type="email" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">NPWP</label>
                        <input wire:model="npwp" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Bank & No. Rekening</label>
                        <input wire:model="bank_name" type="text" placeholder="Nama Bank" class="w-full px-5 py-2.5 bg-white border border-slate-200 rounded-xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all mb-1.5">
                        <input wire:model="bank_account_no" type="text" placeholder="No. Rekening" class="w-full px-5 py-2.5 bg-white border border-slate-200 rounded-xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Alamat</label>
                        <textarea wire:model="address" rows="2" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all"></textarea>
                    </div>
                    <div class="space-y-2 flex flex-col justify-center">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status</label>
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="w-5 h-5 text-slate-900 rounded focus:ring-slate-900">
                            <span class="text-sm font-bold text-slate-700">Rekanan Aktif</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-slate-100">
                    <button type="button" wire:click="$set('showForm', false)" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all">Batal</button>
                    <button type="submit" class="px-8 py-2.5 bg-slate-900 text-white font-bold text-xs rounded-xl hover:shadow-lg transition-all flex items-center space-x-2">
                        <div wire:loading wire:target="save" class="w-4 h-4 border-2 border-slate-400 border-t-white rounded-full animate-spin"></div>
                        <span>{{ $editId ? 'Simpan Perubahan' : 'Tambah Rekanan' }}</span>
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- Table -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center w-20">AKSI</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Kode</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Nama Rekanan</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">PIC & Kontak</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Bank</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($rekanan as $r)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-6 text-center">
                                <button wire:click="openEdit({{ $r->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-slate-900 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                            </td>
                            <td class="py-4 px-6"><span class="text-[10px] font-black text-indigo-600 tracking-widest">{{ $r->rekanan_code }}</span></td>
                            <td class="py-4 px-6">
                                <p class="text-xs font-black text-slate-900">{{ $r->name }}</p>
                                <p class="text-[9px] text-slate-400 font-bold">{{ $r->address ?? '-' }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-xs font-bold text-slate-900">{{ $r->contact_person ?? '-' }}</p>
                                <p class="text-[9px] text-slate-400 font-bold">{{ $r->phone ?? '-' }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-xs font-bold text-slate-900">{{ $r->bank_name ?? '-' }}</p>
                                <p class="text-[9px] text-slate-400 font-bold tracking-widest">{{ $r->bank_account_no ?? '-' }}</p>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-2 py-0.5 text-[9px] font-black rounded uppercase tracking-widest border {{ $r->is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-100 text-slate-400 border-slate-200' }}">
                                    {{ $r->is_active ? 'AKTIF' : 'NON-AKTIF' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-20 text-center">
                                <span class="material-symbols-outlined text-5xl text-slate-200 mb-3">handshake</span>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Belum ada data rekanan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rekanan->hasPages())
                <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                    {{ $rekanan->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </div>
    </div>
</div>
