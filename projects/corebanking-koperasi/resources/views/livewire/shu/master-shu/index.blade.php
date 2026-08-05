<div>
    <x-header title="Master SHU" subtitle="Manajemen kriteria SHU (Sisa Hasil Usaha)" :user="Auth::user()" :role="Auth::user()->getRoleNames()->first() ?? 'Admin'">
        <x-slot:actions>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input wire:model.live="search" type="text" placeholder="Cari CIF atau Kriteria..."
                    class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all w-48 font-medium">
            </div>
            <button wire:click="create"
                class="flex items-center space-x-2 bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Tambah Data</span>
            </button>
        </x-slot:actions>
    </x-header>

    <div class="p-10" x-data="{ showCreate: @entangle('showCreateModal'), showEdit: @entangle('showEditModal'), showDelete: @entangle('confirmingDeletion') }">
        
        @if (session()->has('success'))
        <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-[2rem] border border-emerald-100 flex items-center mb-10 animate-fade-in shadow-sm">
            <span class="material-symbols-outlined mr-3 text-lg">check_circle</span>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
        @endif
        
        @if (session()->has('error'))
        <div class="bg-rose-50 text-rose-700 px-6 py-4 rounded-[2rem] border border-rose-100 flex items-center mb-10 animate-fade-in shadow-sm">
            <span class="material-symbols-outlined mr-3 text-lg">error</span>
            <span class="font-bold text-sm">{{ session('error') }}</span>
        </div>
        @endif

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 text-[10px] uppercase tracking-[0.2em] font-extrabold">
                        <th class="px-10 py-6">CIF</th>
                        <th class="px-6 py-6">Kriteria</th>
                        <th class="px-6 py-6">Rekening Penerima</th>
                        <th class="px-10 py-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($masterShus as $shu)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-10 py-6">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-900 text-sm leading-tight mb-1">{{ $shu->cif->name ?? '-' }}</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ $shu->cif->cif_no ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-6">
                            <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-lg text-[10px] font-bold w-fit uppercase tracking-wider border border-slate-200/50">
                                {{ $shu->kriteria }}
                            </span>
                        </td>
                        <td class="px-6 py-6">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-900 text-sm leading-tight mb-1">{{ $shu->savingAccount->account_no ?? '-' }}</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ $shu->savingAccount ? 'Saldo: Rp ' . number_format($shu->savingAccount->balance, 0, ',', '.') : 'Tidak Diatur' }}</span>
                            </div>
                        </td>
                        <td class="px-10 py-6 text-right">
                            <div class="flex justify-end space-x-2">
                                <button wire:click="edit({{ $shu->id }})" class="w-9 h-9 flex items-center justify-center bg-slate-50 hover:bg-slate-900 hover:text-white rounded-xl text-slate-400 transition-all duration-300 border border-slate-100 shadow-sm"><span class="material-symbols-outlined text-sm">edit</span></button>
                                
                                <button wire:click="confirmDelete({{ $shu->id }})" class="w-9 h-9 flex items-center justify-center bg-slate-50 hover:bg-rose-500 hover:text-white rounded-xl text-slate-400 transition-all duration-300 border border-slate-100 shadow-sm"><span class="material-symbols-outlined text-sm">delete</span></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-10 py-10 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-3xl text-slate-400">inventory_2</span>
                                </div>
                                <p class="text-sm font-bold text-slate-600">Belum ada data Master SHU</p>
                                <p class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 mt-1">Silakan tambah data baru</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($masterShus->hasPages())
            <div class="px-10 py-6 bg-slate-50 border-t border-slate-200">
                {{ $masterShus->links() }}
            </div>
            @endif
        </div>

        <!-- Create/Edit Modal -->
        <div x-show="showCreate || showEdit" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
            <div @click.away="showCreate = false; showEdit = false;" class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden animate-slide-up">
                <div class="pearl-gradient p-8 text-white flex justify-between items-center relative">
                    <div class="relative z-10">
                        <h3 class="text-2xl font-headline font-bold tracking-tight" x-text="showEdit ? 'Edit Master SHU' : 'Tambah Master SHU'"></h3>
                        <p class="text-white/60 text-[10px] font-extrabold uppercase tracking-[0.2em] mt-1">Sistem Manajemen SHU</p>
                    </div>
                    <button wire:click="resetForm" @click="showCreate = false; showEdit = false;" class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors relative z-10">
                        <span class="material-symbols-outlined text-white">close</span>
                    </button>
                    <!-- Decorative Circle -->
                    <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                </div>

                <form wire:submit="save" class="p-8">
                    <div class="space-y-6 mb-8">
                        <div class="space-y-2 relative">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Cari CIF</label>
                            @if($selectedCifName)
                                <div class="flex items-center justify-between w-full px-5 py-3.5 bg-slate-50 border border-emerald-200 rounded-2xl">
                                    <span class="font-bold text-sm text-slate-900">{{ $selectedCifName }}</span>
                                    <button type="button" wire:click="resetForm" class="text-rose-500 hover:text-rose-700">
                                        <span class="material-symbols-outlined text-sm">close</span>
                                    </button>
                                </div>
                            @else
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                                    <input wire:model.live.debounce.300ms="cifSearch" type="text" placeholder="Ketik nama atau nomor CIF..." class="w-full pl-11 pr-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900">
                                </div>
                                @if(count($cifSearchResults) > 0)
                                    <div class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                        @foreach($cifSearchResults as $c)
                                            <button type="button" wire:click="selectCif({{ $c->id }}, '{{ $c->cif_no }}', '{{ addslashes($c->name) }}')" class="w-full text-left px-4 py-3 hover:bg-slate-50 border-b border-slate-100 last:border-0 transition-colors">
                                                <div class="font-bold text-sm text-slate-900">{{ $c->name }}</div>
                                                <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $c->cif_no }}</div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                            @error('cif_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kriteria</label>
                            <select wire:model="kriteria" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900">
                                <option value="">-- Pilih Kriteria --</option>
                                <option value="PEMEGANG SAHAM">Pemegang Saham</option>
                                <option value="PENGAWAS">Pengawas</option>
                                <option value="PENGURUS">Pengurus</option>
                                <option value="ANGGOTA">Anggota</option>
                            </select>
                            @error('kriteria') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Rekening Penerima SHU</label>
                            <select wire:model="saving_account_id" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900" {{ empty($availableSavingAccounts) ? 'disabled' : '' }}>
                                <option value="">-- Pilih Rekening --</option>
                                @foreach($availableSavingAccounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_no }} - {{ $acc->balance ? 'Rp '.number_format($acc->balance,0,',','.') : 'Rp 0' }}</option>
                                @endforeach
                            </select>
                            @if(empty($availableSavingAccounts) && $selectedCifName)
                                <span class="text-[10px] text-slate-400 font-bold ml-1 italic">CIF ini belum memiliki rekening simpanan aktif.</span>
                            @endif
                            @error('saving_account_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <button type="button" wire:click="resetForm" @click="showCreate = false; showEdit = false;" class="flex-1 py-4 px-6 border border-slate-200 rounded-2xl font-bold text-slate-400 hover:bg-slate-50 transition-all">Batalkan</button>
                        <button type="submit" class="flex-[2] py-4 px-6 bg-slate-900 text-white rounded-2xl font-bold hover:shadow-2xl hover:shadow-slate-900/20 transition-all active:scale-95">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Modal -->
        <div x-show="showDelete" class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
            <div @click.away="showDelete = false" class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden p-8 text-center animate-slide-up">
                <div class="w-16 h-16 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <span class="material-symbols-outlined text-3xl">delete_forever</span>
                </div>
                <h3 class="text-xl font-headline font-bold text-slate-900 mb-2">Hapus Master SHU?</h3>
                <p class="text-xs text-slate-400 font-medium mb-8 leading-relaxed">Anda yakin ingin menghapus data kriteria SHU ini? Tindakan ini tidak dapat dibatalkan.</p>
                <div class="flex flex-col space-y-3">
                    <button wire:click="delete" class="w-full bg-rose-500 text-white py-4 rounded-2xl font-bold text-sm shadow-lg shadow-rose-500/30 hover:shadow-rose-500/40 transition-all active:scale-95">Ya, Hapus Permanen</button>
                    <button @click="showDelete = false" class="w-full bg-slate-100 text-slate-400 py-4 rounded-2xl font-bold border border-slate-200 text-sm hover:bg-slate-200 transition-all">Batalkan</button>
                </div>
            </div>
        </div>
    </div>
</div>
