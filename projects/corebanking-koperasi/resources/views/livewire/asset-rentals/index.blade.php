<div class="p-0">
    <x-header title="Jasa Sewa Rekanan" subtitle="Manajemen kontrak sewa aset dan tagihan bulanan" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <div class="flex items-center space-x-3">
                <select wire:model.live="filterStatus" class="pl-3 pr-10 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 appearance-none shadow-sm focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="ACTIVE">ACTIVE</option>
                    <option value="EXPIRED">EXPIRED</option>
                    <option value="TERMINATED">TERMINATED</option>
                </select>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari kontrak, rekanan, aset..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 w-60 shadow-sm focus:outline-none">
                </div>
                <button wire:click="openContractForm" class="flex items-center space-x-2 bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-sm">add_circle</span>
                    <span>Buat Kontrak Sewa</span>
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

        <!-- Contract Form Slide-in -->
        @if($showContractForm)
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 p-8 animate-in slide-in-from-top-4 duration-300">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide">Buat Kontrak Sewa Baru</h3>
                <button wire:click="$set('showContractForm', false)" class="p-2 hover:bg-slate-100 rounded-xl transition-all">
                    <span class="material-symbols-outlined text-sm text-slate-400">close</span>
                </button>
            </div>
            <form wire:submit.prevent="saveContract">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Aset yang Disewakan <span class="text-rose-500">*</span></label>
                        <select wire:model="asset_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                            <option value="">Pilih Aset...</option>
                            @foreach($availableAssets as $ast)
                                <option value="{{ $ast->id }}">{{ $ast->name }} ({{ $ast->asset_code }})</option>
                            @endforeach
                        </select>
                        @error('asset_id') <span class="text-[9px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Rekanan Penyewa <span class="text-rose-500">*</span></label>
                        <select wire:model="rekanan_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                            <option value="">Pilih Rekanan...</option>
                            @foreach($allRekanan as $rek)
                                <option value="{{ $rek->id }}">{{ $rek->name }}</option>
                            @endforeach
                        </select>
                        @error('rekanan_id') <span class="text-[9px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Biaya Sewa / Bulan (Rp) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">Rp</span>
                            <input wire:model="monthly_rate" type="text" class="w-full pl-10 pr-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                        </div>
                        @error('monthly_rate') <span class="text-[9px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Mulai Kontrak <span class="text-rose-500">*</span></label>
                        <input wire:model="rental_start_date" type="date" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                        @error('rental_start_date') <span class="text-[9px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Akhir Kontrak <span class="text-rose-500">*</span></label>
                        <input wire:model="rental_end_date" type="date" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                        @error('rental_end_date') <span class="text-[9px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widests font-extrabold text-slate-400 ml-1">Jatuh Tempo Tagihan (Tgl) <span class="text-rose-500">*</span></label>
                        <input wire:model="payment_due_day" type="number" min="1" max="28" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all" placeholder="Cth: 5 (tanggal 5 tiap bulan)">
                        @error('payment_due_day') <span class="text-[9px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-3 space-y-2">
                        <label class="text-[10px] uppercase tracking-widests font-extrabold text-slate-400 ml-1">Catatan Kontrak</label>
                        <textarea wire:model="notes" rows="2" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all"></textarea>
                    </div>
                </div>
                <div class="mt-4 p-4 bg-amber-50 border border-amber-100 rounded-2xl flex items-start space-x-3">
                    <span class="material-symbols-outlined text-amber-500 text-sm mt-0.5">info</span>
                    <p class="text-[10px] text-amber-700 font-bold leading-relaxed">Tagihan bulanan akan digenerate otomatis untuk seluruh periode kontrak. Aset akan berubah status menjadi RENTED.</p>
                </div>
                <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-slate-100">
                    <button type="button" wire:click="$set('showContractForm', false)" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all">Batal</button>
                    <button type="submit" class="px-8 py-2.5 bg-slate-900 text-white font-bold text-xs rounded-xl hover:shadow-lg transition-all flex items-center space-x-2">
                        <div wire:loading wire:target="saveContract" class="w-4 h-4 border-2 border-slate-400 border-t-white rounded-full animate-spin"></div>
                        <span>Buat Kontrak & Generate Tagihan</span>
                    </button>
                </div>
            </form>
        </div>
        @endif

        @if($viewMode === 'grid')
        <!-- Contract List -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center w-16">OPSI</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">No. Kontrak</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Aset</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Rekanan (Penyewa)</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widests text-slate-400 uppercase text-right">Sewa/Bulan</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widests text-slate-400 uppercase text-center">Periode Kontrak</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widests text-slate-400 uppercase text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($rentals as $rental)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-6 text-center">
                                <button wire:click="viewRental({{ $rental->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-slate-900 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto">
                                    <span class="material-symbols-outlined text-sm">visibility</span>
                                </button>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-xs font-black text-indigo-600">{{ $rental->contract_no }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-xs font-black text-slate-900">{{ $rental->asset->name }}</p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $rental->asset->asset_code }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-xs font-black text-slate-900">{{ $rental->rekanan->name }}</p>
                                <p class="text-[9px] text-slate-400 font-bold">{{ $rental->rekanan->phone ?? '-' }}</p>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <p class="text-xs font-black text-slate-900">Rp {{ number_format($rental->monthly_rate, 2, ',', '.') }}</p>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <p class="text-[10px] font-bold text-slate-900">{{ $rental->rental_start_date->format('d/m/Y') }}</p>
                                <p class="text-[9px] text-slate-400 font-bold">s/d {{ $rental->rental_end_date->format('d/m/Y') }}</p>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span @class(['px-2 py-0.5 text-[9px] font-black rounded uppercase tracking-widest border',
                                    'bg-emerald-50 text-emerald-600 border-emerald-100' => $rental->status === 'ACTIVE',
                                    'bg-slate-100 text-slate-500 border-slate-200' => $rental->status === 'EXPIRED',
                                    'bg-rose-50 text-rose-600 border-rose-100' => $rental->status === 'TERMINATED'
                                ])>{{ $rental->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-20 text-center">
                                <span class="material-symbols-outlined text-5xl text-slate-200 mb-3">receipt_long</span>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Belum ada kontrak sewa. Klik "Buat Kontrak Sewa" untuk memulai.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rentals->hasPages())
                <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                    {{ $rentals->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </div>

        @else
        <!-- Detail View: Contract + Billings -->
        @if($selectedRental)
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
            <!-- Top Bar -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 px-8 py-6 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <button wire:click="closeView" class="p-2 bg-white hover:bg-slate-200 rounded-xl transition-all shadow-sm border border-slate-200">
                        <span class="material-symbols-outlined text-sm">arrow_back</span>
                    </button>
                    <div>
                        <h2 class="font-extrabold text-sm text-slate-900 uppercase tracking-wide">Kontrak: {{ $selectedRental->contract_no }}</h2>
                        <p class="text-[10px] uppercase font-bold text-slate-500 tracking-widest mt-0.5">{{ $selectedRental->rekanan->name }} • {{ $selectedRental->asset->name }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span @class(['px-4 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-xl border',
                        'bg-emerald-50 text-emerald-600 border-emerald-100' => $selectedRental->status === 'ACTIVE',
                        'bg-slate-100 text-slate-500 border-slate-200' => in_array($selectedRental->status, ['EXPIRED']),
                        'bg-rose-50 text-rose-600 border-rose-100' => $selectedRental->status === 'TERMINATED'
                    ])>{{ $selectedRental->status }}</span>
                    @if($selectedRental->status === 'ACTIVE')
                    <button wire:click="terminateContract({{ $selectedRental->id }})" wire:confirm="Yakin terminasi kontrak ini? Aset akan dikembalikan ke status ACTIVE." class="px-4 py-1.5 bg-rose-600 text-white text-[10px] font-black rounded-xl hover:bg-rose-700 transition-all">
                        Terminasi Kontrak
                    </button>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-12 gap-8">
                <!-- Left: Billing Table -->
                <div class="col-span-12 lg:col-span-8">
                    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="material-symbols-outlined text-slate-900">receipt_long</span>
                                <h4 class="text-xs font-black uppercase tracking-widest text-slate-900">Tagihan Sewa Bulanan</h4>
                            </div>
                            @php
                                $paidCount = $selectedRental->billings->where('status', 'PAID')->count();
                                $totalBillings = $selectedRental->billings->count();
                            @endphp
                            <span class="text-[9px] font-bold text-slate-400 px-3 py-1 bg-slate-100 rounded-lg">{{ $paidCount }} / {{ $totalBillings }} Lunas</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-white border-b border-slate-100">
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Periode</th>
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Jatuh Tempo</th>
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Jumlah</th>
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($selectedRental->billings as $billing)
                                    <tr class="hover:bg-slate-50/50 transition-colors {{ $billing->status === 'PAID' ? 'opacity-60' : '' }}">
                                        <td class="py-4 px-6 text-[10px] font-black text-slate-900">{{ $billing->billing_period }}</td>
                                        <td class="py-4 px-6 text-[10px] font-bold text-slate-600">{{ $billing->due_date->format('d/m/Y') }}</td>
                                        <td class="py-4 px-6 text-[10px] font-black text-slate-900 text-right">Rp {{ number_format($billing->amount, 2, ',', '.') }}</td>
                                        <td class="py-4 px-6 text-center">
                                            <span @class(['px-2 py-0.5 text-[8px] font-black rounded uppercase tracking-widest border',
                                                'bg-emerald-50 text-emerald-600 border-emerald-100' => $billing->status === 'PAID',
                                                'bg-amber-50 text-amber-600 border-amber-100' => $billing->status === 'UNPAID',
                                                'bg-rose-50 text-rose-600 border-rose-100' => $billing->status === 'OVERDUE'
                                            ])>{{ $billing->status }}</span>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            @if($billing->status !== 'PAID')
                                            <button wire:click="openPaymentModal({{ $billing->id }})" class="text-[9px] font-black text-emerald-600 hover:text-emerald-700 underline uppercase tracking-widest">
                                                Tandai Lunas
                                            </button>
                                            @else
                                            <span class="text-[9px] text-slate-400 font-bold">{{ $billing->paid_at?->format('d/m/Y') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right: Contract Summary -->
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    <div class="p-8 bg-slate-900 rounded-[2.5rem] shadow-xl shadow-slate-900/20 text-white relative overflow-hidden">
                        <div class="absolute -right-8 -top-8 w-36 h-36 bg-white/5 rounded-full blur-3xl"></div>
                        <div class="relative z-10">
                            <p class="text-[10px] font-black uppercase tracking-widest text-white/40 mb-1">Sewa Per Bulan</p>
                            <h3 class="text-3xl font-black tracking-tight mb-6">Rp {{ number_format($selectedRental->monthly_rate, 2, ',', '.') }}</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-[10px] text-white/40 font-bold uppercase tracking-widest">Total Kontrak</span>
                                    <span class="text-sm font-black">Rp {{ number_format($selectedRental->billings->sum('amount'), 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[10px] text-white/40 font-bold uppercase tracking-widest">Sudah Dibayar</span>
                                    <span class="text-sm font-black text-emerald-400">Rp {{ number_format($selectedRental->billings->where('status','PAID')->sum('amount'), 2, ',', '.') }}</span>
                                </div>
                                <div class="pt-3 border-t border-white/10 flex justify-between">
                                    <span class="text-[10px] text-white/40 font-bold uppercase tracking-widest">Sisa Tagihan</span>
                                    <span class="text-sm font-black text-amber-400">Rp {{ number_format($selectedRental->billings->where('status','UNPAID')->sum('amount'), 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white rounded-[2rem] shadow-sm border border-slate-200/60">
                        <p class="text-[10px] font-black uppercase tracking-widests text-slate-400 mb-4">Info Kontrak</p>
                        <div class="space-y-3">
                            <div>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Aset</p>
                                <p class="text-xs font-black text-slate-900">{{ $selectedRental->asset->name }}</p>
                                <p class="text-[9px] text-indigo-600 font-bold">{{ $selectedRental->asset->asset_code }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Rekanan</p>
                                <p class="text-xs font-black text-slate-900">{{ $selectedRental->rekanan->name }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Periode</p>
                                <p class="text-xs font-bold text-slate-900">{{ $selectedRental->rental_start_date->format('d/m/Y') }} – {{ $selectedRental->rental_end_date->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">JT Tagihan</p>
                                <p class="text-xs font-bold text-slate-900">Setiap Tgl {{ $selectedRental->payment_due_day }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif

        <!-- Payment Modal -->
        @if($showPaymentModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="$set('showPaymentModal', false)">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 animate-in zoom-in-95 duration-200">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-6">Konfirmasi Pembayaran</h3>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nomor Referensi / Bukti Bayar</label>
                        <input wire:model="payment_reference" type="text" placeholder="No. Transfer, No. Kuitansi, dll..." class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">COA Debit</label>
                        <input wire:model.live.debounce.300ms="payment_debit_coa_search"
                            type="text"
                            list="asset-rental-payment-debit-coas"
                            placeholder="219011 - Titipan Jasa Sewa"
                            class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                        <datalist id="asset-rental-payment-debit-coas">
                            @foreach($paymentDebitCoas as $coa)
                                <option value="{{ $coa->coa_code }} - {{ $coa->name }}"></option>
                            @endforeach
                        </datalist>
                        @error('payment_debit_coa_id') <span class="text-[9px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">COA Kredit</label>
                        <input wire:model.live.debounce.300ms="payment_credit_coa_search"
                            type="text"
                            list="asset-rental-payment-credit-coas"
                            placeholder="417000 - Pendapatan Sewa Aset"
                            class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                        <datalist id="asset-rental-payment-credit-coas">
                            @foreach($paymentCreditCoas as $coa)
                                <option value="{{ $coa->coa_code }} - {{ $coa->name }}"></option>
                            @endforeach
                        </datalist>
                        @error('payment_credit_coa_id') <span class="text-[9px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex space-x-3 mt-6">
                    <button wire:click="$set('showPaymentModal', false)" class="flex-1 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all">Batal</button>
                    <button wire:click="confirmPayment" class="flex-1 py-3 bg-emerald-600 text-white font-bold text-xs rounded-xl hover:bg-emerald-700 transition-all flex items-center justify-center space-x-2">
                        <div wire:loading wire:target="confirmPayment" class="w-4 h-4 border-2 border-emerald-300 border-t-white rounded-full animate-spin"></div>
                        <span>Konfirmasi Lunas</span>
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
