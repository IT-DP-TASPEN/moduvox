<div>
    <!-- Header -->
    <x-header title="Neraca Saldo" subtitle="Ringkasan saldo debit dan kredit untuk seluruh akun perkiraan"
        :user="auth()->user()" :role="auth()->user()->getRoleNames()->first()">
        <x-slot:actions>
            <div class="flex items-center space-x-3 print:hidden">
                <div class="flex items-center bg-white border border-surface-dim rounded-xl px-3 py-1.5 space-x-2">
                    <span class="text-outline text-[10px] font-bold uppercase tracking-widest">Periode</span>
                    <input type="date" wire:model.live="date_from"
                        class="bg-transparent border-none text-[10px] font-bold text-primary focus:ring-0 p-0">
                    <span class="text-outline text-[10px] font-bold">-</span>
                    <input type="date" wire:model.live="date_to"
                        class="bg-transparent border-none text-[10px] font-bold text-primary focus:ring-0 p-0">
                </div>
                <div class="relative">
                    <select wire:model.live="filter_branch"
                        class="pl-3 pr-8 py-2 bg-white border border-surface-dim rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all font-bold text-primary appearance-none cursor-pointer">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-sm text-outline">search</span>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari akun..."
                        class="w-48 pl-9 pr-3 py-2 bg-white border border-surface-dim rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all font-bold text-primary placeholder:text-outline/60">
                </div>
                <button wire:click="downloadExport"
                    class="p-2 bg-white border border-surface-dim rounded-xl text-outline hover:text-primary transition-all shadow-sm active:scale-95"
                    title="Export CSV">
                    <span class="material-symbols-outlined text-sm">download</span>
                </button>
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-8 space-y-6">
        <div class="hidden print:block mb-10 text-center relative">
            <h1 class="text-2xl font-black uppercase tracking-widest text-primary">{{ config('app.name', 'KOPERASI
                SEJAHTERA MUTIARA') }}</h1>
            <h2 class="text-sm font-bold uppercase tracking-[0.4em] text-outline mt-1 mb-4">Laporan Neraca Saldo</h2>
            <div
                class="flex justify-center items-center space-x-6 text-[10px] font-bold text-outline uppercase tracking-widest border-t border-b border-primary/10 py-3 mt-6">
                <span>Periode: {{ date('d F Y', strtotime($date_from)) }} - {{ date('d F Y', strtotime($date_to)) }}</span>
                <span class="w-1 h-1 bg-primary/20 rounded-full"></span>
                @if($filter_branch)
                <span>Cabang: {{ \App\Models\Branch::find($filter_branch)->name }}</span>
                @else
                <span>Cabang: Konsolidasi (Semua Cabang)</span>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-[1rem] shadow-sm border border-surface-dim overflow-hidden">
            <div class="max-h-[calc(100vh-18rem)] overflow-auto print:max-h-none print:overflow-visible">
            <table class="w-full min-w-[980px] text-left">
                <thead class="sticky top-0 z-20">
                    <tr
                        class="bg-surface shadow-sm border-b-2 border-primary/10 uppercase text-[10px] tracking-widest font-black text-outline">
                        <th class="px-10 py-7">Kode Akun</th>
                        <th class="px-10 py-7">Nama Akun</th>
                        <th class="px-10 py-7 text-right">Saldo Awal</th>
                        <th class="px-10 py-7 text-right">Debit</th>
                        <th class="px-10 py-7 text-right">Kredit</th>
                        <th class="px-10 py-7 text-right">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-dim">
                    @php
                    $profitLossRows = [
                        ['label' => 'Total Pendapatan', 'opening' => $profitLossSummary['opening']['revenue'], 'current' => $profitLossSummary['current']['revenue']],
                        ['label' => 'Total Beban', 'opening' => $profitLossSummary['opening']['expense'], 'current' => $profitLossSummary['current']['expense']],
                        ['label' => 'Laba/Rugi', 'opening' => $profitLossSummary['opening']['profit'], 'current' => $profitLossSummary['current']['profit']],
                        ['label' => 'Taksiran Pajak', 'opening' => $profitLossSummary['opening']['estimated_tax'], 'current' => $profitLossSummary['current']['estimated_tax']],
                    ];
                    @endphp

                    @forelse($groupedCoas as $type => $coas)
                    <tr class="bg-surface/30">
                        <td colspan="6"
                            class="px-8 py-3 text-[10px] font-black text-primary/40 uppercase tracking-[0.2em] italic">
                            {{ $type }}
                        </td>
                    </tr>
                    @foreach($coas as $coa)
                    <tr class="hover:bg-primary/[0.02] transition-colors group">
                        <td class="px-10 py-5">
                            <span
                                class="font-mono text-[10px] bg-surface-dim/30 px-2 py-0.5 rounded text-outline font-black tracking-tighter">{{
                                $coa->coa_code }}</span>
                        </td>
                        <td class="px-10 py-5">
                            <a href="{{ route('ledger.index', ['filter_coa' => $coa->id, 'filter_branch' => $filter_branch, 'date_from' => $date_from, 'date_to' => $date_to]) }}"
                                wire:navigate
                                class="text-[13px] font-bold text-primary hover:text-primary-dim hover:underline transition-all">
                                {{ $coa->name }}
                            </a>
                        </td>
                        <td class="px-10 py-5 text-right">
                            <p
                                class="text-xs font-bold {{ $coa->opening_balance != 0 ? 'text-primary' : 'text-outline opacity-20' }}">
                                {{ number_format($coa->opening_balance, 2) }}
                            </p>
                        </td>
                        <td class="px-10 py-5 text-right">
                            <p
                                class="text-xs font-bold {{ $coa->mutation_debit > 0 ? 'text-primary' : 'text-outline opacity-20' }}">
                                {{ number_format($coa->mutation_debit, 2) }}
                            </p>
                        </td>
                        <td class="px-10 py-5 text-right">
                            <p
                                class="text-xs font-bold {{ $coa->mutation_credit > 0 ? 'text-primary' : 'text-outline opacity-20' }}">
                                {{ number_format($coa->mutation_credit, 2) }}
                            </p>
                        </td>
                        <td class="px-10 py-5 text-right">
                            <p class="text-xs font-black text-primary">
                                {{ number_format($coa->balance, 2) }}
                            </p>
                        </td>
                    </tr>
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="6" class="px-10 py-16 text-center">
                            <div class="flex flex-col items-center space-y-3">
                                <div class="w-12 h-12 bg-primary/5 text-primary rounded-full flex items-center justify-center">
                                    <span class="material-symbols-outlined">search_off</span>
                                </div>
                                <div>
                                    <h3 class="text-xs font-black text-primary uppercase tracking-widest">Akun Tidak Ditemukan</h3>
                                    <p class="mt-1 text-[10px] font-medium text-outline">Coba gunakan kata kunci kode atau nama akun lain.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                    
                    @foreach($profitLossRows as $row)
                    <tr class="bg-surface border-t border-primary/10 font-black">
                        <td class="px-10 py-4 text-xs text-primary uppercase tracking-[0.1em]" colspan="2">{{ $row['label'] }}</td>
                        <td class="px-10 py-4 text-right text-xs text-primary">{{ number_format($row['opening'], 2) }}</td>
                        <td class="px-10 py-4"></td>
                        <td class="px-10 py-4"></td>
                        <td class="px-10 py-4 text-right text-xs text-primary bg-primary/5">{{ number_format($row['current'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <!-- Footer / Signature Area for Printing -->
        <div class="hidden print:grid grid-cols-2 gap-12 mt-16 text-center">
            <div class="space-y-20">
                <p class="text-[10px] font-bold uppercase tracking-widest">Disiapkan Oleh,</p>
                <div class="border-t border-black w-32 mx-auto"></div>
                <p class="text-[9px] font-medium">( ............................ )</p>
            </div>
            <div class="space-y-20">
                <p class="text-[10px] font-bold uppercase tracking-widest">Disetujui Oleh,</p>
                <div class="border-t border-black w-32 mx-auto"></div>
                <p class="text-[9px] font-medium">( ............................ )</p>
            </div>
        </div>

        <!-- Info Card -->
        <div class="grid grid-cols-2 gap-6 print:hidden">
            <div class="bg-amber-50 border border-amber-100 p-6 rounded-[2rem] flex items-start space-x-4">
                <span class="material-symbols-outlined text-amber-600">info</span>
                <div>
                    <h4 class="text-[10px] font-black text-amber-900 uppercase tracking-widest mb-1">Catatan Drill-Down
                    </h4>
                    <p class="text-[10px] text-amber-800/80 leading-relaxed">Klik pada <b>Nama Akun</b> untuk melihat
                        rincian transaksi (Buku Besar) yang membentuk saldo tersebut hingga tanggal yang dipilih.</p>
                </div>
            </div>
            <div class="bg-blue-50 border border-blue-100 p-6 rounded-[2rem] flex items-start space-x-4">
                <span class="material-symbols-outlined text-blue-600">verified</span>
                <div>
                    <h4 class="text-[10px] font-black text-blue-900 uppercase tracking-widest mb-1">Saldo Normal</h4>
                    <p class="text-[10px] text-blue-800/80 leading-relaxed">Saldo dihitung berdasarkan tipe akun.
                        <b>Aset/Beban</b> (Debit - Kredit), sedangkan <b>Kewajiban/Modal/Pendapatan</b> (Kredit -
                        Debit).
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
