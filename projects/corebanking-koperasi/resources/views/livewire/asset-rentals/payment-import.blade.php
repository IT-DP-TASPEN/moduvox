<div class="p-0">
    <x-header title="Pembayaran Sewa Aset Masal" subtitle="Preview, validasi, dan eksekusi pembayaran tagihan sewa aset massal" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <div class="flex items-center space-x-2 bg-slate-100/50 p-1 rounded-2xl border border-slate-200/50">
                <button wire:click="$set('activeTab', 'form')" class="px-6 py-2 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'form' ? 'bg-white text-slate-900 shadow-sm border border-slate-200/50' : 'text-slate-500 hover:text-slate-700' }}">
                    Import
                </button>
                <button wire:click="$set('activeTab', 'history')" class="px-6 py-2 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'history' ? 'bg-white text-slate-900 shadow-sm border border-slate-200/50' : 'text-slate-500 hover:text-slate-700' }}">
                    History
                </button>
            </div>
        </x-slot>
    </x-header>

    <div class="p-10 space-y-6">
        @if(session('success'))
            <div class="px-5 py-3 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-700 text-xs font-bold flex items-center space-x-2">
                <span class="material-symbols-outlined text-sm">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="px-5 py-3 bg-rose-50 border border-rose-100 rounded-2xl text-rose-700 text-xs font-bold flex items-center space-x-2">
                <span class="material-symbols-outlined text-sm">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($activeTab === 'form')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <div class="col-span-1">
                <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-8 space-y-6">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-1">File Import</h3>
                                <p class="text-xs text-slate-500 font-medium">CSV: contract_no, billing_period, amount, note</p>
                            </div>
                            <button type="button" wire:click="downloadTemplate" class="text-[10px] text-slate-500 hover:text-slate-900 font-bold flex items-center gap-1 bg-slate-50 border border-slate-200 py-1.5 px-3 rounded-xl transition-all">
                                <span class="material-symbols-outlined text-xs">download</span>
                                Template
                            </button>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">File CSV Pembayaran</label>
                            <div class="relative flex flex-col items-center justify-center border border-dashed border-slate-300 bg-slate-50 rounded-2xl p-6 hover:bg-slate-100/50 transition-all cursor-pointer">
                                <input type="file" wire:model="importFile" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <span class="material-symbols-outlined text-slate-400 text-3xl mb-2">upload_file</span>
                                <span class="text-xs font-bold text-slate-700 text-center">
                                    @if($importFile)
                                        {{ $importFile->getClientOriginalName() }}
                                    @else
                                        Pilih atau seret file CSV ke sini
                                    @endif
                                </span>
                                <span class="text-[9px] text-slate-400 font-medium mt-1">Maksimal 2 MB. Referensi pembayaran otomatis dari sistem.</span>
                            </div>
                            @error('importFile') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">COA Otomatis</p>
                            <p class="text-xs font-bold text-slate-700">Debit: 219011 - Titipan Jasa Sewa</p>
                            <p class="text-xs font-bold text-slate-700">Kredit: 417000 - Pendapatan Sewa Aset</p>
                        </div>

                        <button wire:click="previewImport" wire:loading.attr="disabled" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-4 px-8 rounded-2xl transition-all shadow-lg shadow-slate-900/20 flex items-center justify-center space-x-2 disabled:opacity-50">
                            <span wire:loading.remove wire:target="previewImport" class="material-symbols-outlined text-xl">query_stats</span>
                            <span wire:loading wire:target="previewImport" class="material-symbols-outlined text-xl animate-spin">refresh</span>
                            <span>Preview & Validasi</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-span-1 lg:col-span-2">
                @if($showPreview && $preview)
                <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-8 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center bg-slate-50/50 gap-4">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">Preview Pembayaran</h2>
                            <p class="text-xs font-medium text-slate-500 mt-1">Dr {{ $preview['debit_coa'] }} / Cr {{ $preview['credit_coa'] }}</p>
                        </div>
                        @if($preview['has_warnings'])
                            <button type="button" disabled class="bg-slate-300 text-slate-500 font-bold py-3 px-6 rounded-xl cursor-not-allowed text-xs flex items-center space-x-2">
                                <span class="material-symbols-outlined text-sm">block</span>
                                <span>Ada Error</span>
                            </button>
                        @else
                            <button wire:click="submitImport" wire:confirm="Yakin ingin menandai semua tagihan pada preview ini sebagai lunas?" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-md flex items-center space-x-2 text-xs">
                                <span class="material-symbols-outlined text-sm">send</span>
                                <span>Eksekusi / Ajukan</span>
                            </button>
                        @endif
                    </div>

                    <div class="grid grid-cols-3 border-b border-slate-100">
                        <div class="p-6 text-center border-r border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Baris CSV</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format($preview['count']) }}</p>
                        </div>
                        <div class="p-6 text-center border-r border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Valid</p>
                            <p class="text-2xl font-black text-emerald-600">{{ number_format($preview['valid_count']) }}</p>
                        </div>
                        <div class="p-6 text-center">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Dibayar</p>
                            <p class="text-2xl font-black text-slate-900">Rp {{ number_format($preview['total'], 2, ',', '.') }}</p>
                        </div>
                    </div>

                    @if($preview['has_warnings'])
                    <div class="bg-rose-50 border-b border-rose-100 p-6 flex items-start space-x-4">
                        <div class="w-8 h-8 rounded-xl bg-rose-100 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-rose-600 text-sm">warning</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs text-rose-800 uppercase tracking-wide">Peringatan Validasi</h4>
                            <ul class="list-disc list-inside text-[10px] text-rose-700 font-bold mt-2 space-y-1">
                                @foreach(array_slice($preview['warnings'], 0, 6) as $warning)
                                    <li>{{ $warning }}</li>
                                @endforeach
                                @if(count($preview['warnings']) > 6)
                                    <li>... dan {{ count($preview['warnings']) - 6 }} peringatan lainnya.</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-400 text-[10px] uppercase tracking-[0.2em] font-extrabold">
                                    <th class="px-6 py-4">Kontrak</th>
                                    <th class="px-6 py-4">Periode</th>
                                    <th class="px-6 py-4">Aset / Rekanan</th>
                                    <th class="px-6 py-4 text-right">Nominal</th>
                                    <th class="px-6 py-4">Keterangan</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($preview['items'] as $item)
                                <tr class="hover:bg-slate-50/30 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-xs text-slate-800">{{ $item['contract_no'] }}</td>
                                    <td class="px-6 py-4 font-bold text-xs text-slate-900">{{ $item['billing_period'] }}</td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-xs text-slate-900">{{ $item['asset_name'] }}</p>
                                        <p class="text-[10px] text-slate-400 font-bold">{{ $item['rekanan_name'] }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-xs text-slate-900">Rp {{ number_format($item['amount'], 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-xs font-medium text-slate-500">{{ $item['note'] ?: '-' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if($item['status_text'] === 'OK')
                                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider">OK</span>
                                        @else
                                            <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider">{{ $item['status_text'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <div class="h-full bg-white rounded-[2rem] border border-slate-200/60 shadow-sm flex flex-col items-center justify-center p-12 text-center min-h-[480px]">
                    <div class="w-20 h-20 bg-slate-50 rounded-2xl flex items-center justify-center mb-6 border border-slate-100">
                        <span class="material-symbols-outlined text-4xl text-slate-400">query_stats</span>
                    </div>
                    <h3 class="text-base font-black text-slate-900 mb-2">Menunggu File Import</h3>
                    <p class="text-slate-500 font-medium text-xs max-w-sm leading-relaxed mb-6">Unggah file CSV lalu klik Preview & Validasi sebelum eksekusi.</p>
                    <button type="button" wire:click="downloadTemplate" class="inline-flex items-center space-x-2 text-slate-600 hover:text-slate-900 border border-slate-200 px-5 py-3 rounded-xl font-bold text-xs bg-slate-50 hover:bg-slate-100 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-sm">download</span>
                        <span>Unduh Template CSV</span>
                    </button>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($activeTab === 'history')
        <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-lg font-black text-slate-900">History Pembayaran Sewa Aset</h2>
                <p class="text-xs font-medium text-slate-500 mt-1">Tagihan sewa yang sudah berstatus lunas</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-slate-100 text-slate-400 text-[10px] uppercase tracking-[0.2em] font-extrabold">
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Kontrak</th>
                            <th class="px-6 py-4">Periode</th>
                            <th class="px-6 py-4">Aset / Rekanan</th>
                            <th class="px-6 py-4">Referensi</th>
                            <th class="px-6 py-4 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($history as $billing)
                        <tr class="hover:bg-slate-50/30 transition-colors">
                            <td class="px-6 py-4 text-xs font-bold text-slate-700">{{ $billing->paid_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="px-6 py-4 font-mono font-bold text-xs text-slate-800">{{ $billing->rental?->contract_no }}</td>
                            <td class="px-6 py-4 text-xs font-black text-slate-900">{{ $billing->billing_period }}</td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-xs text-slate-900">{{ $billing->rental?->asset?->name ?? '-' }}</p>
                                <p class="text-[10px] text-slate-400 font-bold">{{ $billing->rental?->rekanan?->name ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4 font-mono text-[10px] font-bold text-indigo-600">{{ $billing->payment_reference ?: '-' }}</td>
                            <td class="px-6 py-4 text-right font-black text-xs text-slate-900">Rp {{ number_format($billing->amount, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-20 text-center">
                                <span class="material-symbols-outlined text-5xl text-slate-200 mb-3">history</span>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Belum ada history pembayaran sewa aset.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($history->hasPages())
                <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                    {{ $history->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </div>
        @endif
    </div>
</div>
