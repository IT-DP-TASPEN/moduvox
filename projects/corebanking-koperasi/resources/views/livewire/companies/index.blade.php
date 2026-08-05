<div x-data="{ showCreate: @entangle('showCreateModal'), showEdit: @entangle('showEditModal'), showDelete: @entangle('confirmingDeletion') }">
    <!-- Header -->
    <x-header title="Master Perusahaan" subtitle="Kelola entitas perusahaan pusat dan cabang" :user="$user"
        :role="$role">
            <x-slot:actions>
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm">search</span>
                    <input wire:model.live="search" type="text" placeholder="Cari perusahaan..."
                        class="pl-10 pr-4 py-2 bg-surface border border-surface-dim rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all w-48 font-medium">
                </div>
                @can('companies.create')
                <button @click="showCreate = true"
                    class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-primary/30 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-sm">domain</span>
                    <span>Tambah Perusahaan</span>
                </button>
                @endcan
            </x-slot:actions>
        </x-header>

        <!-- Content -->
        <div class="p-8">
            <!-- Stats Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-surface-dim flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-secondary/10 flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined">corporate_fare</span>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-widest font-extrabold text-outline">Total Perusahaan
                        </p>
                        <p class="text-2xl font-headline font-bold text-secondary">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>

            @if (session()->has('success'))
            <div
                class="bg-green-50 text-green-700 px-6 py-4 rounded-2xl border border-green-100 flex items-center mb-6 animate-fade-in shadow-sm">
                <span class="material-symbols-outlined mr-3 text-lg">check_circle</span>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
            @endif

            @if (session()->has('error'))
            <div
                class="bg-red-50 text-red-700 px-6 py-4 rounded-2xl border border-red-100 flex items-center mb-6 animate-fade-in shadow-sm">
                <span class="material-symbols-outlined mr-3 text-lg">error</span>
                <span class="font-bold">{{ session('error') }}</span>
            </div>
            @endif

            <div class="bg-white rounded-3xl shadow-sm border border-surface-dim overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <thead>
                            <tr
                                class="bg-surface border-b border-surface-dim uppercase text-[10px] tracking-widest font-bold text-outline">
                                <th class="px-6 py-4">Nama Perusahaan</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Kontak & Alamat</th>
                                <th class="px-6 py-4">Media Sosial</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                    <tbody class="divide-y divide-surface-dim">
                        @foreach($companiesList as $c)
                        <tr class="hover:bg-surface/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-primary">{{ $c->company_name }}</span>
                                    <div class="flex items-center space-x-2">
                                        @if($c->company_code)
                                        <span
                                            class="text-[9px] bg-primary/10 text-primary px-1.5 py-0.5 rounded font-bold uppercase">{{
                                            $c->company_code }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    @if($c->is_active)
                                    <span
                                        class="flex items-center bg-green-50 text-green-600 px-3 py-1 rounded-full text-[10px] font-bold border border-green-100 transition-all">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                        AKTIF
                                    </span>
                                    @else
                                    <span
                                        class="flex items-center bg-gray-50 text-gray-500 px-3 py-1 rounded-full text-[10px] font-bold border border-gray-100 transition-all">
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-2"></span>
                                        NONAKTIF
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col space-y-1">
                                    <div class="flex items-center space-x-2 text-primary">
                                        <span class="material-symbols-outlined text-sm">phone</span>
                                        <span class="text-xs font-bold">{{ $c->phones['telepon'] ?? '-' }}</span>
                                        @if(isset($c->phones['whatsapp']) && $c->phones['whatsapp'])
                                        <span
                                            class="text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded font-extrabold uppercase">WA</span>
                                        @endif
                                    </div>
                                    <div class="flex items-start space-x-2 text-outline">
                                        <span class="material-symbols-outlined text-sm mt-0.5">location_on</span>
                                        <span
                                            class="text-[10px] font-medium leading-relaxed max-w-[200px] truncate-3-lines">{{
                                            $c->address ?? 'Alamat belum diset' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    @if(isset($c->social_media['twitter']) && $c->social_media['twitter'])
                                    <div class="flex items-center space-x-1 text-primary hover:text-secondary opacity-70 hover:opacity-100 transition-all cursor-default"
                                        title="Twitter">
                                        <span class="material-symbols-outlined text-base font-icon">flutter_dash</span>
                                        <span class="text-[10px] font-bold">{{ $c->social_media['twitter'] }}</span>
                                    </div>
                                    @endif
                                    @if(isset($c->social_media['instagram']) && $c->social_media['instagram'])
                                    <div class="flex items-center space-x-1 text-primary hover:text-secondary opacity-70 hover:opacity-100 transition-all cursor-default"
                                        title="Instagram">
                                        <span class="material-symbols-outlined text-base font-icon">camera</span>
                                        <span class="text-[10px] font-bold">{{ $c->social_media['instagram'] }}</span>
                                    </div>
                                    @endif
                                    @if(!isset($c->social_media['twitter']) && !isset($c->social_media['instagram']))
                                    <span class="text-[10px] text-outline italic">No Social Media</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @can('companies.update')
                                    <button wire:click="editCompany({{ $c->id }})"
                                        class="p-2 hover:bg-primary/10 rounded-lg text-primary transition-colors"
                                        title="Edit">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    @endcan
                                    
                                    @can('companies.delete')
                                    <button wire:click="confirmDelete({{ $c->id }}, '{{ $c->company_name }}')"
                                        class="p-2 hover:bg-error/10 rounded-lg text-error transition-colors"
                                        title="Delete">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-6 py-4 bg-surface border-t border-surface-dim">
                    {{ $companiesList->links() }}
                </div>
            </div>
        </div>

        <!-- Custom Delete Confirmation Modal -->
        <div x-show="showDelete"
            class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-black/40 backdrop-blur-sm" x-cloak
            x-transition>
            <div @click.away="showDelete = false"
                class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden animate-slide-up p-8 text-center">
                <div class="w-20 h-20 bg-error/10 text-error rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <span class="material-symbols-outlined text-4xl">location_off</span>
                </div>
                <h3 class="text-xl font-headline font-bold text-primary mb-2">Hapus Perusahaan?</h3>
                <p class="text-sm text-outline font-medium mb-8">
                    Anda akan menghapus <span class="text-error font-bold">"{{ $deletingName }}"</span>. Seluruh data
                    cabang dan user di bawah perusahaan ini harus sudah kosong terlebih dahulu.
                </p>
                <div class="flex flex-col space-y-3">
                    <button wire:click="deleteCompany"
                        class="w-full bg-error text-white py-4 rounded-2xl font-bold text-sm hover:shadow-lg hover:shadow-error/30 transition-all active:scale-95">
                        Ya, Hapus Perusahaan
                    </button>
                    <button @click="showDelete = false"
                        class="w-full bg-surface text-outline py-4 rounded-2xl font-bold text-sm hover:bg-surface-dim transition-all">
                        Batalkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div x-show="showCreate"
            class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/40 backdrop-blur-sm" x-cloak
            x-transition>
            <div @click.away="showCreate = false"
                class="bg-white w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden animate-slide-up">
                <div class="pearl-gradient p-8 text-white relative">
                    <h3 class="text-2xl font-headline font-bold">Tambah Perusahaan</h3>
                    <p class="text-white/70 text-sm font-medium">Lengkapi detail profil entitas bisnis</p>
                    <button @click="showCreate = false"
                        class="absolute top-6 right-6 text-white/50 hover:text-white transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form wire:submit="saveCompany"
                    class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Nama
                            Perusahaan</label>
                        <input wire:model="new_name" type="text"
                            class="w-full px-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm">
                        @error('new_name') <span class="text-[10px] text-error font-bold ml-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Kode
                            Perusahaan</label>
                        <input wire:model="company_code" type="text" placeholder="MISAL: CP001"
                            class="w-full px-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm uppercase">
                        @error('company_code') <span class="text-[10px] text-error font-bold ml-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Status
                            Perusahaan</label>
                        <div class="flex items-center space-x-4 p-4 bg-surface border border-surface-dim rounded-2xl">
                            <span class="text-xs font-bold {{ $is_active ? 'text-primary' : 'text-outline' }}">{{
                                $is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            <button type="button" wire:click.prevent="$toggle('is_active')"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $is_active ? 'bg-primary' : 'bg-outline/30' }}">
                                <span
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Alamat
                            Lengkap</label>
                        <textarea wire:model="address" rows="2"
                            class="w-full px-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm"></textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Telepon
                            Kantor</label>
                        <input wire:model="phone_telp" type="text"
                            class="w-full px-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">WhatsApp
                            Business</label>
                        <input wire:model="phone_wa" type="text"
                            class="w-full px-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Twitter
                            Account</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-outline text-sm">@</span>
                            <input wire:model="social_twitter" type="text"
                                class="w-full pl-9 pr-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Instagram
                            Account</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-outline text-sm">@</span>
                            <input wire:model="social_ig" type="text"
                                class="w-full pl-9 pr-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm">
                        </div>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Deskripsi
                            Perusahaan</label>
                        <textarea wire:model="description" rows="3"
                            placeholder="Tuliskan deskripsi singkat mengenai perusahaan..."
                            class="w-full px-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm"></textarea>
                    </div>

                    <div class="md:col-span-2 flex space-x-3 pt-4">
                        <button type="button" @click="showCreate = false"
                            class="flex-1 px-5 py-3 bg-surface border border-surface-dim rounded-2xl font-bold text-outline text-sm hover:bg-surface-dim transition-all">Batal</button>
                        <button type="submit"
                            class="flex-[2] bg-primary text-white px-5 py-3 rounded-2xl font-bold text-sm hover:shadow-xl hover:shadow-primary/30 transition-all active:scale-95 flex items-center justify-center space-x-2">
                            <span class="material-symbols-outlined text-sm">save</span>
                            <span>Simpan Perusahaan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div x-show="showEdit"
            class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/40 backdrop-blur-sm" x-cloak
            x-transition>
            <div @click.away="showEdit = false"
                class="bg-white w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden animate-slide-up">
                <div class="bg-surface p-8 border-b border-surface-dim relative">
                    <h3 class="text-2xl font-headline font-bold text-primary">Edit Profil Perusahaan</h3>
                    <p class="text-outline text-sm font-medium">Perbarui informasi entitas bisnis</p>
                    <button @click="showEdit = false"
                        class="absolute top-6 right-6 text-outline/50 hover:text-outline transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form wire:submit="updateCompany"
                    class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Nama
                            Perusahaan</label>
                        <input wire:model="new_name" type="text"
                            class="w-full px-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm">
                        @error('new_name') <span class="text-[10px] text-error font-bold ml-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Kode
                            Perusahaan</label>
                        <input wire:model="company_code" type="text"
                            class="w-full px-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm uppercase">
                        @error('company_code') <span class="text-[10px] text-error font-bold ml-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Status
                            Perusahaan</label>
                        <div class="flex items-center space-x-4 p-4 bg-surface border border-surface-dim rounded-2xl">
                            <span class="text-xs font-bold {{ $is_active ? 'text-primary' : 'text-outline' }}">{{
                                $is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            <button type="button" wire:click.prevent="$toggle('is_active')"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $is_active ? 'bg-primary' : 'bg-outline/30' }}">
                                <span
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Alamat
                            Lengkap</label>
                        <textarea wire:model="address" rows="2"
                            class="w-full px-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm"></textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Telepon
                            Kantor</label>
                        <input wire:model="phone_telp" type="text"
                            class="w-full px-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">WhatsApp
                            Business</label>
                        <input wire:model="phone_wa" type="text"
                            class="w-full px-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Twitter
                            Account</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-outline text-sm">@</span>
                            <input wire:model="social_twitter" type="text"
                                class="w-full pl-9 pr-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Instagram
                            Account</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-outline text-sm">@</span>
                            <input wire:model="social_ig" type="text"
                                class="w-full pl-9 pr-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm">
                        </div>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-outline ml-1">Deskripsi
                            Perusahaan</label>
                        <textarea wire:model="description" rows="3"
                            class="w-full px-5 py-3 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-medium text-sm"></textarea>
                    </div>

                    <div class="md:col-span-2 flex space-x-3 pt-4">
                        <button type="button" @click="showEdit = false"
                            class="flex-1 px-5 py-3 bg-surface border border-surface-dim rounded-2xl font-bold text-outline text-sm hover:bg-surface-dim transition-all">Batal</button>
                        <button type="submit"
                            class="flex-[2] bg-primary text-white px-5 py-3 rounded-2xl font-bold text-sm hover:shadow-xl hover:shadow-primary/30 transition-all active:scale-95 flex items-center justify-center space-x-2">
                            <span class="material-symbols-outlined text-sm">save</span>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>