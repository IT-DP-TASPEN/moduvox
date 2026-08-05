<div x-data="{ showPermissions: @entangle('showPermissionsModal'), showCreate: @entangle('showCreateModal'), showEdit: @entangle('showEditModal'), showDelete: @entangle('confirmingDeletion') }">
    <!-- Header -->
    <x-header title="Manajemen Role" subtitle="Kelola peran dan hak akses sistem" :user="$user" :role="$roleName">
        <x-slot:actions>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm">search</span>
                <input wire:model.live="search" type="text" placeholder="Cari role..." class="pl-10 pr-4 py-2 bg-surface border border-surface-dim rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all w-48">
            </div>
            @can('roles.create')
            <button @click="showCreate = true" class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-primary/30 transition-all active:scale-95">
                <span class="material-symbols-outlined text-sm">add_moderator</span>
                <span>Tambah Role</span>
            </button>
            @endcan
        </x-slot:actions>
    </x-header>

    <!-- Role Grid Content -->
    <div class="p-8">
        @if (session()->has('success'))
            <div class="bg-green-50 text-green-700 px-6 py-4 rounded-2xl border border-green-100 flex items-center mb-6 animate-fade-in shadow-sm">
                <span class="material-symbols-outlined mr-3 text-lg">check_circle</span>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-50 text-red-700 px-6 py-4 rounded-2xl border border-red-100 flex items-center mb-6 animate-fade-in shadow-sm">
                <span class="material-symbols-outlined mr-3 text-lg">error</span>
                <span class="font-bold">{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        @foreach($rolesList as $roleItem)
        <div class="bg-white rounded-3xl shadow-sm border border-surface-dim overflow-hidden flex flex-col hover:shadow-xl transition-all group">
            <div class="p-6 border-b border-surface-dim {{ $roleItem->is_active ? 'bg-surface/30' : 'bg-gray-50' }}">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="text-lg font-bold {{ $roleItem->is_active ? 'text-primary' : 'text-gray-400' }}">{{ $roleItem->name }}</h3>
                            @if(!$roleItem->is_active)
                                <span class="bg-gray-100 text-gray-500 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Nonaktif</span>
                            @else
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            @endif
                        </div>
                        <p class="text-[10px] uppercase tracking-widest font-bold text-outline mt-1">{{ $roleItem->permissions->count() }} Izin Terpasang</p>
                    </div>
                    <div class="w-10 h-10 rounded-2xl {{ $roleItem->is_active ? 'bg-primary-fixed text-primary' : 'bg-gray-100 text-gray-400' }} flex items-center justify-center">
                        <span class="material-symbols-outlined">{{ $roleItem->is_active ? 'security' : 'shield_with_heart' }}</span>
                    </div>
                </div>
            </div>
            
            <div x-data="{ expanded: false }" class="p-6 flex-grow {{ $roleItem->is_active ? '' : 'opacity-50 grayscale-[0.5]' }}">
                <div class="flex flex-wrap gap-2 items-center">
                    @php $perms = $roleItem->permissions; @endphp
                    @forelse($perms->take(12) as $perm)
                        <span class="bg-surface border border-surface-dim text-outline px-2 py-1 rounded-lg text-[10px] font-medium">{{ $perm->name }}</span>
                    @empty
                        <p class="text-xs text-outline italic">Tidak ada izin khusus</p>
                    @endforelse

                    @if($perms->count() > 12)
                        @foreach($perms->skip(12) as $perm)
                            <span x-show="expanded" style="display: none;" class="bg-surface border border-surface-dim text-outline px-2 py-1 rounded-lg text-[10px] font-medium">{{ $perm->name }}</span>
                        @endforeach

                        <button x-show="!expanded" @click="expanded = true" class="bg-primary/20 text-primary px-3 py-1 rounded-lg text-[10px] font-extrabold hover:bg-primary hover:text-white transition-all shadow-sm">
                            +{{ $perms->count() - 12 }} lainnya...
                        </button>
                        <button x-show="expanded" style="display: none;" @click="expanded = false" class="bg-error/10 text-error px-3 py-1.5 mt-2 rounded-lg text-[10px] font-extrabold hover:bg-error/20 transition-colors w-full text-center">
                            Sembunyikan Izin
                        </button>
                    @endif
                </div>
            </div>

            <div class="p-4 bg-surface/50 border-t border-surface-dim flex justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                @can('roles.update')
                <button wire:click="editRole({{ $roleItem->id }})" class="p-2 hover:bg-primary/10 rounded-lg text-primary transition-colors" title="Edit Role">
                    <span class="material-symbols-outlined text-lg">edit</span>
                </button>
                <button wire:click="managePermissions({{ $roleItem->id }})" class="p-2 hover:bg-primary/10 rounded-lg text-primary transition-colors" title="Manage Permissions">
                    <span class="material-symbols-outlined text-lg">settings</span>
                </button>
                @endcan
                
                @can('roles.delete')
                <button wire:click="confirmDelete({{ $roleItem->id }}, '{{ $roleItem->name }}')" class="p-2 hover:bg-error/10 rounded-lg text-error transition-colors" title="Delete Role">
                    <span class="material-symbols-outlined text-lg">delete</span>
                </button>
                @endcan
            </div>
        </div>
        @endforeach
    </div>

    <!-- Create Role Modal -->
    <div x-show="showCreate" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/40 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showCreate = false" class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden animate-slide-up">
            <div class="pearl-gradient p-8 text-white relative">
                <h3 class="text-2xl font-headline font-bold">Tambah Role</h3>
                <button @click="showCreate = false" class="absolute top-6 right-6 text-white/50 hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form wire:submit="saveRole" class="p-8 space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Nama Role</label>
                    <input wire:model="new_name" type="text" placeholder="Contoh: Supervisor" class="w-full px-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm">
                    @error('new_name') <span class="text-[10px] text-error font-bold ml-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Status Role</label>
                    <div class="flex items-center space-x-4 p-4 bg-surface border border-surface-dim rounded-2xl">
                        <span class="text-xs font-bold {{ $is_active ? 'text-primary' : 'text-outline' }}">{{ $is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        <button type="button" wire:click.prevent="$toggle('is_active')" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $is_active ? 'bg-primary' : 'bg-outline/30' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="button" @click="showCreate = false" class="flex-1 px-5 py-3 bg-surface border border-surface-dim rounded-2xl font-bold text-outline text-sm hover:bg-surface-dim transition-all">Batal</button>
                    <button type="submit" class="flex-[2] bg-primary text-white px-5 py-3 rounded-2xl font-bold text-sm hover:shadow-xl hover:shadow-primary/30 transition-all active:scale-95 flex items-center justify-center space-x-2">
                        <span class="material-symbols-outlined text-sm">save</span>
                        <span>Simpan Role</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div x-show="showEdit" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/40 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showEdit = false" class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden animate-slide-up">
            <div class="bg-surface p-8 border-b border-surface-dim relative">
                <h3 class="text-2xl font-headline font-bold text-primary">Edit Role</h3>
                <button @click="showEdit = false" class="absolute top-6 right-6 text-outline/50 hover:text-outline transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form wire:submit="updateRole" class="p-8 space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Nama Role</label>
                    <input wire:model="new_name" type="text" class="w-full px-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm">
                    @error('new_name') <span class="text-[10px] text-error font-bold ml-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Status Role</label>
                    <div class="flex items-center space-x-4 p-4 bg-surface border border-surface-dim rounded-2xl">
                        <span class="text-xs font-bold {{ $is_active ? 'text-primary' : 'text-outline' }}">{{ $is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        <button type="button" wire:click.prevent="$toggle('is_active')" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $is_active ? 'bg-primary' : 'bg-outline/30' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="button" @click="showEdit = false" class="flex-1 px-5 py-3 bg-surface border border-surface-dim rounded-2xl font-bold text-outline text-sm hover:bg-surface-dim transition-all">Batal</button>
                    <button type="submit" class="flex-[2] bg-primary text-white px-5 py-3 rounded-2xl font-bold text-sm hover:shadow-xl hover:shadow-primary/30 transition-all active:scale-95 flex items-center justify-center space-x-2">
                        <span class="material-symbols-outlined text-sm">save</span>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <div x-show="showDelete" class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-black/40 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showDelete = false" class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden animate-slide-up p-8 text-center">
            <div class="w-20 h-20 bg-error/10 text-error rounded-3xl flex items-center justify-center mx-auto mb-6">
                <span class="material-symbols-outlined text-4xl">delete_forever</span>
            </div>
            <h3 class="text-xl font-headline font-bold text-primary mb-2">Hapus Role?</h3>
            <p class="text-sm text-outline font-medium mb-8">
                Anda akan menghapus role <span class="text-error font-bold">"{{ $deletingName }}"</span>. Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="flex flex-col space-y-3">
                <button wire:click="deleteRole" class="w-full bg-error text-white py-4 rounded-2xl font-bold text-sm hover:shadow-lg hover:shadow-error/30 transition-all active:scale-95">
                    Ya, Hapus Role
                </button>
                <button @click="showDelete = false" class="w-full bg-surface text-outline py-4 rounded-2xl font-bold text-sm hover:bg-surface-dim transition-all">
                    Batalkan
                </button>
            </div>
        </div>
    </div>

    <!-- Permissions Modal -->
    <div x-show="showPermissions" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/40 backdrop-blur-sm" x-cloak x-transition>
        <div x-data="{ searchPerm: '' }" @click.away="showPermissions = false" class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden animate-slide-up flex flex-col max-h-[90vh]">
            <div class="pearl-gradient p-8 text-white relative shrink-0">
                <h3 class="text-2xl font-headline font-bold">Atur Hak Akses</h3>
                <p class="text-white/70 text-sm font-medium">Role: <span class="bg-white/20 px-2 py-0.5 rounded text-white font-bold">{{ $selectedRole->name ?? '' }}</span></p>
                <button @click="showPermissions = false" class="absolute top-6 right-6 text-white/50 hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-8 flex-grow flex flex-col overflow-hidden">
                @if (session()->has('success'))
                    <div class="bg-green-50 text-green-700 px-4 py-2 rounded-xl border border-green-100 flex items-center mb-6 shrink-0">
                        <span class="material-symbols-outlined mr-2 text-sm">check_circle</span>
                        <span class="text-xs font-bold">{{ session('success') }}</span>
                    </div>
                @endif

                @php
                    $allPermsList = [];
                    foreach($categories as $perms) {
                        $allPermsList = array_merge($allPermsList, $perms);
                    }
                    $allPermsList = array_unique($allPermsList);
                    $totalSelected = is_array($rolePermissions) ? count(array_intersect($allPermsList, $rolePermissions)) : 0;
                    $isAllGlobalSelected = count($allPermsList) > 0 && $totalSelected === count($allPermsList);
                @endphp

                <div class="mb-6 flex justify-between items-center p-4 bg-primary/5 rounded-2xl border border-primary/10 shrink-0">
                    <div>
                        <p class="text-[10px] uppercase tracking-widest font-extrabold text-primary">Aksi Global Hak Akses</p>
                        <p class="text-xs text-outline font-medium">{{ $totalSelected }} dari {{ count($allPermsList) }} izin terpilih</p>
                    </div>
                    <button wire:click="toggleAllPermissions" class="flex items-center space-x-2 px-4 py-2 rounded-xl font-bold text-xs transition-all {{ $isAllGlobalSelected ? 'bg-error/10 text-error border border-error/20 hover:bg-error/20' : 'bg-primary text-white hover:shadow-lg hover:shadow-primary/30' }}">
                        <span class="material-symbols-outlined text-sm">{{ $isAllGlobalSelected ? 'deselect' : 'select_all' }}</span>
                        <span>{{ $isAllGlobalSelected ? 'Batalkan Semua' : 'Pilih Semua Izin' }}</span>
                    </button>
                </div>

                <!-- Instant Search Module -->
                <div class="mb-4 relative shrink-0">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-sm">search</span>
                    <input x-model="searchPerm" type="text" placeholder="Ketik kategori modul... (misal: 'Marketing' atau 'Cif')" class="w-full pl-11 pr-4 py-3 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
                </div>

                <div class="space-y-4 overflow-y-auto pr-4 custom-scrollbar flex-grow">
                    @foreach($categories as $moduleName => $perms)
                    @php
                        $rolePermissionsArray = is_array($rolePermissions) ? $rolePermissions : [];
                        $intersect = array_intersect($perms, $rolePermissionsArray);
                        $allInCatSelected = count($intersect) === count($perms);
                        
                        $actionMap = [
                            // Standard CRUD
                            'view'         => 'Lihat / Inquiry',
                            'create'       => 'Tambah / Buat Baru',
                            'update'       => 'Ubah / Edit',
                            'delete'       => 'Hapus',
                            'manage'       => 'Kelola Penuh',
                            'index'        => 'Daftar Semua',

                            // CIF & Account Operations
                            'inquiry'      => 'Inquiry / Lihat Detail',
                            'inactive'     => 'Nonaktifkan',
                            'block'        => 'Blokir',
                            'unblock'      => 'Buka Blokir',
                            'reactivate'   => 'Reaktivasi',
                            'mutation'     => 'Mutasi Cabang',

                            // Savings operations
                            'deposit'      => 'Setoran Simpanan',
                            'withdrawal'   => 'Penarikan Simpanan',
                            'transfer'     => 'Transfer',
                            'reversal'     => 'Reversal Transaksi',
                            'close'        => 'Penutupan Rekening',
                            'dormant'      => 'Ubah Status Dormant',
                            'print-book'   => 'Cetak Buku Tabungan',
                            'print-slip'   => 'Cetak Slip Transaksi',

                            // Deposit operations
                            'placement'    => 'Penempatan Baru',
                            'simulation'   => 'Simulasi',
                            'modification' => 'Perubahan Data',
                            'print-bilyet' => 'Cetak Bilyet',
                            'pay'          => 'Pembayaran Bunga',

                            // Loan operations
                            'origination'  => 'Pendaftaran Kredit',
                            'disbursement' => 'Pencairan Dana',
                            'repayment'    => 'Pembayaran Angsuran',
                        ];
                    @endphp
                    <!-- Alpine block filtering logic -->
                    <div x-show="searchPerm === '' || '{{ strtolower($moduleName) }}'.includes(searchPerm.toLowerCase())" class="bg-surface/50 rounded-2xl border border-surface-dim p-4">
                        <div class="flex justify-between items-center mb-4 pb-2 border-b border-surface-dim/50">
                            <div class="flex items-center space-x-2">
                                <span class="material-symbols-outlined text-primary text-sm">folder_open</span>
                                <h4 class="text-[10px] font-black text-primary uppercase tracking-widest">
                                    {{ $moduleName }}
                                </h4>
                            </div>
                            <button wire:click="toggleCategory('{{ $moduleName }}')" class="text-[8px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg transition-all {{ $allInCatSelected ? 'bg-primary text-white shadow-sm' : 'bg-white border border-surface-dim text-outline hover:border-primary/50 text-primary' }}">
                                {{ $allInCatSelected ? 'Batal Semua' : 'Pilih Semua' }}
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($perms as $pName)
                            @php
                                $parts = explode('.', $pName);
                                $action = end($parts);
                                $humanAction = $actionMap[$action] ?? \Illuminate\Support\Str::title($action);
                                $isSelected = in_array($pName, $rolePermissions);
                            @endphp
                            <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-surface-dim/50 hover:border-primary/30 transition-colors shadow-sm">
                                <div class="flex items-center space-x-2">
                                    <span class="material-symbols-outlined text-xs {{ $isSelected ? 'text-primary' : 'text-slate-300' }}">
                                        {{ $action == 'delete' ? 'delete_forever' : 'check_circle' }}
                                    </span>
                                    <span class="text-[10px] font-bold {{ $isSelected ? 'text-primary' : 'text-outline/70' }}">{{ $humanAction }}</span>
                                </div>
                                <button wire:click="togglePermission('{{ $pName }}')" class="relative inline-flex h-4 w-8 items-center rounded-full transition-colors focus:outline-none {{ $isSelected ? 'bg-primary' : 'bg-surface-dim' }}">
                                    <span class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform {{ $isSelected ? 'translate-x-4' : 'translate-x-1' }}"></span>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-8 flex justify-end">
                    <button @click="showPermissions = false" class="bg-primary text-white px-8 py-3 rounded-2xl font-extrabold text-sm hover:shadow-xl hover:shadow-primary/30 transition-all active:scale-95">
                        Selesai
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
