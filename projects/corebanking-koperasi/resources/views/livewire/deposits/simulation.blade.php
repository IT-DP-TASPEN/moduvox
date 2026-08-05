<div class="p-0">
    <x-header title="Simulasi Simpanan Berjangka" subtitle="Hitung proyeksi bunga dan jadwal investasi simpanan berjangka" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <a href="{{ route('deposits.inquiry') }}"
                class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all"
                wire:navigate>
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Kembali ke Daftar</span>
            </a>
        </x-slot>
    </x-header>

    <div class="p-10 print:p-0 print:m-0">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Input Section -->
            <div class="lg:col-span-1 print:hidden">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col h-full">
                    <form wire:submit.prevent="calculate" class="flex flex-col flex-grow">
                        <div class="p-8 bg-white space-y-8 flex-grow">
                            <!-- SECTION: Parameter Simulasi -->
                            <div>
                                <div class="border-b border-slate-200 pb-2 mb-6">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                                        <span class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">calculate</span>
                                        Parameter Simulasi
                                    </p>
                                </div>

                                <div class="space-y-6">
                                    <!-- Product Selection -->
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Produk Simpanan Berjangka <span class="text-rose-500">*</span></label>
                                        <select wire:model.live="deposit_product_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            <option value="">Pilih Produk...</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }} (Maks. {{ format_percent($product->max_interest_rate) }})</option>
                                            @endforeach
                                        </select>
                                        @error('deposit_product_id') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Placement Date -->
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Penempatan <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <input type="date" wire:model="placement_date" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900">
                                        </div>
                                        @error('placement_date') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Principal Amount -->
                                    <div class="space-y-2" x-data="{ 
                                        display: '',
                                        raw: @entangle('principal'),
                                        digits(v) { let s = (v ?? '').toString(); if (/^\d+\.\d{1,2}$/.test(s)) s = s.split('.')[0]; return s.replace(/\D/g, ''); },
                                        format(v) { return this.digits(v).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
                                        init() { this.display = this.format(this.raw || 0); this.$watch('raw', v => this.display = this.format(v || 0)); }
                                    }">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nominal Penempatan <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</span>
                                            <input type="text" x-model="display" @input="display = format($event.target.value); raw = digits($event.target.value)"
                                                   class="w-full pl-12 pr-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900" placeholder="0">
                                        </div>
                                        @error('principal') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Tenor -->
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tenor (Bulan) <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <input type="number" wire:model="tenor" class="w-full pr-16 pl-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900" placeholder="1" min="1">
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Bulan</span>
                                        </div>
                                        @error('tenor') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Interest Rate -->
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Bunga Simpanan Berjangka (%) <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <input type="number" step="0.01" wire:model="interest_rate" class="w-full pr-12 pl-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900" placeholder="5.00">
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">%</span>
                                        </div>
                                        @error('interest_rate') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Interest Calculation Type -->
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Metode Perhitungan Bunga <span class="text-rose-500">*</span></label>
                                        <select wire:model="interest_calculation_type" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            <option value="MONTHLY">Bunga Flat Bulanan (1/12)</option>
                                            <option value="DAILY">Bunga Harian (Aktual Hari/360)</option>
                                        </select>
                                        @error('interest_calculation_type') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Tax Rate -->
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Pajak (%) <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <input type="number" step="0.01" wire:model="tax_rate" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900 pr-10">
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">%</span>
                                        </div>
                                        @error('tax_rate') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-8 py-5 border-t border-slate-100 flex justify-between items-center bg-slate-50/50 mt-auto">
                            <div>
                                @if($errors->any())
                                <div class="flex items-center text-rose-500 animate-fade-in">
                                    <span class="material-symbols-outlined text-sm mr-2">error</span>
                                    <span class="text-xs font-bold">Pastikan input valid.</span>
                                </div>
                                @else
                                <div class="space-y-0.5">
                                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight">Kalkulasi Bunga</h4>
                                </div>
                                @endif
                            </div>
                            <button type="submit"
                                class="px-8 py-3 bg-slate-900 text-white hover:shadow-lg hover:shadow-slate-900/20 font-bold text-xs rounded-xl transition-all active:scale-95 flex items-center">
                                <div wire:loading wire:target="calculate"
                                    class="w-4 h-4 border-2 border-slate-400 border-t-white rounded-full animate-spin mr-2">
                                </div>
                                <span wire:loading.remove wire:target="calculate"
                                    class="material-symbols-outlined text-sm mr-2">refresh</span>
                                <span>Kalkulasi Simulasi</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Section -->
            <div class="lg:col-span-2 print:col-span-3">
                @if($results)
                    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden animate-slide-up h-full flex flex-col print:border-none print:shadow-none print:overflow-visible print:rounded-none">
                        <div class="p-8 border-b border-slate-200 bg-slate-50/50 print:bg-transparent print:p-0 print:border-b-2 print:border-black print:mb-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 border border-emerald-200 flex items-center justify-center text-emerald-600 shadow-sm">
                                        <span class="material-symbols-outlined">payments</span>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Hasil Proyeksi Simpanan Berjangka</h3>
                                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Estimasi berdasarkan suku bunga {{ format_percent($results['rate']) }}</p>
                                    </div>
                                </div>
                                <div class="text-right flex items-center space-x-6">
                                    <div class="hidden lg:block print:hidden">
                                        <button onclick="window.print()" class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                                            <span class="material-symbols-outlined text-[16px]">print</span>
                                            <span>Download PDF</span>
                                        </button>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Maturity Value (Total)</p>
                                        <p class="text-2xl font-black text-slate-900 tracking-tight flex items-end justify-end space-x-1">
                                            <span class="text-xs text-slate-500 mb-1">Rp</span>
                                            <span>{{ number_format($results['total_payout'], 2, ',', '.') }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 flex-grow print:p-0">
                            <div class="grid grid-cols-2 gap-8 mb-8">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-200">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Pokok Penempatan</span>
                                        <span class="text-sm font-black text-slate-900">Rp {{ number_format($results['principal'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-200">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tenor</span>
                                        <span class="text-sm font-black text-slate-900">{{ $results['tenor'] }} Bulan</span>
                                    </div>
                                    <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-200">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tgl Penempatan</span>
                                        <span class="text-sm font-black text-slate-900">{{ \Carbon\Carbon::parse($results['placement_date'])->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                                        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Tgl Jatuh Tempo</span>
                                        <span class="text-sm font-black text-emerald-700">{{ \Carbon\Carbon::parse($results['maturity_date'])->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                                        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Estimasi Bunga Kotor</span>
                                        <span class="text-sm font-black text-emerald-700">Rp {{ number_format($results['gross_interest'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between p-4 bg-rose-50 rounded-2xl border border-rose-100">
                                        <span class="text-[10px] font-bold text-rose-600 uppercase tracking-widest">Potongan Pajak ({{ format_percent($tax_rate) }})</span>
                                        <span class="text-sm font-black text-rose-700">- Rp {{ number_format($results['tax_amount'], 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-slate-900 rounded-[1.5rem] flex items-center justify-between shadow-xl shadow-slate-900/10 mb-8">
                                <div class="flex items-center space-x-4">
                                    <span class="material-symbols-outlined text-emerald-400">stars</span>
                                    <div>
                                        <p class="text-xs font-black text-white uppercase tracking-widest">Bunga Bersih</p>
                                        <p class="text-[10px] text-slate-400 font-medium tracking-tight">Total keuntungan setelah pajak</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xl font-black text-emerald-400">Rp {{ number_format($results['net_interest'], 2, ',', '.') }}</p>
                                </div>
                            </div>

                            <!-- SCHEDULE TABLE -->
                            @if(isset($results['schedule']) && count($results['schedule']) > 0)
                            <div>
                                <div class="border-b border-slate-200 pb-2 mb-4">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                                        <span class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">calendar_month</span>
                                        Jadwal Pembayaran Bunga Bulanan
                                    </p>
                                </div>
                                
                                <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm print:border-none print:shadow-none print:overflow-visible print:rounded-none">
                                    <div class="max-h-64 overflow-y-auto print:max-h-none print:overflow-visible">
                                        <table class="w-full text-left border-collapse">
                                            <thead class="bg-slate-50 border-b border-slate-200 sticky top-0 z-10 print:static print:z-auto">
                                                <tr class="print:break-inside-avoid">
                                                    <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Bulan</th>
                                                    <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tgl Pembayaran</th>
                                                    <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Hari</th>
                                                    <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Bunga Kotor</th>
                                                    <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Pajak ({{ format_percent($tax_rate) }})</th>
                                                    <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right border-l border-slate-100 bg-emerald-50/30">Bunga Bersih</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach($results['schedule'] as $item)
                                                <tr class="hover:bg-slate-50 transition-colors print:break-inside-avoid">
                                                    <td class="py-3 px-4 text-xs font-bold text-slate-900">{{ $item['month'] }}</td>
                                                    <td class="py-3 px-4 text-xs font-bold text-slate-500">{{ \Carbon\Carbon::parse($item['date'])->format('d M Y') }}</td>
                                                    <td class="py-3 px-4 text-xs font-bold text-slate-400 text-center">{{ $item['days'] ?? '-' }}</td>
                                                    <td class="py-3 px-4 text-xs font-bold text-slate-700 text-right">Rp {{ number_format($item['gross_interest'], 2, ',', '.') }}</td>
                                                    <td class="py-3 px-4 text-xs font-bold text-rose-600 text-right">Rp {{ number_format($item['tax'], 2, ',', '.') }}</td>
                                                    <td class="py-3 px-4 text-xs font-black text-emerald-600 text-right border-l border-emerald-50 bg-emerald-50/10">Rp {{ number_format($item['net_interest'], 2, ',', '.') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="h-full bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col items-center justify-center p-12 text-slate-400 space-y-4">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-2">
                            <span class="material-symbols-outlined text-4xl text-slate-300">analytics</span>
                        </div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-center max-w-sm leading-relaxed">Masukkan parameter di samping untuk memproses proyeksi bunga dan mencetak jadwal</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
