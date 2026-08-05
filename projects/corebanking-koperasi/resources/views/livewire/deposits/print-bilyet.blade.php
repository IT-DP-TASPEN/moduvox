<div class="p-0">
    <x-header title="Cetak Bilyet Simpanan Berjangka" subtitle="Pencetakan sertifikat bilyet berjangka sebagai bukti kepemilikan dana" :user="$user" :role="$role">
        <x-slot name="actions">
            @if($viewMode === 'list')
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <select wire:model.live="filter_branch" class="pl-3 pr-10 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 appearance-none shadow-sm">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                            <span class="material-symbols-outlined text-sm">expand_more</span>
                        </div>
                    </div>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                        <input wire:model.live.debounce.500ms="search" type="text" placeholder="No Rekening / Bilyet / Anggota..." 
                            class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold w-80 shadow-sm">
                    </div>
                </div>
            @else
                <button wire:click="closeView" class="flex items-center space-x-2 bg-white text-slate-600 border border-slate-200 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Kembali ke Daftar</span>
                </button>
            @endif
        </x-slot>
    </x-header>

    <div class="p-10">
        @if($viewMode === 'list')
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden animate-in fade-in duration-500">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50">
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center w-20">OPSI</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">No. Rekening</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Nama Anggota</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Kode Bilyet</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Pokok Simpanan Berjangka</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Jatuh Tempo</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($items as $item)
                                <tr wire:key="print-row-{{ $item->id }}" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="py-4 px-6 text-center">
                                        <button wire:click="selectAccount({{ $item->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-slate-900 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto">
                                            <span class="material-symbols-outlined text-sm">print</span>
                                        </button>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-slate-900 tracking-tight">{{ $item->account_no }}</span>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $item->product->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-black text-xs text-slate-900 uppercase leading-none mb-1">{{ $item->cif->name }}</p>
                                        <p class="text-[9px] text-slate-400 font-bold tracking-widest uppercase">{{ $item->cif->cif_no }}</p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black bg-slate-100 text-slate-600 uppercase tracking-widest">{{ $item->bilyet?->kode_bilyet ?? 'NON-BILYET' }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <p class="font-black text-xs text-slate-900 tracking-tighter">Rp {{ number_format($item->amount, 2, ',', '.') }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black text-slate-900 tracking-widest">{{ $item->maturity_date->format('d/m/Y') }}</span>
                                            @php $days = now()->diffInDays($item->maturity_date, false); @endphp
                                            <span class="text-[9px] font-black uppercase tracking-tighter {{ $days <= 0 ? 'text-rose-500' : 'text-amber-500' }}">
                                                {{ $days <= 0 ? 'Sudah Jatuh Tempo' : "H - $days Hari" }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded border bg-emerald-50 text-emerald-600 border-emerald-100">
                                            ACTIVE
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-32 text-center text-slate-300">
                                        @if(!$search && !$filter_branch)
                                            <span class="material-symbols-outlined text-6xl mb-4 opacity-50">search</span>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Silakan cari nomor rekening atau nama anggota<br>untuk melakukan cetak bilyet simpanan berjangka</p>
                                        @else
                                            <span class="material-symbols-outlined text-6xl mb-4 opacity-50">drafts</span>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">Data tidak ditemukan untuk pencarian: "{{ $search }}"</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($items->hasPages())
                    <div class="px-8 py-4 bg-white/50 border-t border-slate-50 font-bold text-xs uppercase tracking-widest">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- PRINT PREVIEW SECTION -->
            <div class="max-w-5xl mx-auto space-y-12 animate-in zoom-in-95 duration-300 no-print pb-20">
                <div class="bg-white rounded-[3rem] shadow-2xl border border-slate-200/60 p-12 space-y-10 relative overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-8">
                        <div class="flex items-center space-x-5">
                            <div class="w-14 h-14 rounded-2xl bg-slate-900 flex items-center justify-center text-white shadow-lg shadow-slate-900/20">
                                <span class="material-symbols-outlined text-3xl">verified_user</span>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 uppercase tracking-tight">Pratinjau Cetak Bilyet</h3>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Pastikan menggunakan kertas bilyet resmi Sirara</p>
                            </div>
                        </div>
                        <button onclick="window.print()" class="bg-slate-900 hover:shadow-xl hover:shadow-slate-900/20 text-white px-10 py-5 rounded-[2rem] font-black text-xs uppercase tracking-[0.2em] transition-all active:scale-95 flex items-center space-x-3">
                            <span class="material-symbols-outlined text-sm">print</span>
                            <span>Cetak Bilyet</span>
                        </button>
                    </div>

                    <!-- Layout Bilyet (Preview Graphic - screen only) -->
                    <div class="relative rounded-xl p-8 space-y-6 shadow-inner overflow-hidden" style="background-color: #F8F5EE; border: 2px solid #E5E0D8;" id="bilyet-canvas">
                        <div class="absolute inset-0 opacity-[0.05] pointer-events-none select-none flex items-center justify-center">
                             <img src="{{ asset('logo.png') }}" alt="Watermark" style="width: 50%;">
                        </div>

                        <div class="relative z-10 flex items-center border-b-[3px] border-slate-800 pb-4 mb-4 space-x-4">
                            <div class="w-24 flex-shrink-0 ml-4">
                                <img src="{{ asset('logo.png') }}" alt="Logo" class="w-full">
                            </div>
                            <div class="flex-grow text-center">
                                <h1 class="text-xl font-black text-slate-900 tracking-tight">KOPERASI SEJAHTERA MUTIARA</h1>
                                <p class="text-[10px] text-slate-800 mt-1">Ruko Mutiara Bekasi Center Blok B 9 Nomor 1</p>
                                <p class="text-[10px] text-slate-800">Jl Ahmad Yani, Margajaya, Kota Bekasi</p>
                            </div>
                            <div class="w-24 flex-shrink-0"></div> <!-- Spacer -->
                        </div>

                        <div class="relative z-10 text-slate-800">
                            <h2 class="text-center text-lg font-black tracking-widest uppercase mt-4">Bilyet Simpanan Berjangka</h2>
                             <h5 class="text-center text-xs  tracking-widest">Nomor : {{ $selectedAccount->bilyet?->kode_bilyet ?? '-' }}</h5>
                            

                            <div class="grid grid-cols-[130px_10px_1fr] gap-y-2 text-xs ml-16 mr-10 mt-6 items-start">
                                <div class="self-center">No. Seri</div><div class="self-center">:</div><div class="font-bold self-center">{{ $selectedAccount->bilyet?->bilyet_number ?? '-' }}</div>
                                <div class="mt-3 self-center">Nama</div><div class="mt-3 self-center">:</div><div class="mt-3 font-bold uppercase self-center">{{ $selectedAccount->cif->name }}</div>
                                <div class="self-center">Alamat</div><div class="self-center">:</div><div class="self-center">{{ $selectedAccount->cif->alamat_lengkap }}</div>
                                <div class="self-center">Uang Sejumlah</div><div class="self-center">:</div><div class="font-bold self-center">Rp {{ number_format($selectedAccount->amount, 2, ',', '.') }}</div>
                                <div class="self-center">Terbilang</div><div class="self-center">:</div>{{ ucwords(\App\Services\SavingOperationService::terbilang($selectedAccount->amount)) }} Rupiah</div>
                            </div>

                            <div class="mt-10 ml-16 mr-16 pb-12">
                                <table class="w-full text-center text-xs" style="border-collapse: separate; border-spacing: 12px 0; width: calc(100% + 24px); margin-left: -12px;">
                                    <colgroup>
                                        <col style="width:20%; padding: 0 6px;">
                                        <col style="width:20%; padding: 0 6px;">
                                        <col style="width:20%; padding: 0 6px;">
                                        <col style="width:20%; padding: 0 6px;">
                                        <col style="width:20%; padding: 0 6px;">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th style="padding: 0 8px 6px 8px;  border-bottom: 1px solid #64748b;">Jangka Waktu</th>
                                            <th style="padding: 0 8px 6px 8px;  border-bottom: 1px solid #64748b;">Realisasi</th>
                                            <th style="padding: 0 8px 6px 8px; border-bottom: 1px solid #64748b;">Jatuh Tempo</th>
                                            <th style="padding: 0 8px 6px 8px; border-bottom: 1px solid #64748b;">Rate</th>
                                            <th style="padding: 0 8px 6px 8px; border-bottom: 1px solid #64748b;">Jasa per Bulan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="padding: 8px 8px 6px 8px; border-bottom: 1px solid #64748b;">{{ $selectedAccount->tenor }} Bulan</td>
                                            <td style="padding: 8px 8px 6px 8px; border-bottom: 1px solid #64748b;">{{ $selectedAccount->placement_date->format('d-m-Y') }}</td>
                                            <td style="padding: 8px 8px 6px 8px; border-bottom: 1px solid #64748b;">{{ $selectedAccount->maturity_date->format('d-m-Y') }}</td>
                                            <td style="padding: 8px 8px 6px 8px; border-bottom: 1px solid #64748b;">{{ format_percent($selectedAccount->interest_rate) }}</td>
                                            @php
                                                $monthlyInterest = ($selectedAccount->amount * ($selectedAccount->interest_rate / 100)) / 12;
                                            @endphp
                                            <td style="padding: 8px 8px 6px 8px; border-bottom: 1px solid #64748b;">{{ number_format($monthlyInterest, 2, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTUAL PRINTABLE CONTENT -->
            <div class="hidden print:block bilyet-print-area">
                <p class="nomor_bilyet">Nomor : {{ $selectedAccount->bilyet?->kode_bilyet ?? '-' }}</p>

                <div class="container-print">
                    <div>Nama</div>
                    <div>:</div>
                    <div style="text-transform: uppercase;">{{ $selectedAccount->cif->name }}</div>
                    
                    <div>Alamat</div>
                    <div>:</div>
                    <div class="alamat">{{ $selectedAccount->cif->alamat_lengkap }}</div>
                    
                    <div>Uang Sejumlah</div>
                    <div>:</div>
                    <div>Rp {{ number_format($selectedAccount->amount, 2, ',', '.') }}</div>
                    
                    <div>Terbilang</div>
                    <div>:</div>
                    <div>{{ ucwords(\App\Services\SavingOperationService::terbilang($selectedAccount->amount)) }} Rupiah</div>
                </div>

                <div class="container-isi">
                    <div class="head-isi">Jangka Waktu</div>
                    <div class="head-isi">Realisasi</div>
                    <div class="head-isi">Jatuh Tempo</div>
                    <div class="head-isi">Rate</div>
                    <div class="head-isi">Jasa Perbulan</div>
                    
                    <div class="head-isi">{{ $selectedAccount->tenor ?? '-' }} Bulan</div>
                    <div class="head-isi">{{ $selectedAccount->placement_date ? $selectedAccount->placement_date->format('d-m-Y') : '-' }}</div>
                    <div class="head-isi">{{ $selectedAccount->maturity_date ? $selectedAccount->maturity_date->format('d-m-Y') : '-' }}</div>
                    <div class="head-isi">{{ format_percent($selectedAccount->interest_rate) }}</div>
                    
                    @php
                        // Calculate estimated monthly interest if not stored
                        $monthlyInterest = 0;
                        if($selectedAccount->amount && $selectedAccount->interest_rate) {
                             $monthlyInterest = ($selectedAccount->amount * ($selectedAccount->interest_rate / 100)) / 12;
                        }
                    @endphp
                    <div class="head-isi">{{ number_format($monthlyInterest, 2, ',', '.') }}</div>
                </div>

                <div class="container-ttd">
                    <div class="head-isi-ttd-1" style="margin-top:8px">
                        Bekasi, {{ $selectedAccount->placement_date ? \Carbon\Carbon::parse($selectedAccount->placement_date)->locale('id')->translatedFormat('d F Y') : now()->locale('id')->translatedFormat('d F Y') }}
                    </div>
                    <div class="signature-group">
                        <div class="signature-block">
                            <div class="head-isi-ttd-3 signature-name">Vivian Dyah S</div>
                            <div class="head-isi-ttd-3 signature-position">Bendahara</div>
                        </div>
                        <div class="signature-block" style="margin-left: 40px">
                            <div class="head-isi-ttd-1 signature-name">Samuel Timothy</div>
                            <div class="head-isi-ttd-1 signature-position">Ketua</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    @media print {
        /* Hide sidebar column and other UI chrome elements entirely */
        div.shrink-0, .w-72, [class*="w-72"], header, .header, x-header, .no-print, nav, #sidebar-nav, .actions, .print\:hidden {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        .p-10 { padding: 0 !important; }
        .hidden.print\:block {
            display: block !important;
        }
        html, body, main, .min-h-screen, .flex, .bg-surface, .bilyet-print-area { 
            margin: 0; 
            padding: 0; 
            min-height: 0 !important;
            height: auto !important;
            width: 100% !important;
            max-width: 100% !important;
            background-color: transparent !important; 
            background: transparent !important;
            -webkit-print-color-adjust: exact !important; 
            print-color-adjust: exact !important; 
            font-family: 'Times New Roman', Times, serif !important; 
        }
        .bilyet-print-area, .bilyet-print-area * {
            font-family: 'Times New Roman', Times, serif !important;
            color: #000000 !important;
        }
        .bilyet-print-area {
            page-break-inside: avoid !important;
            break-inside: avoid-page !important;
        }
        .bilyet-print-area .container-ttd {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        @page {
            size: 21.5cm 11cm;
            margin: 0.5cm;
        }

        .bilyet-print-area .watermark {
            position: absolute;
            top: 20px;
            left: 20px;
            opacity: 0.1;
            z-index: -1;
        }
        .bilyet-print-area .header-logo {
            position: absolute;
            top: 10px;
            left: 40px; /* Adjusted to fit layout */
            height: 40px;
        }
        .bilyet-print-area .container-print {
            display: grid;
            grid-template-columns: 4fr 1fr 15fr;
            margin-top: 2px;
            padding-top: 4px;
            padding-left: 69.5px;
            margin-bottom: 10px;
        }
        .bilyet-print-area .container-isi {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
        }
        .bilyet-print-area .container-ttd {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            padding: 2px;
            padding-right: 9px;
        }
        .bilyet-print-area .container-print>div {
            padding: 3px;
            font-size: 12px;
            text-align: left;
        }
        .bilyet-print-area .container-isi>div {
            padding: 2px;
            font-size: 12px;
            text-align: left;
        }
        .bilyet-print-area .container-isi>div {
            text-align: center;
        }
        .bilyet-print-area .nomor_bilyet {
            padding-top: 85px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 30px;
            font-size: 12px;
            margin-bottom: 20px !important;
        }
        .bilyet-print-area .head-isi {
            border-bottom: 1px solid black;
            font-size: 12px;
            font-weight: normal;
            text-align: center;
            padding: 5px;
            margin-left: 10px;
            margin-right: 10px;
        }
        .bilyet-print-area .head-isi-ttd {
            font-size: 12px;
            font-weight: normal;
            text-align: left;
            padding: 5px;
            margin-left: 10px;
            margin-right: 10px;
        }
        .bilyet-print-area .head-isi-ttd-2 {
            font-size: 12px;
            font-weight: normal;
            text-align: right;
            margin-top: 20px;
            margin-left: 10px;
            margin-right: 10px;
        }
        .bilyet-print-area .signature-group {
            display: flex;
            margin-top: 30px;
        }
        .bilyet-print-area .signature-block {
            display: flex;
            flex-direction: column;
        }
        .bilyet-print-area .head-isi-ttd-1,
        .bilyet-print-area .head-isi-ttd-3 {
            text-align: right;
            margin: 0;
            padding-left: 90px;
            font-size: 12px;
        }
        .bilyet-print-area .signature-position {
            margin-top: 5px;
        }
    }
</style>
