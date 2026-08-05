<div class="p-0">
    <x-header title="Pengaturan Persetujuan" subtitle="Konfigurasi tata kelola aksi yang memerlukan persetujuan (Maker-Checker)" :user="$user" :role="$role">
        <x-slot:actions>
            <div class="flex items-center space-x-2 bg-emerald-50 text-emerald-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-emerald-100 animate-pulse">
                <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                <span>Sistem Aktif</span>
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-10 space-y-10">
        @if (session()->has('success'))
        <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-[2rem] border border-emerald-100 flex items-center animate-fade-in shadow-sm">
            <span class="material-symbols-outlined mr-3 text-lg">check_circle</span>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
        @endif

        @php
            $groupedModules = collect($modules)->groupBy('group');
            $groupIcons = [
                'Manajemen Sistem'  => 'admin_panel_settings',
                'Master Wilayah'    => 'map',
                'Master Produk'     => 'inventory_2',
                'Manajemen CIF'     => 'badge',
                'Layanan Simpanan'  => 'savings',
                'Layanan Simpanan Berjangka'  => 'account_balance_wallet',
                'Layanan Kredit'    => 'credit_score',
                'Akuntansi'         => 'menu_book',
                'Aset & Sewa'       => 'domain',
            ];
        @endphp

        @foreach($groupedModules as $groupName => $groupModules)
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            {{-- Group Header --}}
            <div class="px-10 py-6 bg-slate-50 border-b border-slate-200 flex items-center space-x-4">
                <div class="w-10 h-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-lg shadow-slate-900/10">
                    <span class="material-symbols-outlined text-lg">{{ $groupIcons[$groupName] ?? 'settings' }}</span>
                </div>
                <div>
                    <h3 class="font-headline font-bold text-base text-slate-900 leading-tight">{{ $groupName }}</h3>
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $groupModules->count() }} modul dikonfigurasi</p>
                </div>
            </div>

            {{-- Module Rows --}}
            <div class="divide-y divide-slate-100">
                @foreach($groupModules as $mod)
                @php
                    $moduleActions = $configs[$mod['key']] ?? [];
                    $activeActionsCount = collect($moduleActions)->where('is_active', true)->count();
                    $totalActions = count($moduleActions);
                @endphp
                <div class="px-10 py-5 flex items-center justify-between hover:bg-slate-50/50 transition-colors group">
                    {{-- Module Info --}}
                    <div class="flex items-center space-x-4 min-w-0 flex-1">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-900 text-sm leading-tight mb-0.5">{{ $mod['name'] }}</p>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest font-mono">{{ $mod['key'] }}</p>
                        </div>
                    </div>

                    {{-- Actions Status Pills --}}
                    <div class="flex items-center gap-2 mx-6">
                        @foreach($moduleActions as $action => $config)
                        <button wire:click="toggle('{{ $mod['key'] }}', '{{ $action }}')"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest border transition-all cursor-pointer
                                {{ $config['is_active']
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100 shadow-sm'
                                    : 'bg-slate-50 text-slate-400 border-slate-200 hover:bg-slate-100' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $config['is_active'] ? 'bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.6)]' : 'bg-slate-300' }}"></span>
                            {{ $action }}
                        </button>
                        @endforeach
                        @if(empty($moduleActions))
                        <span class="text-[9px] text-slate-300 font-bold italic uppercase tracking-widest">Tidak ada aksi</span>
                        @endif
                    </div>

                    {{-- Summary & Settings Button --}}
                    <div class="flex items-center gap-4 shrink-0">
                        <div class="text-right">
                            <p class="text-xs font-black text-slate-900">{{ $activeActionsCount }} / {{ $totalActions }}</p>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">aktif</p>
                        </div>
                        @can('manage.approvals')
                        <button wire:click="editModule('{{ $mod['key'] }}', '{{ $mod['name'] }}')"
                            class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-400 text-[9px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all shadow-sm group/btn whitespace-nowrap">
                            <span class="material-symbols-outlined text-sm group-hover/btn:rotate-90 transition-transform">tune</span>
                            <span>Kelola Role</span>
                        </button>
                        @endcan
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <!-- Edit Governance Modal -->
    <div x-data="{ open: @entangle('showEditModal') }" x-show="open"
        class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm"
        x-transition x-cloak>
        <div @click.away="open = false" class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-slide-up flex flex-col max-h-[90vh]">
            <div class="pearl-gradient p-8 text-white flex justify-between items-center relative shrink-0">
                <div class="relative z-10">
                    <h3 class="text-2xl font-headline font-bold tracking-tight text-white m-0">Tata Kelola Modul</h3>
                    <p class="text-white/60 text-[10px] font-extrabold uppercase tracking-[0.2em] mt-1">{{ $selectedModuleName }}</p>
                </div>
                <button @click="open = false" class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors relative z-10 border-none outline-none cursor-pointer">
                    <span class="material-symbols-outlined text-white">close</span>
                </button>
                <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            </div>

            <div class="p-8 space-y-6 overflow-y-auto custom-scrollbar">
                @php
                    $actionLabels = [
                        'CREATE'       => ['label' => 'Penambahan Data Baru', 'icon' => 'add_circle'],
                        'UPDATE'       => ['label' => 'Perubahan Data', 'icon' => 'edit_square'],
                        'DELETE'       => ['label' => 'Penghapusan Data', 'icon' => 'delete_forever'],
                        'DEPOSIT'      => ['label' => 'Setoran Simpanan', 'icon' => 'savings'],
                        'WITHDRAWAL'   => ['label' => 'Penarikan Simpanan', 'icon' => 'money_off'],
                        'TRANSFER'     => ['label' => 'Transfer Antar Rekening', 'icon' => 'swap_horiz'],
                        'BLOCK'        => ['label' => 'Pemblokiran', 'icon' => 'lock'],
                        'UNBLOCK'      => ['label' => 'Buka Pemblokiran', 'icon' => 'lock_open'],
                        'REVERSAL'     => ['label' => 'Reversal / Pembalikan Transaksi', 'icon' => 'undo'],
                        'CLOSE'        => ['label' => 'Penutupan / Pencairan', 'icon' => 'close'],
                        'DORMANT'      => ['label' => 'Ubah Status Dormant', 'icon' => 'bedtime'],
                        'REACTIVATE'   => ['label' => 'Reaktivasi Rekening', 'icon' => 'restart_alt'],
                        'INACTIVE'     => ['label' => 'Penonaktifan', 'icon' => 'power_settings_new'],
                        'MUTATION'     => ['label' => 'Mutasi Cabang', 'icon' => 'transfer_within_a_station'],
                        'Originate'    => ['label' => 'Pendaftaran Fasilitas Kredit', 'icon' => 'post_add'],
                        'Disbursement' => ['label' => 'Pencairan Dana Kredit', 'icon' => 'request_quote'],
                        'Repayment'    => ['label' => 'Pembayaran Angsuran', 'icon' => 'payments'],
                        'Reversal'     => ['label' => 'Batal Cair (Reversal)', 'icon' => 'undo'],
                        'EXPORT'       => ['label' => 'Ekspor / Unduh Laporan', 'icon' => 'download'],
                    ];
                @endphp

                @foreach($editingConfigs as $action => $data)
                <div class="bg-slate-50 rounded-3xl p-6 border border-slate-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-900 shadow-sm">
                                <span class="material-symbols-outlined text-lg">{{ $actionLabels[$action]['icon'] ?? 'star' }}</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 mb-0.5">{{ $actionLabels[$action]['label'] ?? $action }}</h4>
                                <p class="text-[9px] font-extrabold uppercase tracking-widest text-slate-400">Kode: {{ $action }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('editingConfigs.{{ $action }}.is_active', {{ !$editingConfigs[$action]['is_active'] ? 'true' : 'false' }})"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $editingConfigs[$action]['is_active'] ? 'bg-slate-900' : 'bg-slate-200' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $editingConfigs[$action]['is_active'] ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>

                    @if($editingConfigs[$action]['is_active'])
                    <div class="grid grid-cols-2 gap-2 mt-4">
                        @foreach($roles as $r)
                        <label class="flex items-center p-3 bg-white hover:bg-slate-50 rounded-xl border border-slate-100 transition-all cursor-pointer">
                            <input type="checkbox" wire:model="editingConfigs.{{ $action }}.roles" value="{{ $r->name }}" class="w-4 h-4 rounded border-slate-200 text-slate-900 mr-3">
                            <span class="text-[11px] font-bold text-slate-600">{{ $r->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    @else
                    <div class="py-3 text-center">
                        <p class="text-[10px] uppercase tracking-widest font-black text-slate-300 italic">Persetujuan Tidak Diperlukan</p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <div class="p-8 pt-4 border-t border-slate-100 shrink-0 bg-white">
                <div class="flex items-center space-x-3">
                    <button type="button" @click="open = false" class="flex-1 py-4 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-2xl font-bold transition-all text-xs uppercase tracking-widest border-none outline-none cursor-pointer">
                        Batal
                    </button>
                    <button wire:click="saveModuleConfigs" class="flex-[2] py-4 bg-slate-900 hover:shadow-lg hover:shadow-slate-900/20 text-white rounded-2xl font-bold transition-all text-xs uppercase tracking-widest border-none outline-none cursor-pointer">
                        Simpan Tata Kelola
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
