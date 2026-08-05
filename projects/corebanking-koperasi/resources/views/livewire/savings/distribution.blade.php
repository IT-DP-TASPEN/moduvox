<div class="p-0">
    <x-header title="Distribusi Dana Simpanan" subtitle="Distribusi dana Kredit (CR) atau Debit (DR) massal via unggah file CSV per produk" :user="$user" :role="$role">
        <x-slot:actions>
            <div class="flex items-center space-x-2 bg-slate-100/50 p-1 rounded-2xl border border-slate-200/50 backdrop-blur-xl">
                <button wire:click="$set('activeTab', 'form')" class="px-6 py-2 rounded-xl text-xs font-bold transition-all duration-300 {{ $activeTab === 'form' ? 'bg-white text-slate-900 shadow-sm border border-slate-200/50' : 'text-slate-500 hover:text-slate-700' }}">
                    Form Distribusi
                </button>
                <button wire:click="$set('activeTab', 'history')" class="px-6 py-2 rounded-xl text-xs font-bold transition-all duration-300 {{ $activeTab === 'history' ? 'bg-white text-slate-900 shadow-sm border border-slate-200/50' : 'text-slate-500 hover:text-slate-700' }}">
                    Riwayat Distribusi
                </button>
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-10">
        <!-- Toast Alerts -->
        @if (session()->has('success'))
        <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-[2rem] border border-emerald-100 flex items-center mb-10 animate-fade-in shadow-sm">
            <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center mr-4 shrink-0">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            </div>
            <p class="font-bold text-sm">{{ session('success') }}</p>
        </div>
        @endif

        @if (session()->has('error'))
        <div class="bg-rose-50 text-rose-700 px-6 py-4 rounded-[2rem] border border-rose-100 flex items-center mb-10 animate-fade-in shadow-sm">
            <div class="w-10 h-10 rounded-2xl bg-rose-100 flex items-center justify-center mr-4 shrink-0">
                <span class="material-symbols-outlined text-rose-600">error</span>
            </div>
            <p class="font-bold text-sm">{{ session('error') }}</p>
        </div>
        @endif

        @if($activeTab === 'form')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Parameter Settings Panel -->
            <div class="col-span-1 space-y-6">
                <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-8 space-y-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-1">Parameter</h3>
                                <p class="text-xs text-slate-500 font-medium">Tentukan tipe, target produk, dan file</p>
                            </div>
                            <button type="button" wire:click="downloadTemplate" class="text-[10px] text-slate-500 hover:text-slate-900 font-bold flex items-center gap-1 bg-slate-50 border border-slate-200 py-1.5 px-3 rounded-xl transition-all">
                                <span class="material-symbols-outlined text-xs">download</span>
                                Template CSV
                            </button>
                        </div>

                        <!-- Type Toggle -->
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Jenis Distribusi</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" wire:click="$set('distribution_type', 'CREDIT')"
                                    class="py-3.5 px-4 rounded-2xl text-xs font-black uppercase tracking-wider transition-all border flex items-center justify-center space-x-2 {{ $distribution_type === 'CREDIT' ? 'bg-emerald-50 text-emerald-700 border-emerald-300 shadow-sm' : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100' }}">
                                    <span class="material-symbols-outlined text-sm">add_circle</span>
                                    <span>Kredit (CR)</span>
                                </button>
                                <button type="button" wire:click="$set('distribution_type', 'DEBIT')"
                                    class="py-3.5 px-4 rounded-2xl text-xs font-black uppercase tracking-wider transition-all border flex items-center justify-center space-x-2 {{ $distribution_type === 'DEBIT' ? 'bg-rose-50 text-rose-700 border-rose-300 shadow-sm' : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100' }}">
                                    <span class="material-symbols-outlined text-sm">remove_circle</span>
                                    <span>Debit (DR)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Channel Selection -->
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Jalur Transaksi (Channel)</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">account_balance</span>
                                <select wire:model.live="channel" class="w-full pl-12 pr-10 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all appearance-none cursor-pointer">
                                    <option value="CASH">Tunai (Kas)</option>
                                    <option value="ABA">Antar Bank Aktiva (ABA)</option>
                                    <option value="COA">COA Manual</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
                            </div>
                            @error('channel') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <!-- Dropdown Pemilihan Sub-Akun COA (Kas / ABA) -->
                        @if($channel === 'ABA' && $abaCoas->count() > 1)
                        <div class="space-y-2 animate-in fade-in slide-in-from-top-4 duration-300">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Sub-Akun Bank (ABA)</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">account_balance_wallet</span>
                                <select wire:model="bank_coa_id" class="w-full pl-12 pr-10 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all appearance-none cursor-pointer">
                                    <option value="">-- Pilih Bank --</option>
                                    @foreach($abaCoas as $coa)
                                        <option value="{{ $coa->id }}">{{ $coa->name }} ({{ $coa->coa_code }})</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
                            </div>
                            @error('bank_coa_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        @elseif($channel === 'CASH' && $cashCoas->count() > 1)
                        <div class="space-y-2 animate-in fade-in slide-in-from-top-4 duration-300">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Sub-Akun Kas</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">payments</span>
                                <select wire:model="cash_coa_id" class="w-full pl-12 pr-10 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all appearance-none cursor-pointer">
                                    <option value="">-- Pilih Kas --</option>
                                    @foreach($cashCoas as $coa)
                                        <option value="{{ $coa->id }}">{{ $coa->name }} ({{ $coa->coa_code }})</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
                            </div>
                            @error('cash_coa_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        @elseif($channel === 'COA')
                        <div class="space-y-2 animate-in fade-in slide-in-from-top-4 duration-300">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">COA Transaksi <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                                <input wire:model.live.debounce.300ms="coaSearch" type="text"
                                    list="distribution-coa-options"
                                    class="w-full pl-12 pr-5 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all"
                                    placeholder="Cari lalu pilih COA...">
                                <datalist id="distribution-coa-options">
                                    @foreach($allCoas as $coa)
                                        <option value="{{ $coa->coa_code }} - {{ $coa->name }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">Menampilkan maksimal 50 hasil teratas.</p>
                            @error('coa_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        @endif
                        <!-- Product Selection -->
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Produk Tabungan Target</label>
                            <div class="relative">
                                <select wire:model.live="saving_product_id" class="w-full pl-5 pr-10 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all appearance-none cursor-pointer">
                                    <option value="">Pilih Produk Tabungan...</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
                            </div>
                            @error('saving_product_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- File Upload (CSV) -->
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">File CSV Distribusi</label>
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
                                <span class="text-[9px] text-slate-400 font-medium mt-1">Maksimal 2 MB (Format .csv)</span>
                            </div>
                            @error('importFile') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Effective Date -->
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Efektif</label>
                            <input wire:model="effective_date" type="date" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-bold text-xs text-slate-900">
                            @error('effective_date') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Keterangan Jurnal</label>
                            <textarea wire:model="description" placeholder="Misal: Pembagian Jasa Produksi/Bonus Tabungan" rows="3" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-bold text-xs text-slate-900"></textarea>
                            @error('description') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Submit Preview Button -->
                        <button wire:click="previewDistribution" wire:loading.attr="disabled" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-4 px-8 rounded-2xl transition-all duration-300 shadow-lg shadow-slate-900/20 flex items-center justify-center space-x-2 disabled:opacity-50">
                            <span wire:loading.remove wire:target="previewDistribution" class="material-symbols-outlined text-xl">query_stats</span>
                            <span wire:loading wire:target="previewDistribution" class="material-symbols-outlined text-xl animate-spin">refresh</span>
                            <span>Preview & Validasi</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview Results Panel -->
            <div class="col-span-1 lg:col-span-2">
                @if($showPreview && !empty($preview))
                <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-sm overflow-hidden animate-in slide-in-from-bottom-6 duration-300">
                    <div class="p-8 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center bg-slate-50/50 gap-4">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">Preview Dampak Distribusi</h2>
                            <p class="text-xs font-medium text-slate-500 mt-1">Produk: <span class="text-slate-800 font-bold">{{ $preview['product_name'] }}</span></p>
                        </div>
                        <div class="flex items-center space-x-3">
                            @if($preview['has_warnings'])
                                <button type="button" disabled class="bg-slate-300 text-slate-500 font-bold py-3 px-6 rounded-xl cursor-not-allowed text-xs flex items-center space-x-2">
                                    <span class="material-symbols-outlined text-sm">block</span>
                                    <span>Ada Error / Saldo Kurang</span>
                                </button>
                            @else
                                @php
                                    $pendingCount = collect($preview['accounts'])->where('account_status', 'PENDING')->count();
                                @endphp
                                <button wire:click="submitDistribution" onclick="return confirm('{{ $pendingCount > 0 ? 'Distribusi ini akan mengaktifkan ' . $pendingCount . ' rekening PENDING. ' : '' }}Apakah Anda yakin ingin melanjutkan?')" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-md flex items-center space-x-2 text-xs">
                                    <span class="material-symbols-outlined text-sm">send</span>
                                    <span>Eksekusi / Ajukan</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- COA Mapping Info -->
                    <div class="px-8 py-4 bg-slate-100/50 border-b border-slate-100 flex items-center space-x-3">
                        <span class="material-symbols-outlined text-slate-400 text-sm">account_tree</span>
                        <p class="text-xs text-slate-600 font-medium">
                            @if($distribution_type === 'CREDIT')
                                <strong>COA Lawan (Debet) otomatis [{{ $channel }}]:</strong> 
                                <span class="font-mono text-slate-950 font-bold bg-white px-2 py-0.5 rounded border border-slate-200 ml-1">
                                    {{ $preview['counterpart_coa_code'] }} - {{ $preview['counterpart_coa_name'] }}
                                </span>
                            @else
                                <strong>COA Lawan (Kredit) otomatis [{{ $channel }}]:</strong> 
                                <span class="font-mono text-slate-950 font-bold bg-white px-2 py-0.5 rounded border border-slate-200 ml-1">
                                    {{ $preview['counterpart_coa_code'] }} - {{ $preview['counterpart_coa_name'] }}
                                </span>
                            @endif
                        </p>
                    </div>

                    <!-- Metrics -->
                    <div class="grid grid-cols-2 border-b border-slate-100">
                        <div class="p-6 text-center border-r border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Jumlah Rekening dalam CSV</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format($preview['account_count']) }} Rekening</p>
                        </div>
                        <div class="p-6 text-center">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Dana Distribusi</p>
                            <p class="text-2xl font-black text-slate-900 text-emerald-600">Rp {{ number_format($preview['total_amount'], 2, ',', '.') }}</p>
                        </div>
                    </div>

                    <!-- Warning Alert Banner -->
                    @if($preview['has_warnings'])
                    <div class="bg-rose-50 border-b border-rose-100 p-6 flex items-start space-x-4">
                        <div class="w-8 h-8 rounded-xl bg-rose-100 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-rose-600 text-sm">warning</span>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-xs text-rose-800 uppercase tracking-wide">Peringatan Validasi Rekening / Saldo!</h4>
                            <p class="text-slate-600 text-xs leading-relaxed">
                                Terdapat beberapa data rekening dalam CSV yang tidak valid atau memiliki saldo kurang / berstatus tidak kompatibel. Silakan perbaiki file CSV Anda.
                            </p>
                            <ul class="list-disc list-inside text-[10px] text-rose-700 font-bold mt-2 space-y-1">
                                @foreach(array_slice($preview['warnings'], 0, 5) as $w)
                                    <li>{{ $w }}</li>
                                @endforeach
                                @if(count($preview['warnings']) > 5)
                                    <li>... dan {{ count($preview['warnings']) - 5 }} peringatan lainnya.</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    @else
                    @php
                        $pendingActivationCount = collect($preview['accounts'])->where('account_status', 'PENDING')->count();
                    @endphp
                    @if($pendingActivationCount > 0 && $distribution_type === 'CREDIT')
                    <div class="bg-amber-50 border-b border-amber-100 p-6 flex items-start space-x-4">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-amber-600 text-sm">manage_accounts</span>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-xs text-amber-800 uppercase tracking-wide">Info: Pengaktifan Rekening Massal</h4>
                            <p class="text-amber-700 text-xs leading-relaxed">
                                Ditemukan <strong>{{ $pendingActivationCount }} rekening berstatus PENDING</strong> dalam file CSV. 
                                Rekening-rekening ini akan otomatis <strong>diaktifkan (ACTIVE)</strong> setelah distribusi KREDIT berhasil dieksekusi.
                            </p>
                        </div>
                    </div>
                    @endif
                    @endif

                    <!-- Account list table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-400 text-[10px] uppercase tracking-[0.2em] font-extrabold">
                                    <th class="px-6 py-4">No. Rekening</th>
                                    <th class="px-6 py-4">Nama Anggota</th>
                                    <th class="px-6 py-4 text-right">Saldo Saat Ini</th>
                                    <th class="px-6 py-4 text-right">Nominal</th>
                                    <th class="px-6 py-4">Keterangan Baris</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($preview['accounts'] as $acc)
                                <tr class="hover:bg-slate-50/30 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-xs text-slate-800 tracking-wide">{{ $acc['account_no'] }}</td>
                                    <td class="px-6 py-4 font-bold text-xs text-slate-900 uppercase">{{ $acc['cif_name'] }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-xs text-slate-900">Rp {{ number_format($acc['balance'], 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-right font-black text-xs {{ $distribution_type === 'CREDIT' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $distribution_type === 'CREDIT' ? '+' : '-' }} Rp {{ number_format($acc['amount'], 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-slate-500">{{ $acc['note'] ?: '-' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if($acc['insufficient'])
                                            <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider">{{ $acc['status_text'] }}</span>
                                        @elseif(($acc['account_status'] ?? 'ACTIVE') === 'PENDING')
                                            <span class="bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider">PENDING → Aktifkan</span>
                                        @elseif($acc['status_text'] !== 'OK')
                                            <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider">{{ $acc['status_text'] }}</span>
                                        @else
                                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider">OK</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach

                                @if($preview['more_count'] > 0)
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center font-bold text-xs text-slate-400 bg-slate-50/20">
                                        + {{ number_format($preview['more_count']) }} rekening lainnya dalam file CSV...
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <div class="h-full bg-white rounded-[2rem] border border-slate-200/60 shadow-sm flex flex-col items-center justify-center p-12 text-center min-h-[480px]">
                    <div class="w-20 h-20 bg-slate-50 rounded-2xl flex items-center justify-center mb-6 border border-slate-100">
                        <span class="material-symbols-outlined text-4xl text-slate-400">query_stats</span>
                    </div>
                    <h3 class="text-base font-black text-slate-900 mb-2">Menunggu Parameter & Unggahan File</h3>
                    <p class="text-slate-500 font-medium text-xs max-w-sm leading-relaxed mb-6">
                        Lengkapi parameter di sebelah kiri, kemudian unggah file CSV transaksi Anda. Klik "Preview & Validasi" untuk memproses data.
                    </p>
                    <button type="button" wire:click="downloadTemplate" class="inline-flex items-center space-x-2 text-slate-600 hover:text-slate-900 border border-slate-200 px-5 py-3 rounded-xl font-bold text-xs bg-slate-50 hover:bg-slate-100 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-sm">download</span>
                        <span>Unduh Contoh Template CSV</span>
                    </button>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($activeTab === 'history')
        <!-- History Tab Content -->
        <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-lg font-black text-slate-900">Riwayat Distribusi Dana</h2>
                <p class="text-xs font-medium text-slate-500 mt-1">Daftar eksekusi distribusi kredit/debit massal</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200 text-slate-400 text-[10px] uppercase tracking-[0.2em] font-extrabold">
                            <th class="px-8 py-5">No. Distribusi / Tgl</th>
                            <th class="px-6 py-5">Tipe</th>
                            <th class="px-6 py-5">Produk Tabungan</th>
                            <th class="px-6 py-5">COA Lawan</th>
                            <th class="px-6 py-5 text-center">Rekening</th>
                            <th class="px-6 py-5 text-right">Total Dana</th>
                            <th class="px-6 py-5 text-center">Status</th>
                            <th class="px-8 py-5">Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($histories as $history)
                        <tr class="hover:bg-slate-50/30 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex flex-col">
                                    <span class="font-mono font-bold text-xs text-slate-800 tracking-wide mb-1">{{ $history->distribution_no }}</span>
                                    <span class="text-[9px] text-slate-400 font-bold">{{ $history->effective_date->format('d M Y') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                 <div class="flex flex-col gap-1.5 items-start">
                                     @if($history->distribution_type === 'CREDIT')
                                         <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase tracking-wider">Kredit (CR)</span>
                                     @else
                                         <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase tracking-wider">Debit (DR)</span>
                                     @endif
                                     <span class="bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider">{{ $history->channel ?? 'CASH' }}</span>
                                 </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-bold text-xs text-slate-800">{{ $history->product?->name ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-col">
                                    <span class="font-mono text-xs text-slate-700 font-bold">{{ $history->counterpartCoa?->coa_code }}</span>
                                    <span class="text-[9px] text-slate-400 font-bold uppercase">{{ $history->counterpartCoa?->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="bg-slate-100 text-slate-700 font-bold px-2.5 py-1 rounded-lg text-[10px]">{{ $history->account_count }} Rekening</span>
                            </td>
                            <td class="px-6 py-5 text-right font-black text-xs text-slate-900">
                                Rp {{ number_format($history->total_amount, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="px-2.5 py-1 text-[9px] font-bold border rounded-lg uppercase tracking-wider {{ $history->status_badge }}">
                                    {{ $history->status }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <span class="font-bold text-xs text-slate-600 uppercase">{{ $history->creator?->name ?? '-' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-4 border border-slate-100">
                                        <span class="material-symbols-outlined text-3xl text-slate-400">history</span>
                                    </div>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Belum ada riwayat distribusi</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($histories->hasPages())
            <div class="px-8 py-4 bg-slate-50/50 border-t border-slate-100">
                {{ $histories->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
