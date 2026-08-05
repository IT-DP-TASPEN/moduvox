<div>
    <!-- Header -->
    <x-header title="Jurnal Umum" subtitle="Pencatatan transaksi keuangan dual-entry manual" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first()">
        <x-slot:actions>
             @if($viewMode === 'list')
                <div class="flex items-center space-x-3">
                    @can('journals.create')
                    <button wire:click="create" class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-xl hover:bg-primary-dim transition-all shadow-sm active:scale-95">
                        <span class="material-symbols-outlined text-sm">add</span>
                        <span class="text-xs font-bold uppercase tracking-wider">Buat Jurnal</span>
                    </button>
                    @endcan
                </div>
            @else
                <button wire:click="$set('viewMode', 'list')" class="flex items-center space-x-2 bg-surface border border-surface-dim text-primary px-4 py-2 rounded-xl hover:bg-surface-dim transition-all shadow-sm active:scale-95">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span class="text-xs font-bold uppercase tracking-wider">Kembali</span>
                </button>
            @endif
        </x-slot:actions>
    </x-header>

    <div class="p-8">
        @if($viewMode === 'list')
            <div class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-4 bg-white border border-surface-dim rounded-3xl p-5 shadow-sm">
                <div class="space-y-2 md:col-span-2">
                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Cari</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-sm">search</span>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Ref, keterangan, kode/nama COA..." class="w-full pl-12 pr-4 py-3 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Dari Tanggal</label>
                    <input wire:model.live="date_from" type="date" class="w-full px-4 py-3 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Sampai Tanggal</label>
                    <input wire:model.live="date_to" type="date" class="w-full px-4 py-3 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Tampilkan</label>
                    <div class="flex gap-2">
                        <select wire:model.live="perPage" class="w-full px-4 py-3 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <button wire:click="resetFilters" type="button" class="px-4 py-3 bg-surface border border-surface-dim rounded-2xl text-outline hover:text-primary hover:bg-surface-dim transition-all" title="Reset Filter">
                            <span class="material-symbols-outlined text-sm">refresh</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-surface-dim overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-surface border-b border-surface-dim uppercase text-[10px] tracking-widest font-bold text-outline">
                            <th class="px-8 py-5">Tanggal</th>
                            <th class="px-8 py-5">Referensi</th>
                            <th class="px-8 py-5">Keterangan</th>
                            <th class="px-8 py-5 text-center">Jenis</th>
                            <th class="px-8 py-5 text-right">Total Debit</th>
                            <th class="px-8 py-5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-dim">
                        @foreach($journals as $j)
                            <tr class="hover:bg-surface/50 transition-colors group">
                                <td class="px-8 py-4">
                                    <p class="text-xs font-bold text-primary">{{ $j->transaction_date->format('d M Y') }}</p>
                                </td>
                                <td class="px-8 py-4">
                                    <span class="bg-primary/5 text-primary text-[10px] font-black px-2 py-1 rounded-lg border border-primary/10 tracking-widest uppercase">{{ $j->reference_no }}</span>
                                </td>
                                <td class="px-8 py-4 max-w-md">
                                    <p class="text-xs font-medium text-outline whitespace-normal break-words">{{ $j->description }}</p>
                                </td>
                                <td class="px-8 py-4 text-center">
                                    @if($j->is_revision)
                                        <span class="bg-orange-50 text-orange-600 text-[9px] font-bold px-2 py-1 rounded-lg border border-orange-100 uppercase tracking-widest">Revisi</span>
                                    @else
                                        <span class="bg-blue-50 text-blue-600 text-[9px] font-bold px-2 py-1 rounded-lg border border-blue-100 uppercase tracking-widest">Baru</span>
                                    @endif
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <p class="text-xs font-black text-primary">{{ number_format($j->total_debit, 2) }}</p>
                                </td>
                                <td class="px-8 py-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider
                                        @if($j->status == 'APPROVED') bg-green-50 text-green-600 border border-green-100
                                        @elseif($j->status == 'PENDING') bg-amber-50 text-amber-600 border border-amber-100
                                        @else bg-red-50 text-red-600 border border-red-100 @endif">
                                        {{ $j->status }}
                                    </span>
                                </td>
                            </tr>
                            @if($j->entries->isNotEmpty())
                                <tr class="bg-surface/20">
                                    <td colspan="6" class="px-8 py-4">
                                        <div class="overflow-hidden rounded-2xl border border-surface-dim bg-white">
                                            <table class="w-full text-left">
                                                <thead class="bg-surface/70">
                                                    <tr class="text-[9px] font-black uppercase tracking-widest text-outline">
                                                        <th class="px-4 py-3">COA</th>
                                                        <th class="px-4 py-3">Keterangan Detail</th>
                                                        <th class="px-4 py-3 text-right">Debit</th>
                                                        <th class="px-4 py-3 text-right">Kredit</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-surface-dim">
                                                    @foreach($j->entries as $entry)
                                                        <tr>
                                                            <td class="px-4 py-3">
                                                                <p class="text-[10px] font-black text-primary">{{ $entry->coa?->coa_code }}</p>
                                                                <p class="text-[10px] font-bold text-outline uppercase">{{ $entry->coa?->name }}</p>
                                                            </td>
                                                            <td class="px-4 py-3 text-[10px] font-bold text-slate-700 uppercase">
                                                                {{ $entry->description ?: ($entry->coa?->name ?: $j->description) }}
                                                            </td>
                                                            <td class="px-4 py-3 text-right text-[10px] font-black text-emerald-600">
                                                                {{ (float) $entry->debit > 0 ? number_format($entry->debit, 2, ',', '.') : '-' }}
                                                            </td>
                                                            <td class="px-4 py-3 text-right text-[10px] font-black text-rose-600">
                                                                {{ (float) $entry->credit > 0 ? number_format($entry->credit, 2, ',', '.') : '-' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                <div class="px-8 py-5 border-t border-surface-dim bg-surface/30">
                    {{ $journals->links() }}
                </div>
            </div>
        @else
            <!-- Form View -->
            <div class="max-w-6xl mx-auto space-y-6">
                 @if(session()->has('error'))
                    <div class="p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center space-x-3">
                        <span class="material-symbols-outlined text-red-600 text-sm">warning</span>
                        <p class="text-[10px] font-bold text-red-700 uppercase tracking-widest">{{ session('error') }}</p>
                    </div>
                @endif

                <!-- Header Card -->
                <div class="bg-white rounded-[2rem] shadow-xl border border-surface-dim p-10">
                    <div class="grid grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Tanggal Transaksi</label>
                            <input wire:model="transaction_date" type="date" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Keterangan Jurnal</label>
                            <input wire:model="description" type="text" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10 transition-all" placeholder="Deskripsi transaksi...">
                        </div>
                        <div class="col-span-2 space-y-2">
                            <label class="text-[10px] font-black text-outline uppercase tracking-widest ml-1">Jenis Jurnal</label>
                            <div class="flex items-center space-x-6">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input wire:model="is_revision" type="radio" value="0" class="w-4 h-4 text-primary border-surface-dim focus:ring-primary">
                                    <span class="text-xs font-bold text-outline">Jurnal Baru</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input wire:model="is_revision" type="radio" value="1" class="w-4 h-4 text-primary border-surface-dim focus:ring-primary">
                                    <span class="text-xs font-bold text-outline">Jurnal Revisi</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lines Card -->
                <div class="bg-white rounded-[2rem] shadow-xl border border-surface-dim overflow-hidden">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-surface border-b border-surface-dim uppercase text-[10px] tracking-widest font-bold text-outline">
                                <th class="px-8 py-5 min-w-[300px]">Akun (COA)</th>
                                <th class="px-8 py-5 text-right w-48">Saldo Sebelum</th>
                                <th class="px-8 py-5 text-right w-48">Estimasi Setelah</th>
                                <th class="px-8 py-5 text-right w-48">Debit</th>
                                <th class="px-8 py-5 text-right w-48">Kredit</th>
                                <th class="px-8 py-5 text-center w-16"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-dim">
                            @php($balancePreviews = $this->entry_balance_previews)
                            @foreach($entries as $index => $entry)
                                <tr>
                                    <td class="px-8 py-4">
                                        <input wire:model.live.debounce.300ms="entries.{{ $index }}.coa_search"
                                            type="text"
                                            list="journal-coa-options-{{ $index }}"
                                            class="w-full px-4 py-3 bg-surface border border-surface-dim rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"
                                            placeholder="Cari kode / nama COA...">
                                        <datalist id="journal-coa-options-{{ $index }}">
                                            @foreach($coas as $coa)
                                                <option value="{{ $coa->coa_code }} - {{ $coa->name }}"></option>
                                            @endforeach
                                        </datalist>
                                        @error("entries.$index.coa_id") <p class="text-[9px] text-red-500 font-bold mt-1 uppercase">Pilih COA dari hasil pencarian.</p> @enderror
                                    </td>
                                    @php($preview = $balancePreviews[$index] ?? null)
                                    <td class="px-8 py-4 text-right">
                                        @if($preview)
                                            <p class="text-xs font-black text-primary">{{ number_format($preview['before'], 2, ',', '.') }}</p>
                                            <p class="text-[9px] font-bold text-outline uppercase tracking-widest">Normal {{ $preview['normal_side'] }}</p>
                                        @else
                                            <p class="text-xs font-black text-outline">-</p>
                                        @endif
                                    </td>
                                    <td class="px-8 py-4 text-right">
                                        @if($preview)
                                            <p class="text-xs font-black {{ $preview['after'] < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ number_format($preview['after'], 2, ',', '.') }}</p>
                                            <p class="text-[9px] font-bold {{ $preview['delta'] < 0 ? 'text-rose-500' : 'text-emerald-600' }}">
                                                {{ $preview['delta'] >= 0 ? '+' : '' }}{{ number_format($preview['delta'], 2, ',', '.') }}
                                            </p>
                                        @else
                                            <p class="text-xs font-black text-outline">-</p>
                                        @endif
                                    </td>
                                    <td class="px-8 py-4">
                                        <input wire:model.live="entries.{{ $index }}.debit" type="number" step="0.01" class="w-full px-4 py-3 bg-surface border border-surface-dim rounded-xl text-xs font-bold text-right focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                                    </td>
                                    <td class="px-8 py-4">
                                        <input wire:model.live="entries.{{ $index }}.credit" type="number" step="0.01" class="w-full px-4 py-3 bg-surface border border-surface-dim rounded-xl text-xs font-bold text-right focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @if(count($entries) > 2)
                                            <button wire:click="removeEntry({{ $index }})" class="text-red-400 hover:text-red-600 transition-colors">
                                                <span class="material-symbols-outlined text-sm">remove_circle</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-surface/30">
                            <tr>
                                <td class="px-8 py-6" colspan="3">
                                    <button wire:click="addEntry" class="flex items-center space-x-2 text-[10px] font-black text-primary uppercase tracking-widest hover:text-primary-dim transition-colors">
                                        <span class="material-symbols-outlined text-sm">add_circle</span>
                                        <span>Tambah Baris</span>
                                    </button>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <p class="text-[9px] font-black text-outline uppercase tracking-widest mb-1">Total Debit</p>
                                    <p class="text-xs font-black text-primary">{{ number_format($this->total_debit, 2) }}</p>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <p class="text-[9px] font-black text-outline uppercase tracking-widest mb-1">Total Kredit</p>
                                    <p class="text-xs font-black text-primary">{{ number_format($this->total_credit, 2) }}</p>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="px-10 py-8 border-t border-surface-dim bg-surface/10 flex justify-between items-center">
                        <div>
                            @if(abs($this->total_debit - $this->total_credit) <= 0.001 && $this->total_debit > 0)
                                <div class="flex items-center space-x-2 text-green-600">
                                    <span class="material-symbols-outlined text-sm font-bold">check_circle</span>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Jurnal Seimbang</span>
                                </div>
                            @elseif($this->total_debit > 0 || $this->total_credit > 0)
                                <div class="flex items-center space-x-2 text-red-600">
                                    <span class="material-symbols-outlined text-sm font-bold">cancel</span>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Selisih: {{ number_format(abs($this->total_debit - $this->total_credit), 2) }}</span>
                                </div>
                            @endif
                        </div>
                        <button wire:click="save" class="bg-primary hover:bg-primary-dim text-white px-12 py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-primary/20 transition-all active:scale-95">
                            Kirim Persetujuan Jurnal
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
