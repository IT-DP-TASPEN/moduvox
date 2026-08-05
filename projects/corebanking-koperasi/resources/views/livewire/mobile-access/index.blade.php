<div class="p-0">
    <x-header title="Mobile Banking Access" subtitle="Kelola pendaftaran, blokir, dan reset PIN akun mobile nasabah" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <button wire:click="openRegisterModal"
                class="flex items-center space-x-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-slate-700 transition-all shadow-sm">
                <span class="material-symbols-outlined text-sm">person_add</span>
                <span>Daftarkan Nasabah</span>
            </button>
        </x-slot>
    </x-header>

    <div class="p-10 space-y-6">

        {{-- Flash Message --}}
        @if(session('success'))
            <div class="px-5 py-3 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-700 text-xs font-bold flex items-center space-x-2">
                <span class="material-symbols-outlined text-sm">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Search & Filter Bar --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 p-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px] space-y-1.5">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Cari Nasabah</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="CIF, username, atau nama nasabah..."
                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm text-slate-900 font-medium focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status Akses</label>
                    <select wire:model.live="filterStatus"
                        class="px-5 py-3 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Diblokir</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-outlined text-slate-900">smartphone</span>
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-900">Daftar Akun Mobile</h4>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $records->total() }} total data</p>
                    </div>
                </div>
            </div>

            @if($records->isEmpty())
                <div class="py-20 text-center">
                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-3">smartphone</span>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Belum ada data akun mobile</p>
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-slate-100">
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Nasabah</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">CIF No</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Username</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Aktivasi</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">PIN Salah</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Login Terakhir</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Device</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($records as $row)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            {{-- Nasabah --}}
                            <td class="py-4 px-6">
                                <div class="text-xs font-black text-slate-900">{{ $row->cif?->name ?? '—' }}</div>
                                <div class="text-[9px] font-bold text-slate-400 mt-0.5">{{ $row->cif?->phone ?? '' }}</div>
                            </td>
                            {{-- CIF No --}}
                            <td class="py-4 px-6">
                                <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded">
                                    {{ $row->cif_no }}
                                </span>
                            </td>
                            {{-- Username --}}
                            <td class="py-4 px-6 text-[10px] font-bold text-slate-700">{{ $row->username ?? 'Belum diatur' }}</td>
                            {{-- Aktivasi --}}
                            <td class="py-4 px-6 text-center">
                                @if($row->activated_at)
                                    <span class="inline-flex items-center space-x-1 text-[9px] font-black px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500 inline-block"></span>
                                        <span>SUDAH</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center space-x-1 text-[9px] font-black px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 inline-block"></span>
                                        <span>BELUM</span>
                                    </span>
                                @endif
                            </td>
                            {{-- Status --}}
                            <td class="py-4 px-6 text-center">
                                @if($row->is_active)
                                    <span class="inline-flex items-center space-x-1 text-[9px] font-black px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                        <span>AKTIF</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center space-x-1 text-[9px] font-black px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block"></span>
                                        <span>DIBLOKIR</span>
                                    </span>
                                @endif
                            </td>
                            {{-- PIN Salah --}}
                            <td class="py-4 px-6 text-center">
                                <span class="text-[10px] font-black {{ $row->wrong_pin_count >= 3 ? 'text-rose-600' : ($row->wrong_pin_count > 0 ? 'text-amber-600' : 'text-slate-400') }}">
                                    {{ $row->wrong_pin_count }}x
                                </span>
                            </td>
                            {{-- Login Terakhir --}}
                            <td class="py-4 px-6">
                                <div class="text-[10px] font-bold text-slate-600">
                                    {{ $row->last_login_at ? $row->last_login_at->format('d M Y, H:i') : '—' }}
                                </div>
                                @if($row->last_login_ip)
                                    <div class="text-[9px] font-bold text-slate-400 mt-0.5">{{ $row->last_login_ip }}</div>
                                @endif
                            </td>
                            {{-- Device --}}
                            <td class="py-4 px-6">
                                <div class="text-[9px] font-bold text-slate-500 max-w-[140px] truncate" title="{{ $row->device_id }}">
                                    {{ $row->device_id ? Str::limit($row->device_id, 20) : '—' }}
                                </div>
                            </td>
                            {{-- Aksi --}}
                            <td class="py-4 px-6">
                                <div class="flex items-center justify-end space-x-2">
                                    {{-- Reset Password --}}
                                    <button wire:click="openResetPassword({{ $row->id }})"
                                        title="Reset Password"
                                        class="p-2 rounded-xl text-sky-600 bg-sky-50 border border-sky-100 hover:bg-sky-100 transition-all">
                                        <span class="material-symbols-outlined text-sm">password</span>
                                    </button>
                                    {{-- Reset PIN --}}
                                    <button wire:click="openResetPin({{ $row->id }})"
                                        title="Reset PIN"
                                        class="p-2 rounded-xl text-amber-600 bg-amber-50 border border-amber-100 hover:bg-amber-100 transition-all">
                                        <span class="material-symbols-outlined text-sm">pin</span>
                                    </button>
                                    {{-- Blokir / Aktifkan --}}
                                    <button wire:click="openToggleStatus({{ $row->id }})"
                                        title="{{ $row->is_active ? 'Blokir Akses' : 'Aktifkan Kembali' }}"
                                        class="p-2 rounded-xl {{ $row->is_active ? 'text-rose-600 bg-rose-50 border border-rose-100 hover:bg-rose-100' : 'text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100' }} transition-all">
                                        <span class="material-symbols-outlined text-sm">{{ $row->is_active ? 'block' : 'lock_open' }}</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $records->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         MODAL: Registrasi Akun Mobile Baru
    ═══════════════════════════════════════════════════════ --}}
    @if($showRegisterModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            {{-- Header --}}
            <div class="p-7 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-slate-900 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-sm">person_add</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Daftarkan Akun Mobile</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Admin hanya daftarkan CIF, user aktivasi mandiri</p>
                    </div>
                </div>
                <button wire:click="$set('showRegisterModal', false)" class="p-2 rounded-xl hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined text-slate-500 text-sm">close</span>
                </button>
            </div>
            {{-- Body --}}
            <div class="p-7 space-y-5">
                {{-- CIF No --}}
                <div class="space-y-1.5">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">CIF No Nasabah <span class="text-rose-500">*</span></label>
                    <input wire:model="reg_cif_no" type="text" placeholder="Contoh: CIF00001"
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all uppercase">
                    @error('reg_cif_no') <span class="text-[9px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                </div>
                <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl text-[9px] font-bold text-amber-700 flex items-start space-x-2">
                    <span class="material-symbols-outlined text-xs mt-0.5">info</span>
                    <span>Username, password, dan PIN akan diisi langsung oleh user saat proses aktivasi mobile.</span>
                </div>
            </div>
            {{-- Footer --}}
            <div class="p-7 pt-0 flex items-center justify-end space-x-3">
                <button wire:click="$set('showRegisterModal', false)"
                    class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold text-xs rounded-2xl hover:bg-slate-50 transition-all">
                    Batal
                </button>
                <button wire:click="register"
                    class="px-6 py-3 bg-slate-900 text-white font-bold text-xs rounded-2xl hover:bg-slate-700 transition-all flex items-center space-x-2">
                    <div wire:loading wire:target="register" class="w-3.5 h-3.5 border-2 border-slate-500 border-t-white rounded-full animate-spin"></div>
                    <span class="material-symbols-outlined text-sm" wire:loading.remove wire:target="register">person_add</span>
                    <span>Daftarkan</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════
         MODAL: Reset PIN
    ═══════════════════════════════════════════════════════ --}}
    @if($showResetPinModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="p-7 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-sm">pin</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Reset PIN</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $resetTargetName }}</p>
                    </div>
                </div>
                <button wire:click="$set('showResetPinModal', false)" class="p-2 rounded-xl hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined text-slate-500 text-sm">close</span>
                </button>
            </div>
            <div class="p-7 space-y-5">
                <div class="space-y-1.5">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">PIN Baru (6 Digit) <span class="text-rose-500">*</span></label>
                    <input wire:model="resetNewPin" type="password" placeholder="6 digit angka" maxlength="6" inputmode="numeric"
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all tracking-[0.5em]">
                    @error('resetNewPin') <span class="text-[9px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                </div>
                <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl text-[9px] font-bold text-emerald-700 flex items-start space-x-2">
                    <span class="material-symbols-outlined text-xs mt-0.5">lock_open</span>
                    <span>Reset PIN akan membuka blokir akun secara otomatis dan mereset hitungan percobaan PIN.</span>
                </div>
            </div>
            <div class="p-7 pt-0 flex items-center justify-end space-x-3">
                <button wire:click="$set('showResetPinModal', false)"
                    class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold text-xs rounded-2xl hover:bg-slate-50 transition-all">
                    Batal
                </button>
                <button wire:click="resetPin"
                    class="px-6 py-3 bg-amber-500 text-white font-bold text-xs rounded-2xl hover:bg-amber-600 transition-all flex items-center space-x-2">
                    <div wire:loading wire:target="resetPin" class="w-3.5 h-3.5 border-2 border-amber-300 border-t-white rounded-full animate-spin"></div>
                    <span class="material-symbols-outlined text-sm" wire:loading.remove wire:target="resetPin">pin</span>
                    <span>Reset PIN</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════
         MODAL: Reset Password
    ═══════════════════════════════════════════════════════ --}}
    @if($showResetPasswordModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="p-7 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-sky-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-sm">password</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Reset Password</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $resetPassTargetName }}</p>
                    </div>
                </div>
                <button wire:click="$set('showResetPasswordModal', false)" class="p-2 rounded-xl hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined text-slate-500 text-sm">close</span>
                </button>
            </div>
            <div class="p-7 space-y-5">
                <div class="space-y-1.5">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Password Baru <span class="text-rose-500">*</span></label>
                    <input wire:model="resetNewPassword" type="password" placeholder="Minimal 8 karakter"
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                    @error('resetNewPassword') <span class="text-[9px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="p-7 pt-0 flex items-center justify-end space-x-3">
                <button wire:click="$set('showResetPasswordModal', false)"
                    class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold text-xs rounded-2xl hover:bg-slate-50 transition-all">
                    Batal
                </button>
                <button wire:click="resetPassword"
                    class="px-6 py-3 bg-sky-500 text-white font-bold text-xs rounded-2xl hover:bg-sky-600 transition-all flex items-center space-x-2">
                    <div wire:loading wire:target="resetPassword" class="w-3.5 h-3.5 border-2 border-sky-300 border-t-white rounded-full animate-spin"></div>
                    <span class="material-symbols-outlined text-sm" wire:loading.remove wire:target="resetPassword">password</span>
                    <span>Reset Password</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════
         MODAL: Konfirmasi Blokir / Aktifkan
    ═══════════════════════════════════════════════════════ --}}
    @if($showStatusModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="p-7 border-b border-slate-100">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center {{ $newStatus ? 'bg-emerald-500' : 'bg-rose-500' }}">
                        <span class="material-symbols-outlined text-white text-sm">{{ $newStatus ? 'lock_open' : 'block' }}</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">{{ $newStatus ? 'Aktifkan Kembali' : 'Blokir Akses' }}</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Konfirmasi tindakan</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    @if($newStatus)
                        Anda akan <span class="font-black text-emerald-600">mengaktifkan kembali</span> akses mobile banking untuk nasabah <span class="font-black text-slate-900">{{ $statusTargetName }}</span>.
                        Hitungan PIN salah akan direset.
                    @else
                        Anda akan <span class="font-black text-rose-600">memblokir</span> akses mobile banking untuk nasabah <span class="font-black text-slate-900">{{ $statusTargetName }}</span>.
                        Semua sesi login aktif akan dihapus.
                    @endif
                </p>
            </div>
            <div class="p-7 flex items-center justify-end space-x-3">
                <button wire:click="$set('showStatusModal', false)"
                    class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold text-xs rounded-2xl hover:bg-slate-50 transition-all">
                    Batal
                </button>
                <button wire:click="toggleStatus"
                    class="px-6 py-3 font-bold text-xs rounded-2xl transition-all flex items-center space-x-2 {{ $newStatus ? 'bg-emerald-500 text-white hover:bg-emerald-600' : 'bg-rose-500 text-white hover:bg-rose-600' }}">
                    <div wire:loading wire:target="toggleStatus" class="w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></div>
                    <span class="material-symbols-outlined text-sm" wire:loading.remove wire:target="toggleStatus">{{ $newStatus ? 'lock_open' : 'block' }}</span>
                    <span>{{ $newStatus ? 'Ya, Aktifkan' : 'Ya, Blokir' }}</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
