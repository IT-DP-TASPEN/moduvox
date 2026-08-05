<div>
    <!-- Header -->
    <x-header title="Profil Saya" subtitle="Kelola informasi akun dan keamanan" :user="$user" :role="$role" />

        <div class="p-8 space-y-8">
            <!-- Profile Info Section -->
            <div class="bg-white rounded-3xl shadow-sm border border-surface-dim overflow-hidden transition-all hover:shadow-lg">
                <div class="p-8 border-b border-surface-dim flex items-center space-x-6">
                    <div class="w-20 h-20 rounded-3xl pearl-gradient flex items-center justify-center text-white text-3xl font-bold border-4 border-white shadow-xl">
                        {{ substr($name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-primary">{{ $name }}</h3>
                        <p class="text-sm text-outline font-medium">Username: <span class="font-mono text-primary-fixed-variant bg-surface px-2 py-0.5 rounded">{{ $username }}</span></p>
                    </div>
                </div>
                
                <form wire:submit="updateProfile" class="p-8 space-y-6">
                    @if (session()->has('message'))
                        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-xl border border-green-100 flex items-center mb-4">
                            <span class="material-symbols-outlined mr-2 text-sm">check_circle</span>
                            <span class="text-sm font-bold">{{ session('message') }}</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs uppercase tracking-widest font-extrabold text-outline ml-1">Nama Lengkap</label>
                            <input wire:model="name" type="text" class="w-full px-5 py-3.5 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-primary font-medium">
                            @error('name') <span class="text-xs text-error font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs uppercase tracking-widest font-extrabold text-outline ml-1">Email Address</label>
                            <input wire:model="email" type="email" readonly disabled class="w-full px-5 py-3.5 bg-surface/50 border border-surface-dim rounded-2xl cursor-not-allowed text-outline font-medium">
                            @error('email') <span class="text-xs text-error font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-primary text-white px-8 py-3 rounded-2xl font-extrabold text-sm hover:shadow-xl hover:shadow-primary/30 transition-all active:scale-95 flex items-center space-x-2">
                            <span class="material-symbols-outlined text-sm">save</span>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change Section -->
            <div class="bg-white rounded-3xl shadow-sm border border-surface-dim overflow-hidden transition-all hover:shadow-lg">
                <div class="p-8 border-b border-surface-dim">
                    <h3 class="text-xl font-bold text-primary">Keamanan Akun</h3>
                    <p class="text-sm text-outline font-medium">Ubah kata sandi Anda secara berkala untuk keamanan.</p>
                </div>

                <form wire:submit="updatePassword" class="p-8 space-y-6">
                    @if (session()->has('password_message'))
                        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-xl border border-green-100 flex items-center mb-4">
                            <span class="material-symbols-outlined mr-2 text-sm">check_circle</span>
                            <span class="text-sm font-bold">{{ session('password_message') }}</span>
                        </div>
                    @endif

                    <div class="space-y-6 max-w-md">
                        <div class="space-y-2">
                            <label class="text-xs uppercase tracking-widest font-extrabold text-outline ml-1">Password Saat Ini</label>
                            <input wire:model="current_password" type="password" class="w-full px-5 py-3.5 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-primary font-medium">
                            @error('current_password') <span class="text-xs text-error font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-xs uppercase tracking-widest font-extrabold text-outline ml-1">Password Baru</label>
                            <input wire:model="new_password" type="password" class="w-full px-5 py-3.5 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-primary font-medium">
                            @error('new_password') <span class="text-xs text-error font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs uppercase tracking-widest font-extrabold text-outline ml-1">Konfirmasi Password Baru</label>
                            <input wire:model="new_password_confirmation" type="password" class="w-full px-5 py-3.5 bg-surface border border-surface-dim rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-primary font-medium">
                        </div>
                    </div>

                    <div class="flex justify-start">
                        <button type="submit" class="bg-secondary-fixed text-on-secondary-fixed-variant px-8 py-3 rounded-2xl font-extrabold text-sm hover:shadow-xl hover:shadow-secondary/30 transition-all active:scale-95 flex items-center space-x-2">
                            <span class="material-symbols-outlined text-sm">lock_reset</span>
                            <span>Ganti Password</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
