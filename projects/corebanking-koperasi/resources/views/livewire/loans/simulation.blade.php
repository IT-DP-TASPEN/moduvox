<div class="p-0">
    <x-header title="Simulasi Pinjaman" subtitle="Hitung estimasi angsuran dan skema amortisasi pinjaman" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <a href="{{ route('loans.inquiry') }}" wire:navigate
                class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Kembali ke Inquiry</span>
            </a>
        </x-slot>
    </x-header>

    <div class="p-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <!-- Input Section -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col h-full ring-1 ring-slate-200/5">
                    <form wire:submit.prevent="calculate" class="flex flex-col flex-grow">
                        <div class="p-8 space-y-8 flex-grow">
                            <div>
                                <div class="border-b border-slate-100 pb-3 mb-6 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-slate-400 text-lg">tune</span>
                                    <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Parameter Simulasi</p>
                                </div>

                                <div class="space-y-6">
                                    <!-- Product -->
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-black text-slate-400 ml-1">Jenis Fasilitas <span class="text-rose-500">*</span></label>
                                        <select wire:model.live="loan_product_id" class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-black text-xs text-slate-700">
                                            <option value="">Pilih Produk...</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Principal -->
                                    <div class="space-y-2" x-data="{ 
                                        display: '',
                                        raw: @entangle('principal_amount'),
                                        format(v) { return v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
                                        init() { this.display = this.format(this.raw || 0); }
                                    }">
                                        <label class="text-[10px] uppercase tracking-widest font-black text-slate-400 ml-1">Plafon Pinjaman <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</span>
                                            <input type="text" x-model="display" @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                                   class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-black text-sm text-slate-900" placeholder="0">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Tenor -->
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-black text-slate-400 ml-1">Tenor <span class="text-rose-500">*</span></label>
                                            <div class="relative">
                                                <input type="number" wire:model="tenor" class="w-full pr-16 pl-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-black text-sm text-slate-900" placeholder="12">
                                                <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-[9px] uppercase">Bulan</span>
                                            </div>
                                        </div>

                                        <!-- Interest Rate -->
                                        <div class="space-y-2">
                                            <label class="text-[10px] uppercase tracking-widest font-black text-slate-400 ml-1">Bunga (% PA) <span class="text-rose-500">*</span></label>
                                            <div class="relative">
                                                <input type="number" step="0.01" wire:model="interest_rate" class="w-full pr-10 pl-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-black text-sm text-slate-900" placeholder="12.00">
                                                <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Calculation Method -->
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-black text-slate-400 ml-1">Metode Perhitungan <span class="text-rose-500">*</span></label>
                                        <select wire:model="calculation_method" class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-black text-xs text-slate-700">
                                            <option value="FLAT">Flat Rate (Menetap)</option>
                                            <option value="EFFECTIVE">Effective Rate (Menurun)</option>
                                            <option value="ANNUITY">Annuity Rate (Anuitas)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-8 py-6 border-t border-slate-50 bg-slate-50/30">
                            <button type="submit"
                                class="w-full py-4 bg-slate-900 text-white hover:bg-slate-800 hover:shadow-xl hover:shadow-slate-900/10 font-black text-xs uppercase tracking-widest rounded-2xl transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                                <span wire:loading.remove wire:target="calculate" class="material-symbols-outlined text-sm">calculate</span>
                                <div wire:loading wire:target="calculate" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
                                <span>Kalkulasi Proyeksi</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Section -->
            <div class="lg:col-span-2">
                @if($results)
                    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
                        <!-- Summary Cards -->
                        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col">
                            <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                                        <span class="material-symbols-outlined">analytics</span>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest">Detail Proyeksi</h3>
                                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $results['product_name'] }} - {{ $results['method'] }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-8">
                                    <div class="text-right">
                                        <p class="text-[9px] text-slate-400 font-black uppercase tracking-widest mb-1">Total Kewajiban</p>
                                        <p class="text-2xl font-black text-slate-900 tracking-tight">
                                            <span class="text-xs text-slate-400">Rp</span>
                                            {{ number_format($results['total_payment'], 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-10">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 space-y-2">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Angsuran / Bulan</p>
                                        <p class="text-lg font-black text-indigo-600">Rp{{ number_format($results['monthly_payment'], 2, ',', '.') }}</p>
                                    </div>
                                    <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 space-y-2">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Bunga</p>
                                        <p class="text-lg font-black text-slate-900">Rp{{ number_format($results['total_interest'], 2, ',', '.') }}</p>
                                    </div>
                                    <div class="p-6 bg-slate-900 rounded-3xl border border-white/5 space-y-2 shadow-xl shadow-slate-900/10">
                                        <p class="text-[9px] font-black text-white/40 uppercase tracking-widest">Suku Bunga Efektif</p>
                                        <p class="text-lg font-black text-white">{{ format_percent($results['rate']) }} <span class="text-[10px] text-white/40">PA</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Table -->
                        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col">
                            <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                                <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">event_repeat</span>
                                    Jadwal Amortisasi
                                </h3>
                            </div>
                            
                            <div class="max-h-[500px] overflow-y-auto custom-scrollbar">
                                <table class="w-full text-left border-collapse">
                                    <thead class="sticky top-0 bg-slate-50/95 backdrop-blur-md z-10">
                                        <tr>
                                            <th class="py-4 px-8 text-[9px] font-black tracking-widest text-slate-400 uppercase">Ke-</th>
                                            <th class="py-4 px-8 text-[9px] font-black tracking-widest text-slate-400 uppercase text-right">Pokok</th>
                                            <th class="py-4 px-8 text-[9px] font-black tracking-widest text-slate-400 uppercase text-right">Bunga</th>
                                            <th class="py-4 px-8 text-[9px] font-black tracking-widest text-slate-400 uppercase text-right">Total Angsuran</th>
                                            <th class="py-4 px-8 text-[9px] font-black tracking-widest text-slate-400 uppercase text-right">Sisa Pokok</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($results['schedule'] as $item)
                                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                                <td class="py-4 px-8 text-xs font-black text-slate-400 group-hover:text-slate-900 transition-colors">{{ $item['installment_number'] }}</td>
                                                <td class="py-4 px-8 text-xs font-bold text-slate-600 text-right">Rp{{ number_format($item['principal_amount'], 2, ',', '.') }}</td>
                                                <td class="py-4 px-8 text-xs font-bold text-slate-600 text-right">Rp{{ number_format($item['interest_amount'], 2, ',', '.') }}</td>
                                                <td class="py-4 px-8 text-xs font-black text-indigo-600 text-right">Rp{{ number_format($item['total_amount'], 2, ',', '.') }}</td>
                                                <td class="py-4 px-8 text-xs font-bold text-slate-400 text-right">Rp{{ number_format($item['balance'], 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="h-[600px] bg-white rounded-[3rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col items-center justify-center p-12 text-slate-400 space-y-6">
                        <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100">
                            <span class="material-symbols-outlined text-5xl text-slate-200">calculate</span>
                        </div>
                        <div class="text-center space-y-2">
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Kalkulasi Belum Diproses</p>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest max-w-sm leading-relaxed">Masukkan nominal, tenor, dan metode untuk melihat simulasi angsuran kredit secara detail</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
