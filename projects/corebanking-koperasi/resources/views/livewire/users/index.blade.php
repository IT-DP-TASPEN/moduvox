<div>
    <x-header title="Manajemen User" subtitle="Daftar pengguna dalam sistem" :user="$user" :role="$role">
        <x-slot:actions>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input wire:model.live="search" type="text" placeholder="Cari user..."
                    class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all w-48 font-medium">
            </div>
            @can('users.create')
            <button @click="$dispatch('open-create-modal')"
                class="flex items-center space-x-2 bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Tambah User</span>
            </button>
            @endcan
        </x-slot:actions>
    </x-header>

    <div class="p-10" x-data="{ showCreate: @entangle('showCreateModal'), showEdit: @entangle('showEditModal'), showDelete: @entangle('confirmingDeletion') }"
        @open-create-modal.window="showCreate = true">
        
        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 flex items-center space-x-6">
                <div class="w-14 h-14 rounded-2xl bg-slate-900 text-white flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">group</span>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-[0.2em] font-extrabold text-slate-400 mb-1">Total Users</p>
                    <p class="text-3xl font-headline font-bold text-slate-900">{{ $stats['total'] }}</p>
                </div>
            </div>
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 flex items-center space-x-6">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">person_check</span>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-[0.2em] font-extrabold text-slate-400 mb-1">Active</p>
                    <p class="text-3xl font-headline font-bold text-emerald-600">{{ $stats['active'] }}</p>
                </div>
            </div>
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 flex items-center space-x-6">
                <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">person_off</span>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-[0.2em] font-extrabold text-slate-400 mb-1">Inactive</p>
                    <p class="text-3xl font-headline font-bold text-rose-600">{{ $stats['inactive'] }}</p>
                </div>
            </div>
        </div>

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
                        <th class="px-10 py-6">Pengguna</th>
                        <th class="px-6 py-6">Akses & Status</th>
                        <th class="px-6 py-6">Unit Kerja</th>
                        <th class="px-6 py-6">Aktivitas</th>
                        <th class="px-10 py-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($usersList as $u)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-10 py-6">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-bold text-sm shadow-lg shadow-slate-900/10">
                                    {{ substr($u->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm leading-tight mb-1">{{ $u->name }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ $u->username }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-6">
                            <div class="flex flex-col space-y-2">
                                <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-lg text-[9px] font-bold w-fit uppercase tracking-wider border border-slate-200/50">{{ $u->getRoleNames()->first() }}</span>
                                <div class="flex items-center space-x-2 {{ $u->is_active ? 'text-emerald-600' : 'text-slate-300' }} text-[9px] font-black uppercase tracking-widest ml-1">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $u->is_active ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-slate-300' }}"></span>
                                    <span>{{ $u->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-6">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-900 mb-1">{{ $u->company->company_name ?? '-' }}</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ $u->branch->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-6">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest italic">{{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'No Activity' }}</p>
                        </td>
                        <td class="px-10 py-6 text-right">
                            <div class="flex justify-end space-x-2">
                                @if(auth()->id() !== $u->id)
                                <button wire:click="impersonateUser({{ $u->id }})" class="w-9 h-9 flex items-center justify-center bg-slate-50 hover:bg-emerald-500 hover:text-white rounded-xl text-slate-400 transition-all duration-300 border border-slate-100 shadow-sm" title="Impersonate / Login Sebagai User Ini"><span class="material-symbols-outlined text-sm">visibility</span></button>
                                @endif
                                
                                @can('users.update')
                                <button wire:click="editUser({{ $u->id }})" class="w-9 h-9 flex items-center justify-center bg-slate-50 hover:bg-slate-900 hover:text-white rounded-xl text-slate-400 transition-all duration-300 border border-slate-100 shadow-sm"><span class="material-symbols-outlined text-sm">edit</span></button>
                                @endcan
                                
                                @can('users.delete')
                                <button wire:click="confirmDelete({{ $u->id }}, '{{ $u->name }}')" class="w-9 h-9 flex items-center justify-center bg-slate-50 hover:bg-rose-500 hover:text-white rounded-xl text-slate-400 transition-all duration-300 border border-slate-100 shadow-sm"><span class="material-symbols-outlined text-sm">delete</span></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-10 py-6 bg-slate-50 border-t border-slate-200">
                {{ $usersList->links() }}
            </div>
        </div>

        <div x-show="showCreate" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
            <div @click.away="showCreate = false" class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-slide-up">
                <div class="pearl-gradient p-8 text-white flex justify-between items-center relative">
                    <div class="relative z-10">
                        <h3 class="text-2xl font-headline font-bold tracking-tight">Tambah User</h3>
                        <p class="text-white/60 text-[10px] font-extrabold uppercase tracking-[0.2em] mt-1">Sistem Manajemen Pengguna</p>
                    </div>
                    <button @click="showCreate = false" class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors relative z-10">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                    <!-- Decorative Circle -->
                    <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                </div>

                <form wire:submit="saveUser" class="p-8">
                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Lengkap</label>
                            <input wire:model="new_name" type="text" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900">
                            @error('new_name') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Email</label>
                            <input wire:model="new_email" type="email" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900">
                            @error('new_email') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Username</label>
                            <input wire:model="new_username" type="text" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900">
                            @error('new_username') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Password</label>
                            <input wire:model="new_password" type="password" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900">
                            @error('new_password') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Perusahaan</label>
                            <select wire:model="company_id" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900">
                                <option value="">Pilih Perusahaan</option>
                                @foreach($allCompanies as $c)
                                <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Cabang</label>
                            <select wire:model="branch_id" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900">
                                <option value="">Pilih Cabang</option>
                                @foreach($allBranches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Hak Akses (Role)</label>
                            <select wire:model="new_role" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900 uppercase">
                                <option value="">Pilih Role</option>
                                @foreach($allRoles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status Keaktifan</label>
                            <div class="flex items-center space-x-4 p-3.5 bg-slate-50 border border-slate-200 rounded-2xl">
                                <span class="text-xs font-bold {{ $is_active ? 'text-emerald-600' : 'text-slate-400' }} uppercase">{{ $is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                                <button type="button" wire:click="$toggle('is_active')" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $is_active ? 'bg-emerald-500' : 'bg-slate-300' }}">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <button type="button" @click="showCreate = false" class="flex-1 py-4 px-6 border border-slate-200 rounded-2xl font-bold text-slate-400 hover:bg-slate-50 transition-all">Batalkan</button>
                        <button type="submit" class="flex-[2] py-4 px-6 bg-slate-900 text-white rounded-2xl font-bold hover:shadow-2xl hover:shadow-slate-900/20 transition-all active:scale-95">Simpan User Baru</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="showEdit" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
            <div @click.away="showEdit = false" class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-slide-up">
                <div class="pearl-gradient p-8 text-white flex justify-between items-center relative">
                    <div class="relative z-10">
                        <h3 class="text-2xl font-headline font-bold tracking-tight text-white">Perbarui User</h3>
                        <p class="text-white/60 text-[10px] font-extrabold uppercase tracking-[0.2em] mt-1">ID: #{{ $userId }}</p>
                    </div>
                    <button @click="showEdit = false" class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors relative z-10">
                        <span class="material-symbols-outlined text-white">close</span>
                    </button>
                    <!-- Decorative Circle -->
                    <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                </div>

                <form wire:submit="updateUser" class="p-8">
                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Lengkap</label>
                            <input wire:model="new_name" type="text" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Email</label>
                            <input wire:model="new_email" type="email" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Username</label>
                            <input wire:model="new_username" type="text" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Password Baru (Opsional)</label>
                            <input wire:model="new_password" type="password" placeholder="Biarkan kosong jika tetap" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Hak Akses (Role)</label>
                            <select wire:model="new_role" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900 uppercase">
                                @foreach($allRoles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status Keaktifan</label>
                            <div class="flex items-center space-x-4 p-3.5 bg-slate-50 border border-slate-200 rounded-2xl">
                                <span class="text-xs font-bold {{ $is_active ? 'text-emerald-600' : 'text-slate-400' }} uppercase">{{ $is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                                <button type="button" wire:click="$toggle('is_active')" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $is_active ? 'bg-emerald-500' : 'bg-slate-300' }}">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <button type="button" @click="showEdit = false" class="flex-1 py-4 px-6 border border-slate-200 rounded-2xl font-bold text-slate-400 hover:bg-slate-50 transition-all">Batalkan</button>
                        <button type="submit" class="flex-[2] py-4 px-6 bg-slate-900 text-white rounded-2xl font-bold hover:shadow-2xl hover:shadow-slate-900/20 transition-all active:scale-95">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Modal -->
        <div x-show="showDelete" class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
            <div @click.away="showDelete = false" class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden p-8 text-center animate-slide-up">
                <div class="w-16 h-16 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <span class="material-symbols-outlined text-3xl">no_accounts</span>
                </div>
                <h3 class="text-xl font-headline font-bold text-slate-900 mb-2">Hapus Pengguna?</h3>
                <p class="text-xs text-slate-400 font-medium mb-8 leading-relaxed">Anda akan menghapus user <span class="text-rose-500 font-bold">"{{ $deletingName }}"</span> secara permanen. Tindakan ini tidak dapat dibatalkan.</p>
                <div class="flex flex-col space-y-3">
                    <button wire:click="deleteUser" class="w-full bg-rose-500 text-white py-4 rounded-2xl font-bold text-sm shadow-lg shadow-rose-500/30 hover:shadow-rose-500/40 transition-all active:scale-95">Ya, Hapus Permanen</button>
                    <button @click="showDelete = false" class="w-full bg-slate-100 text-slate-400 py-4 rounded-2xl font-bold border border-slate-200 text-sm hover:bg-slate-200 transition-all">Batalkan</button>
                </div>
            </div>
        </div>
    </div>
</div>