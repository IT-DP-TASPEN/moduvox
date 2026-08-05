<div class="p-0">
    <x-header title="Matrix Tarif Asuransi" subtitle="{{ $product->name }} - {{ $product->provider->name }}" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <a href="{{ route('insurance-products.index') }}" class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Kembali</span>
            </a>
        </x-slot>
    </x-header>

    <div class="px-10 py-6 border-b border-slate-100 bg-white flex items-center justify-between sticky top-0 z-20 shadow-sm">
        <div class="flex items-center space-x-3">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">add_circle</span>
                <input wire:model="searchAge" type="number" placeholder="Input Usia..." class="pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 w-36 focus:bg-white focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all">
            </div>
            <button wire:click="addAge" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-800 transition-all active:scale-95">Tambah Baris</button>
            <div class="w-px h-6 bg-slate-200 mx-2"></div>
            <label class="flex items-center space-x-2 cursor-pointer group">
                <input type="checkbox" wire:model.live="hideZeros" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest group-hover:text-slate-700 transition-colors">Sembunyikan Nilai 0</span>
            </label>
            <div class="w-px h-6 bg-slate-200 mx-2"></div>
            <button wire:click="addYear" class="flex items-center space-x-2 text-emerald-600 hover:bg-emerald-50 px-4 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Tambah Kolom JKW</span>
            </button>
        </div>

        <div class="flex items-center space-x-3">
            <button wire:click="initializeMatrix" wire:confirm="Inisialisasi akan membuat baris kosong untuk Usia 17-80. Lanjutkan?" class="flex items-center space-x-2 text-indigo-600 hover:bg-indigo-50 px-4 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all">
                <span class="material-symbols-outlined text-sm">auto_awesome</span>
                <span>Auto-Generate Matrix</span>
            </button>
            <div class="w-px h-6 bg-slate-200 mx-2"></div>
            
            <!-- Compact Import/Export Group -->
            <div class="flex items-center bg-slate-50 border border-slate-200 p-1 rounded-2xl">
                <div class="relative group">
                    <input type="file" wire:model="importFile" id="matrixImport" class="hidden">
                    <label for="matrixImport" class="flex items-center space-x-2 px-4 py-2 rounded-xl cursor-pointer hover:bg-white transition-all">
                        <span class="material-symbols-outlined text-sm text-slate-500">upload_file</span>
                        <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">{{ $importFile ? 'File Terpilih' : 'Pilih Excel' }}</span>
                    </label>
                </div>
                @if($importFile)
                    <button wire:click="importMatrix" wire:loading.attr="disabled" class="bg-indigo-600 text-white px-5 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-700 transition-all flex items-center space-x-2">
                        <span wire:loading wire:target="importMatrix" class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span wire:loading.remove wire:target="importMatrix">Import Sekarang</span>
                    </button>
                @endif
                <button wire:click="exportMatrix" class="p-2 text-slate-400 hover:text-indigo-600 transition-all rounded-xl" title="Download Matrix (Excel)">
                    <span class="material-symbols-outlined">download</span>
                </button>
            </div>
            <div class="w-px h-6 bg-slate-200 mx-1"></div>
            <button wire:click="clearMatrix" wire:confirm="Seluruh data tarif untuk produk ini akan dihapus. Lanjutkan?" class="p-2 text-slate-400 hover:text-rose-600 transition-all rounded-xl" title="Kosongkan Matrix">
                <span class="material-symbols-outlined">delete_sweep</span>
            </button>
        </div>
    </div>

    <div class="p-10">
        @if(session('success'))
            <div class="mb-6 px-5 py-3 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-700 text-xs font-bold flex items-center space-x-2 animate-in fade-in slide-in-from-top-2 duration-300">
                <span class="material-symbols-outlined text-sm">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 px-5 py-3 bg-rose-50 border border-rose-100 rounded-2xl text-rose-700 text-xs font-bold flex items-center space-x-2 animate-in fade-in slide-in-from-top-2 duration-300">
                <span class="material-symbols-outlined text-sm">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-fixed min-w-[1200px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th wire:click="toggleSort" class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase sticky left-0 bg-slate-50 z-10 border-r border-slate-100 w-24 cursor-pointer hover:text-indigo-600 transition-colors">
                                <div class="flex items-center space-x-1">
                                    <span>USIA</span>
                                    <span class="material-symbols-outlined text-xs">{{ $sortDir === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                </div>
                            </th>
                            @foreach($years as $year)
                                <th class="py-5 px-4 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center w-24">
                                    JKW {{ $year }} THN
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($paginatedAges as $ageRow)
                            @php $ageValue = $ageRow->age; @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="py-4 px-6 text-xs font-black text-slate-900 sticky left-0 bg-white group-hover:bg-slate-50 z-10 border-r border-slate-100 shadow-[2px_0_5px_rgba(0,0,0,0.02)]">
                                    <div class="flex items-center justify-between">
                                        <span>{{ $ageValue }} TAHUN</span>
                                        <button wire:click="deleteAge({{ $ageValue }})" wire:confirm="Hapus seluruh data tarif untuk usia {{ $ageValue }}?" class="opacity-0 group-hover:opacity-100 p-1 text-slate-300 hover:text-rose-500 transition-all">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                    </div>
                                </td>
                                @foreach($years as $year)
                                    @php 
                                        $rateItem = $rates[$ageValue][$year] ?? null;
                                        $rateValue = $rateItem ? $rateItem->rate : 0;
                                        $isEditing = $editingCell === "{$ageValue}-{$year}";
                                    @endphp
                                    <td class="py-2 px-1 text-center">
                                        @if($isEditing)
                                            <div class="flex flex-col items-center space-y-1">
                                                <input wire:model="editValue" 
                                                       wire:keydown.enter="saveEdit({{ $ageValue }}, {{ $year }})"
                                                       wire:keydown.escape="$set('editingCell', null)"
                                                       type="number" min="0" max="100" step="0.01" 
                                                       class="w-20 px-2 py-1.5 bg-white border-2 border-indigo-500 rounded-lg text-[11px] font-black text-indigo-700 text-center focus:outline-none shadow-lg shadow-indigo-500/10"
                                                       autofocus>
                                                <div class="flex space-x-1">
                                                    <button wire:click="saveEdit({{ $ageValue }}, {{ $year }})" class="p-1 bg-emerald-500 text-white rounded hover:bg-emerald-600 transition-all shadow-sm">
                                                        <span class="material-symbols-outlined text-[10px]">check</span>
                                                    </button>
                                                    <button wire:click="$set('editingCell', null)" class="p-1 bg-slate-400 text-white rounded hover:bg-slate-500 transition-all shadow-sm">
                                                        <span class="material-symbols-outlined text-[10px]">close</span>
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <button wire:click="startEdit({{ $ageValue }}, {{ $year }}, {{ $rateValue }})" 
                                                    class="w-full py-3 px-2 rounded-xl border border-transparent hover:border-indigo-100 hover:bg-indigo-50/30 hover:text-indigo-600 transition-all group/cell">
                                                <span class="text-[11px] font-bold {{ $rateValue > 0 ? 'text-slate-900' : 'text-slate-300' }}">
                                                    {{ format_percent($rateValue) }}
                                                </span>
                                            </button>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($paginatedAges->hasPages())
                <div class="px-10 py-6 border-t border-slate-50 bg-slate-50/30">
                    {{ $paginatedAges->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </div>
        
        <div class="mt-8 flex items-center justify-between px-6">
            <div class="flex items-center space-x-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                <div class="w-3 h-3 bg-indigo-50 rounded-full border border-indigo-100"></div>
                <span>Klik pada sel untuk mengubah tarif</span>
            </div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                Total Baris: {{ $paginatedAges->total() }} Usia
            </div>
        </div>
    </div>
</div>
