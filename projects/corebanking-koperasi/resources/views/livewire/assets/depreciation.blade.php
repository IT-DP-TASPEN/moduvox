<div class="p-0">
    <x-header title="Eksekusi Penyusutan Aset" subtitle="Hitung dan catat penyusutan aset kantor per periode" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <a href="{{ route('assets.inquiry') }}" wire:navigate class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Inventaris</span>
            </a>
        </x-slot>
    </x-header>

    <div class="p-10 space-y-8">
        @if(session('success'))
            <div class="px-5 py-3 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-700 text-xs font-bold flex items-center space-x-2">
                <span class="material-symbols-outlined text-sm">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Control Panel -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 p-8">
            <div class="flex flex-wrap items-end gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Periode Penyusutan <span class="text-rose-500">*</span></label>
                    <input wire:model="period" type="month" class="px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                    @error('period') <span class="text-[9px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Filter Kategori</label>
                    <select wire:model="filterCategory" class="px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button wire:click="preview" class="px-8 py-3.5 bg-white border-2 border-slate-900 text-slate-900 font-bold text-xs rounded-2xl hover:bg-slate-50 transition-all flex items-center space-x-2">
                    <div wire:loading wire:target="preview" class="w-4 h-4 border-2 border-slate-400 border-t-slate-900 rounded-full animate-spin"></div>
                    <span class="material-symbols-outlined text-sm" wire:loading.remove wire:target="preview">preview</span>
                    <span>Pratinjau Penyusutan</span>
                </button>
                @if($isPreviewed && !empty($previewList))
                <button wire:click="execute" wire:confirm="Yakin eksekusi penyusutan untuk {{ count($previewList) }} aset pada periode {{ $period }}? Aksi ini tidak dapat dibatalkan." class="px-8 py-3.5 bg-rose-600 text-white font-bold text-xs rounded-2xl hover:bg-rose-700 transition-all flex items-center space-x-2">
                    <div wire:loading wire:target="execute" class="w-4 h-4 border-2 border-red-300 border-t-white rounded-full animate-spin"></div>
                    <span class="material-symbols-outlined text-sm" wire:loading.remove wire:target="execute">play_arrow</span>
                    <span>Eksekusi Penyusutan ({{ count($previewList) }} Aset)</span>
                </button>
                @endif
            </div>
        </div>

        <!-- Preview Table -->
        @if($isPreviewed)
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden animate-in fade-in duration-300">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-outlined text-slate-900">trending_down</span>
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-900">Pratinjau Penyusutan — Periode {{ $period }}</h4>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ count($previewList) }} aset akan diproses</p>
                    </div>
                </div>
            </div>
            @if(empty($previewList))
                <div class="py-20 text-center">
                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-3">check_circle</span>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Semua aset sudah diproses untuk periode ini, atau tidak ada aset yang memenuhi syarat.</p>
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-slate-100">
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Kode Aset</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Nama Aset</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Kategori</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Metode</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Nilai Buku Awal</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Penyusutan</th>
                            <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Nilai Buku Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($previewList as $item)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-4 px-6 text-[10px] font-black text-indigo-600">{{ $item['asset_code'] }}</td>
                            <td class="py-4 px-6 text-[10px] font-bold text-slate-900">{{ $item['name'] }}</td>
                            <td class="py-4 px-6 text-[10px] font-bold text-slate-500">{{ $item['category'] }}</td>
                            <td class="py-4 px-6">
                                <span class="text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-widest bg-slate-100 text-slate-600 border border-slate-200">{{ $item['status'] }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-widest {{ $item['method'] === 'PERCENTAGE' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-purple-50 text-purple-600 border border-purple-100' }}">
                                    {{ $item['method'] }}
                                    @if($item['method'] === 'PERCENTAGE')
                                        ({{ format_percent($item['rate_or_nominal']) }})
                                    @else
                                        (Rp {{ number_format($item['rate_or_nominal'], 2, ',', '.') }})
                                    @endif
                                </span>
                            </td>
                            <td class="py-4 px-6 text-[10px] font-bold text-slate-600 text-right">Rp {{ number_format($item['opening_book_value'], 2, ',', '.') }}</td>
                            <td class="py-4 px-6 text-[10px] font-black text-rose-600 text-right">- Rp {{ number_format($item['depreciation_amount'], 2, ',', '.') }}</td>
                            <td class="py-4 px-6 text-[10px] font-black text-emerald-600 text-right">Rp {{ number_format($item['closing_book_value'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                        <tr>
                            <td colspan="5" class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">TOTAL PENYUSUTAN PERIODE INI</td>
                            <td class="py-4 px-6 text-right"></td>
                            <td class="py-4 px-6 text-sm font-black text-rose-600 text-right">- Rp {{ number_format(collect($previewList)->sum('depreciation_amount'), 2, ',', '.') }}</td>
                            <td class="py-4 px-6 text-sm font-black text-emerald-600 text-right">Rp {{ number_format(collect($previewList)->sum('closing_book_value'), 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
