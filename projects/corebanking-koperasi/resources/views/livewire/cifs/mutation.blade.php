<div class="p-0">
    <x-header title="Mutasi Cabang CIF" subtitle="Pemindahan penempatan unit basis data terpusat nasabah." :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot:actions>
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <select wire:model.live="filter_branch" class="pl-3 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold w-48 text-slate-600 appearance-none">
                        <option value="">Filter Cabang</option>
                        @foreach($branches as $b) <option value="{{ $b->id }}">{{ $b->name }}</option> @endforeach
                    </select>
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live="search" type="text" placeholder="Cari CIF, NIK, Nama..." class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium w-64">
                </div>
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-10">
        @if (session()->has('success'))
            <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-[2rem] border border-emerald-100 flex items-center mb-10 shadow-sm"><p class="font-bold text-sm">{{ session('success') }}</p></div>
        @endif
        @if (session()->has('error'))
            <div class="bg-rose-50 text-rose-700 px-6 py-4 rounded-[2rem] border border-rose-100 flex items-center mb-10 shadow-sm"><p class="font-bold text-sm">{{ session('error') }}</p></div>
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
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Cabang Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-6 relative w-24">
                                <button wire:click="viewCif({{ $item->id }})" class="flex items-center text-[10px] uppercase tracking-wider font-bold px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg transition-all"><span class="material-symbols-outlined text-[14px] mr-1">transfer_within_a_station</span> Mutasi</button>
                            </td>
                            <td class="py-4 px-6"><span class="text-sm font-extrabold text-slate-800 tracking-wider">{{ $item->cif_no }}</span></td>
                            <td class="py-4 px-6"><p class="font-bold text-sm text-slate-900">{{ $item->name }}</p></td>
                            <td class="py-4 px-6"><p class="font-bold text-xs text-slate-900">{{ $item->nik }}</p></td>
                            <td class="py-4 px-6"><p class="font-bold text-xs text-slate-700">{{ $item->branch->name ?? 'N/A' }}</p></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-24 text-center text-slate-400">
                                <span class="material-symbols-outlined text-5xl mb-4 opacity-20">youtube_searched_for</span>
                                <p class="text-sm font-bold">Lakukan pencarian untuk memunculkan target Mutasi.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages()) <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">{{ $items->links() }}</div> @endif
        </div>
        
        @else
        <!-- MUTATION FORM -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col mb-10">
            <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center space-x-4">
                <button wire:click="closeView" class="p-2 bg-white hover:bg-slate-200 rounded-xl transition-all shadow-sm border border-slate-200"><span class="material-symbols-outlined text-sm">arrow_back</span></button>
                <div>
                    <h2 class="font-extrabold text-sm text-slate-900 tracking-wider">Formulir Pemindahan Cabang: {{ $selectedCif->cif_no ?? '' }}</h2>
                </div>
            </div>

            <!-- Mini Profile Card (Read Only) -->
            <div class="p-8 bg-blue-50/20 border-b border-slate-100 relative overflow-hidden">
                <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-[120px] text-blue-500/5 select-none pointer-events-none">account_circle</span>
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
                        <p class="text-[9px] uppercase tracking-widest text-slate-400 font-bold mb-1">Status Rekening</p>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">{{ $selectedCif->status ?? '' }}</span>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-slate-400 font-bold mb-1">Cabang Tuan Rumah Saat Ini</p>
                        <p class="font-extrabold text-sm text-blue-600 mb-1">{{ $selectedCif->branch->name ?? 'N/A' }}</p>
                        <p class="text-[10px] text-slate-500 font-bold">{{ $selectedCif->marketing->name ?? 'TANPA MARKETING' }}</p>
                    </div>
                </div>
            </div>

            <div class="p-8 pb-12 space-y-8">
                <div>
                    <div class="border-b border-slate-200 pb-2 mb-6">
                        <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">transfer_within_a_station</span> Target Penempatan Baru</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Pindah Ke Cabang <span class="text-rose-500">*</span></label>
                            <select wire:model="branch_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                <option value="">Pilih Cabang Target</option>
                                @foreach($branches as $b) <option value="{{ $b->id }}">{{ $b->name }}</option> @endforeach
                            </select>
                            @error('branch_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Pindah Marketing Target (Opsional)</label>
                            <select wire:model="marketing_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                <option value="">Tanpa Perubahan Marketing</option>
                                @foreach($marketings as $m) <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->branch->name ?? '-' }})</option> @endforeach
                            </select>
                            @error('marketing_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="px-8 py-5 border-t border-slate-100 bg-slate-50 flex items-center justify-end">
                <button wire:click="submitMutation" class="px-8 py-3 bg-blue-600 text-white font-bold text-xs rounded-xl hover:bg-blue-700 hover:shadow-lg transition-all flex items-center">
                    <span class="material-symbols-outlined text-sm mr-2">send</span> Terapkan Mutasi
                </button>
            </div>
        </div>
        @endif
    </div>
</div>
