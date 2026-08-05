<div class="p-0">
    <x-header title="Nonaktifkan CIF" subtitle="Tutup rekening profil nasabah secara permanen." :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot:actions>
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live="search" type="text" placeholder="Cari CIF Aktif..." class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium w-64">
                </div>
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-10">
        @if (session()->has('success'))
            <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-[2rem] border border-emerald-100 flex items-center mb-10 shadow-sm"><p class="font-bold text-sm">{{ session('success') }}</p></div>
        @endif

        @if($viewMode === 'grid')
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/50">
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Aksi</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">No. CIF</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Nama Peserta</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">NIK / Kontak</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-6 relative w-28">
                                <button wire:click="viewCif({{ $item->id }})" class="flex items-center text-[10px] uppercase tracking-wider font-bold px-3 py-1.5 bg-slate-900 text-white hover:bg-slate-800 rounded-lg transition-all"><span class="material-symbols-outlined text-[14px] mr-1">power_settings_new</span> Nonaktifkan</button>
                            </td>
                            <td class="py-4 px-6"><span class="text-sm font-extrabold text-slate-800 tracking-wider">{{ $item->cif_no }}</span></td>
                            <td class="py-4 px-6"><p class="font-bold text-sm text-slate-900">{{ $item->name }}</p></td>
                            <td class="py-4 px-6"><p class="font-bold text-xs text-slate-900">{{ $item->nik }}</p></td>
                            <td class="py-4 px-6">
                                <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-emerald-100 text-emerald-700">{{ $item->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-24 text-center text-slate-400">
                                <span class="material-symbols-outlined text-5xl mb-4 opacity-20">youtube_searched_for</span>
                                <p class="text-sm font-bold">Lakukan pencarian untuk memunculkan target Penutupan Akun CIF.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages()) <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">{{ $items->links() }}</div> @endif
        </div>
        
        @else
        <!-- INACTIVE FORM -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-300 overflow-hidden flex flex-col mb-10 ring-4 ring-slate-100">
            <div class="px-8 py-6 bg-slate-100 border-b border-slate-200 flex items-center space-x-4">
                <button wire:click="closeView" class="p-2 bg-white text-slate-600 hover:bg-slate-200 rounded-xl transition-all shadow-sm border border-slate-200"><span class="material-symbols-outlined text-sm">arrow_back</span></button>
                <div>
                    <h2 class="font-extrabold text-sm text-slate-900 tracking-wider flex items-center"><span class="material-symbols-outlined mr-2">power_settings_new</span> Eksekusi Penutupan CIF: {{ $selectedCif->cif_no ?? '' }}</h2>
                </div>
            </div>

            <!-- Mini Profile Card (Read Only) -->
            <div class="p-8 bg-slate-50 border-b border-slate-200 relative overflow-hidden">
                <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-[120px] text-slate-900/5 select-none pointer-events-none">account_circle</span>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 relative z-10">
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-slate-400 font-bold mb-1">Nama Nasabah</p>
                        <p class="font-extrabold text-sm text-slate-800">{{ $selectedCif->name ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-slate-400 font-bold mb-1">NIK</p>
                        <p class="font-extrabold text-sm text-slate-800">{{ $selectedCif->nik ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-slate-400 font-bold mb-1">Status Saat Ini</p>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">{{ $selectedCif->status ?? '' }}</span>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-slate-400 font-bold mb-1">Penempatan</p>
                        <p class="font-extrabold text-sm text-slate-600 mb-1">{{ $selectedCif->branch->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <div class="p-8 pb-12 space-y-8">
                <div>
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Keterangan / Alasan Penutupan <span class="text-rose-500">*</span></label>
                        <textarea wire:model="reason" rows="3" placeholder="Masukkan alasan mengapa pengguna ini ditutup/dinonaktifkan permanen..." class="w-full px-5 py-3.5 bg-white border border-slate-300 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-500 transition-all font-bold text-sm text-slate-700 resize-none"></textarea>
                        @error('reason') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            
            <div class="px-8 py-5 border-t border-slate-200 bg-slate-100 flex items-center justify-between">
                <p class="text-xs font-bold text-slate-600 uppercase tracking-widest flex items-center"><span class="material-symbols-outlined text-sm mr-2">info</span> Aksi ini menandakan akun telah mati dan tidak diakui lagi aktif dalam buku.</p>
                <button wire:click="submitInactive" class="px-8 py-3 bg-slate-900 text-white font-bold text-xs rounded-xl hover:bg-slate-800 hover:shadow-lg transition-all flex items-center">
                    <span class="material-symbols-outlined text-sm mr-2">check_circle</span> Konfirmasi Penutupan Status
                </button>
            </div>
        </div>
        @endif
    </div>
</div>
