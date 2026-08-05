<div>
    <!-- Header -->
    <x-header title="Buku Besar" subtitle="Laporan jejak transaksi dan historis saldo per akun" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first()">
        <x-slot:actions>
             <div class="flex items-center space-x-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm">search</span>
                    <input wire:model.live.debounce.300ms="coaSearch"
                        type="text"
                        list="ledger-coa-options"
                        placeholder="Cari COA..."
                        class="w-72 pl-10 pr-4 py-2 bg-white border border-surface-dim rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all font-bold text-primary">
                    <datalist id="ledger-coa-options">
                        @foreach($coas as $coa)
                            <option value="{{ $coa->coa_code }} - {{ $coa->name }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="flex items-center bg-white border border-surface-dim rounded-xl px-3 py-1.5 space-x-2">
                    <input type="date" wire:model.live="date_from" class="bg-transparent border-none text-[10px] font-bold text-primary focus:ring-0 p-0">
                    <span class="text-outline text-[10px] font-bold">s/d</span>
                    <input type="date" wire:model.live="date_to" class="bg-transparent border-none text-[10px] font-bold text-primary focus:ring-0 p-0">
                </div>
                <button wire:click="downloadExport" class="p-2 bg-white border border-surface-dim rounded-xl text-outline hover:text-primary transition-all shadow-sm" title="Export CSV">
                    <span class="material-symbols-outlined text-sm">download</span>
                </button>
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-8 space-y-6">
        @if($filter_coa)
            <div class="grid grid-cols-3 gap-6">
                @php $coa = \App\Models\Coa::find($filter_coa); @endphp
                <div class="bg-white p-6 rounded-3xl border border-surface-dim shadow-sm">
                    <p class="text-[10px] font-black text-outline uppercase tracking-widest mb-1">Saldo Awal</p>
                    <p class="text-xl font-black text-primary">{{ number_format($openingBalance, 2) }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-surface-dim shadow-sm">
                    <p class="text-[10px] font-black text-outline uppercase tracking-widest mb-1 text-green-600">Total Mutasi Debit</p>
                    <p class="text-xl font-black text-green-700">{{ number_format($entries->sum('debit'), 2) }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-surface-dim shadow-sm">
                    <p class="text-[10px] font-black text-outline uppercase tracking-widest mb-1 text-red-600">Total Mutasi Kredit</p>
                    <p class="text-xl font-black text-red-700">{{ number_format($entries->sum('credit'), 2) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-surface-dim overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-surface border-b border-surface-dim uppercase text-[10px] tracking-widest font-bold text-outline">
                            <th class="px-8 py-5">Tanggal</th>
                            <th class="px-8 py-5">Referensi</th>
                            <th class="px-8 py-5 w-[38%]">Keterangan</th>
                            <th class="px-8 py-5 text-right">Debit</th>
                            <th class="px-8 py-5 text-right">Kredit</th>
                            <th class="px-8 py-5 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-dim">
                        <!-- Opening Balance Row -->
                        <tr class="bg-surface/30">
                            <td class="px-8 py-4 text-[10px] font-bold text-outline uppercase tracking-widest" colspan="3 text-center">Saldo Awal per {{ date('d M Y', strtotime($date_from)) }}</td>
                            <td class="px-8 py-4"></td>
                            <td class="px-8 py-4"></td>
                            <td class="px-8 py-4 text-right">
                                <p class="text-xs font-black text-primary">{{ number_format($openingBalance, 2) }}</p>
                            </td>
                        </tr>

                        @php $runningBalance = $openingBalance; @endphp
                        @foreach($entries as $entry)
                            @php 
                                if (in_array($coa->type, ['ASSET', 'EXPENSE'])) {
                                    $runningBalance += ($entry->debit - $entry->credit);
                                } else {
                                    $runningBalance += ($entry->credit - $entry->debit);
                                }
                            @endphp
                            <tr class="hover:bg-surface/50 transition-colors">
                                <td class="px-8 py-4">
                                    <p class="text-xs font-bold text-primary opacity-60">{{ $entry->journal->transaction_date->format('d M Y') }}</p>
                                </td>
                                <td class="px-8 py-4">
                                    @php($referenceNo = $entry->reference_no ?: $entry->journal->reference_no)
                                    <a href="{{ route('journals.index', ['search' => $referenceNo]) }}"
                                        wire:navigate
                                        class="inline-flex text-[10px] font-black text-primary bg-primary/5 px-2 py-0.5 rounded border border-primary/10 tracking-widest uppercase hover:bg-primary/10 hover:border-primary/30 transition-all"
                                        title="Lihat di Jurnal Umum">
                                        {{ $referenceNo }}
                                    </a>
                                    @if($entry->reference_no)
                                        <p class="mt-1 text-[9px] font-bold text-outline uppercase tracking-widest">{{ $entry->journal->reference_no }}</p>
                                    @endif
                                </td>
                                <td class="px-8 py-4 align-top">
                                    <p class="text-xs font-medium text-outline whitespace-normal break-words leading-relaxed">{{ $entry->description ?: $entry->journal->description }}</p>
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <p class="text-xs font-bold {{ $entry->debit > 0 ? 'text-green-600' : 'text-outline opacity-20' }}">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}</p>
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <p class="text-xs font-bold {{ $entry->credit > 0 ? 'text-red-600' : 'text-outline opacity-20' }}">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}</p>
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <p class="text-xs font-black text-primary">{{ number_format($runningBalance, 2) }}</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-surface">
                        <tr class="font-black">
                            <td class="px-8 py-6 text-xs text-primary uppercase tracking-widest" colspan="3">Saldo Akhir per {{ date('d M Y', strtotime($date_to)) }}</td>
                            <td class="px-8 py-6 text-right text-xs text-green-700">{{ number_format($entries->sum('debit'), 2) }}</td>
                            <td class="px-8 py-6 text-right text-xs text-red-700">{{ number_format($entries->sum('credit'), 2) }}</td>
                            <td class="px-8 py-6 text-right text-xs text-primary">{{ number_format($runningBalance, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="px-6 py-24 text-center">
                <div class="flex flex-col items-center space-y-4">
                    <div class="w-16 h-16 bg-primary/5 text-primary rounded-full flex items-center justify-center mb-2">
                        <span class="material-symbols-outlined text-4xl">search_insights</span>
                    </div>
                    <h3 class="text-lg font-black text-primary uppercase tracking-widest">Pilih Akun Terlebih Dahulu</h3>
                    <p class="text-xs text-outline max-w-sm mx-auto font-medium">Gunakan filter di bagian atas untuk memilih nomor perkiraan (COA) dan rentang waktu laporan.</p>
                </div>
            </div>
        @endif
    </div>
</div>
