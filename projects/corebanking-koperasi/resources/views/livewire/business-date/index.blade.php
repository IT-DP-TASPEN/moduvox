<div class="p-0">
    <x-header title="Tanggal Operasional" subtitle="Pengaturan tanggal aplikasi untuk input transaksi backdate" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot:actions>
            <div class="flex items-center space-x-2 bg-slate-50 text-slate-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-slate-100">
                <span class="material-symbols-outlined text-sm">schedule</span>
                <span>{{ $application_date_display }}</span>
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-10 space-y-8">
        @if (session()->has('success'))
            <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-[2rem] border border-emerald-100 flex items-center shadow-sm">
                <span class="material-symbols-outlined mr-3 text-lg">check_circle</span>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-10 py-6 bg-slate-50 border-b border-slate-200 flex items-center justify-between gap-6">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-lg shadow-slate-900/10">
                        <span class="material-symbols-outlined text-lg">event_repeat</span>
                    </div>
                    <div>
                        <h3 class="font-headline font-bold text-base text-slate-900 leading-tight">Tanggal Operasional Aplikasi</h3>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Kosongkan untuk mengikuti tanggal sistem server</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-xl border text-[9px] font-black uppercase tracking-widest {{ $business_date ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100' }}">
                    {{ $business_date ? 'Backdate Aktif' : 'Tanggal Real' }}
                </span>
            </div>

            <div class="p-10 grid grid-cols-1 lg:grid-cols-3 gap-6 items-end">
                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Sistem Server</label>
                    <input type="text" readonly value="{{ $system_date_display }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Aplikasi</label>
                    <input type="date" wire:model="business_date" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-black text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900">
                    @error('business_date') <span class="text-[10px] text-rose-500 font-bold uppercase">{{ $message }}</span> @enderror
                </div>
                <div class="flex items-center gap-3">
                    <button wire:click="saveBusinessDate" class="flex-1 py-3.5 bg-slate-900 hover:shadow-lg hover:shadow-slate-900/20 text-white rounded-2xl font-black transition-all text-xs uppercase tracking-widest flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">save</span>
                        Simpan
                    </button>
                    <button wire:click="resetBusinessDate" class="px-5 py-3.5 bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 rounded-2xl font-black transition-all text-xs uppercase tracking-widest flex items-center justify-center">
                        <span class="material-symbols-outlined text-sm">restart_alt</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
