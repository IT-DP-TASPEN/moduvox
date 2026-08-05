<div class="p-0">
    <x-header title="Transaksi Antar Bank" subtitle="Transfer dana antara rekening internal dan bank umum" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <div class="flex items-center space-x-3">
                <a href="{{ route('journals.index') }}" wire:navigate class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-sm">menu_book</span>
                    <span>Jurnal Umum</span>
                </a>
            </div>
        </x-slot>
    </x-header>

    <div class="p-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- FORM --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                    <form wire:submit.prevent="save" class="flex flex-col">
                        <div class="p-8 space-y-8">

                            {{-- Section Header --}}
                            <div class="border-b border-slate-200 pb-2">
                                <p class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                                    <span class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">swap_horiz</span>
                                    Detail Transaksi
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Tanggal --}}
                                <div class="space-y-2">
                                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                                    <input wire:model="transfer_date" type="date"
                                        class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                    @error('transfer_date') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- No. Referensi --}}
                                <div class="space-y-2">
                                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. Referensi <span class="text-rose-500">*</span></label>
                                    <input wire:model="reference_no" type="text"
                                        class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                    @error('reference_no') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- Dari Rekening --}}
                                <div class="space-y-2">
                                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Dari Rekening (Sumber) <span class="text-rose-500">*</span></label>
                                    <select wire:model="from_coa_id"
                                        class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                        <option value="">Pilih Rekening Sumber...</option>
                                        @foreach($bankCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} — {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('from_coa_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- Ke Rekening --}}
                                <div class="space-y-2">
                                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Ke Rekening (Tujuan) <span class="text-rose-500">*</span></label>
                                    <select wire:model="to_coa_id"
                                        class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                        <option value="">Pilih Rekening Tujuan...</option>
                                        @foreach($bankCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->coa_code }} — {{ $coa->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('to_coa_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- Jumlah --}}
                                <div class="space-y-2 md:col-span-2">
                                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Jumlah Transfer <span class="text-rose-500">*</span></label>
                                    <div class="relative" x-data="{
                                        display: '',
                                        raw: @entangle('amount').live,
                                        format(v) { return v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
                                        init() { this.display = this.format(this.raw || 0); }
                                    }">
                                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</div>
                                        <input type="text" x-model="display"
                                            @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                            class="w-full pl-12 pr-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-lg text-slate-900"
                                            placeholder="0">
                                    </div>
                                    @error('amount') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- Keterangan --}}
                                <div class="space-y-2 md:col-span-2">
                                    <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Keterangan <span class="text-rose-500">*</span></label>
                                    <textarea wire:model="description" rows="3"
                                        placeholder="Contoh: Transfer dana operasional ke Bank Mandiri cabang Bekasi..."
                                        class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700"></textarea>
                                    @error('description') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex justify-end">
                            <button type="submit"
                                class="flex items-center space-x-2 bg-slate-900 text-white px-8 py-3 rounded-2xl font-black text-sm hover:bg-slate-700 transition-all shadow-sm">
                                <span class="material-symbols-outlined text-sm">send</span>
                                <span>Proses Transfer</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- SUMMARY CARD --}}
            <div class="space-y-4">
                {{-- Info Box --}}
                <div class="bg-slate-900 text-white rounded-[2rem] p-6 space-y-4">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white">account_balance</span>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Rekening Bank Tersedia</p>
                        <div class="mt-3 space-y-2">
                            @foreach($bankCoas as $coa)
                            <div class="flex items-center justify-between py-2 border-b border-white/10 last:border-0">
                                <span class="text-xs font-bold text-white">{{ $coa->name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $coa->coa_code }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Flash Messages --}}
                @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-start space-x-3">
                    <span class="material-symbols-outlined text-emerald-500 text-sm mt-0.5">check_circle</span>
                    <p class="text-xs font-bold text-emerald-700">{{ session('success') }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Recent Transfers --}}
        @if(count($recentTransfers) > 0)
        <div class="mt-8 bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <p class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                    <span class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">history</span>
                    Riwayat Transaksi Antar Bank
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-bold text-slate-500 uppercase tracking-widest">No. Ref</th>
                            <th class="px-6 py-3 text-left font-bold text-slate-500 uppercase tracking-widest">Tanggal</th>
                            <th class="px-6 py-3 text-left font-bold text-slate-500 uppercase tracking-widest">Keterangan</th>
                            <th class="px-6 py-3 text-right font-bold text-slate-500 uppercase tracking-widest">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentTransfers as $trx)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900">{{ $trx['reference_no'] }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ \Carbon\Carbon::parse($trx['transaction_date'])->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ Str::limit($trx['description'], 60) }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase
                                    {{ $trx['status'] === 'APPROVED' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $trx['status'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
