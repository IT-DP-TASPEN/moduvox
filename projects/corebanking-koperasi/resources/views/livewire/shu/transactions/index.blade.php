<div class="p-6 sm:p-10 space-y-8 min-h-screen font-sans">
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Transaksi SHU</h1>
            <p class="text-slate-500 font-medium mt-2 text-sm">Kalkulasi dan distribusi Sisa Hasil Usaha ke rekening anggota</p>
        </div>
        <div class="flex items-center space-x-2 bg-slate-100/50 p-1 rounded-2xl border border-slate-200/50 backdrop-blur-xl">
            <button wire:click="$set('activeTab', 'calculator')" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 {{ $activeTab === 'calculator' ? 'bg-white text-emerald-600 shadow-sm border border-slate-200/50' : 'text-slate-500 hover:text-slate-700' }}">
                Kalkulator
            </button>
            <button wire:click="$set('activeTab', 'history')" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 {{ $activeTab === 'history' ? 'bg-white text-emerald-600 shadow-sm border border-slate-200/50' : 'text-slate-500 hover:text-slate-700' }}">
                Riwayat
            </button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-6 py-4 rounded-2xl flex items-center shadow-sm">
            <span class="material-symbols-outlined mr-3 text-emerald-500">check_circle</span>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-600 px-6 py-4 rounded-2xl flex items-center shadow-sm">
            <span class="material-symbols-outlined mr-3 text-rose-500">error</span>
            <span class="font-bold text-sm">{{ session('error') }}</span>
        </div>
    @endif

    @if($activeTab === 'calculator')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Settings Panel -->
        <div class="col-span-1 space-y-6">
            <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/20 overflow-hidden relative">
                <div class="p-8 space-y-6 relative z-10">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-1">Parameter SHU</h3>
                        <p class="text-xs text-slate-500 font-medium">Atur nominal dan kriteria persentase</p>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Periode SHU</label>
                            <input wire:model="periode" type="text" placeholder="Misal: Tahun 2026" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-900/5 focus:border-emerald-500 transition-all font-bold text-sm text-slate-900">
                            @error('periode') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Total Laba (Rp)</label>
                            <input wire:model="total_laba" type="number" placeholder="Contoh: 100000000" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-900/5 focus:border-emerald-500 transition-all font-bold text-sm text-slate-900">
                            @error('total_laba') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1 mb-2 block">Persentase Pembagian (%)</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-slate-500 ml-1">Pemegang Saham</label>
                                    <input wire:model="persen_saham" type="number" step="0.01" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-emerald-900/5 focus:border-emerald-500 transition-all font-bold text-sm text-slate-900 text-center">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-slate-500 ml-1">Pengawas</label>
                                    <input wire:model="persen_pengawas" type="number" step="0.01" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-emerald-900/5 focus:border-emerald-500 transition-all font-bold text-sm text-slate-900 text-center">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-slate-500 ml-1">Pengurus</label>
                                    <input wire:model="persen_pengurus" type="number" step="0.01" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-emerald-900/5 focus:border-emerald-500 transition-all font-bold text-sm text-slate-900 text-center">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-slate-500 ml-1">Anggota</label>
                                    <input wire:model="persen_anggota" type="number" step="0.01" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-emerald-900/5 focus:border-emerald-500 transition-all font-bold text-sm text-slate-900 text-center">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button wire:click="calculate" wire:loading.attr="disabled" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-4 px-8 rounded-2xl transition-all duration-300 shadow-lg shadow-slate-900/20 flex items-center justify-center space-x-2 disabled:opacity-50">
                        <span wire:loading.remove wire:target="calculate" class="material-symbols-outlined text-xl">calculate</span>
                        <span wire:loading wire:target="calculate" class="material-symbols-outlined text-xl animate-spin">refresh</span>
                        <span>Hitung Kalkulasi</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Result Panel -->
        <div class="col-span-1 lg:col-span-2">
            @if(!empty($preview))
            <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/20 overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-emerald-50 to-transparent">
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Hasil Kalkulasi</h2>
                        <p class="text-sm font-medium text-slate-500 mt-1">Periode: {{ $periode }} | Laba: Rp {{ number_format($totalCalculatedLaba, 2, ',', '.') }}</p>
                    </div>
                    <button wire:click="distribute" wire:loading.attr="disabled" onclick="return confirm('Apakah Anda yakin ingin mengeksekusi pencairan SHU ini ke rekening anggota?')" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg shadow-emerald-500/30 flex items-center space-x-2">
                        <span class="material-symbols-outlined text-sm">payments</span>
                        <span>Distribusikan</span>
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 text-[10px] uppercase tracking-[0.2em] font-extrabold">
                                <th class="px-6 py-4">Kriteria</th>
                                <th class="px-6 py-4 text-center">%</th>
                                <th class="px-6 py-4 text-right">Laba</th>
                                <th class="px-6 py-4 text-right">SHU (Rp)</th>
                                <th class="px-6 py-4 text-center">Jumlah</th>
                                <th class="px-6 py-4 text-right">Per Orang (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($preview as $index => $row)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-sm text-slate-900">{{ ucwords(strtolower($row['kriteria'])) }}</td>
                                <td class="px-6 py-4 text-center font-bold text-sm text-slate-600">{{ format_percent($row['persentase']) }}</td>
                                
                                @if($index === 0)
                                <td class="px-6 py-4 text-right font-black text-sm text-slate-900" rowspan="4" style="vertical-align: middle; border-left: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; background: #f8fafc;">
                                    {{ number_format($totalCalculatedLaba, 2, ',', '.') }}
                                </td>
                                @endif

                                <td class="px-6 py-4 text-right font-bold text-sm text-slate-900">{{ number_format($row['shu'], 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg text-xs font-bold">{{ $row['jumlah_orang'] }} Orang</span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-sm text-emerald-600">{{ number_format($row['per_orang'], 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                            <tr class="bg-slate-50 font-black">
                                <td class="px-6 py-4 text-sm text-slate-900 uppercase">Total</td>
                                <td class="px-6 py-4 text-center text-sm text-slate-900">100%</td>
                                <td class="px-6 py-4"></td>
                                <td class="px-6 py-4 text-right text-sm text-slate-900">{{ number_format($totalCalculatedLaba, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center text-sm text-slate-900">{{ $totalOrang }} Orang</td>
                                <td class="px-6 py-4"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="h-full bg-white rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/20 flex flex-col items-center justify-center p-12 text-center min-h-[400px]">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-5xl text-slate-300">calculate</span>
                </div>
                <h3 class="text-lg font-black text-slate-900 mb-2">Belum Ada Kalkulasi</h3>
                <p class="text-slate-500 font-medium max-w-sm">Masukkan periode, total laba, dan persentase di panel samping lalu klik "Hitung Kalkulasi" untuk melihat rincian pembagian.</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    @if($activeTab === 'history')
    <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/20 overflow-hidden">
        <div class="p-8 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-transparent">
            <h2 class="text-xl font-black text-slate-900">Riwayat Distribusi</h2>
            <p class="text-sm font-medium text-slate-500 mt-1">Daftar SHU yang telah didistribusikan ke anggota</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 text-[10px] uppercase tracking-[0.2em] font-extrabold">
                        <th class="px-8 py-6">ID / Periode</th>
                        <th class="px-6 py-6 text-right">Total Laba</th>
                        <th class="px-6 py-6 text-center">Status</th>
                        <th class="px-8 py-6 text-right">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($histories as $history)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-900 text-sm mb-1">{{ $history->periode }}</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">#SHU-{{ str_pad($history->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-6 text-right font-black text-sm text-slate-900">
                            Rp {{ number_format($history->total_laba, 2, ',', '.') }}
                        </td>
                        <td class="px-6 py-6 text-center">
                            @if($history->status === 'DISTRIBUTED')
                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg text-[10px] font-bold w-fit uppercase tracking-wider">Terdestribusi</span>
                            @else
                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-lg text-[10px] font-bold w-fit uppercase tracking-wider">{{ $history->status }}</span>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-right font-bold text-sm text-slate-500">
                            {{ $history->created_at->format('d M Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-3xl text-slate-400">history</span>
                                </div>
                                <span class="text-sm font-bold text-slate-400">Belum ada riwayat distribusi</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-6 border-t border-slate-100 bg-slate-50/50">
            {{ $histories->links('components.table-pagination') }}
        </div>
    </div>
    @endif
</div>
