<div>
    <x-header title="PENGATURAN PAJAK" subtitle="KONFIGURASI TARIF DAN COA PPH BADAN" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first()">
        <x-slot:actions>
            <button wire:click="createNew" class="flex items-center space-x-2 bg-white border border-surface-dim text-primary px-4 py-2 rounded-xl hover:bg-surface transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-sm">add</span>
                <span class="text-xs font-bold uppercase tracking-wider">TAMBAH PERIODE</span>
            </button>
            <button wire:click="save" class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-xl hover:bg-primary-dim transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-sm">save</span>
                <span class="text-xs font-bold uppercase tracking-wider">SIMPAN</span>
            </button>
        </x-slot:actions>
    </x-header>

    <div class="p-8 space-y-6">
        @if (session()->has('success'))
            <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-2xl border border-emerald-100 text-xs font-bold uppercase">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <section class="xl:col-span-2 bg-white border border-surface-dim rounded-[2rem] shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-surface-dim">
                    <h2 class="text-sm font-black text-primary uppercase tracking-widest">
                        {{ $editingId ? 'EDIT KONFIGURASI PAJAK' : 'KONFIGURASI PAJAK BARU' }}
                    </h2>
                    @if($editingId)
                        <p class="mt-1 text-[10px] font-bold text-outline uppercase tracking-widest">ID: {{ $editingId }}</p>
                    @endif
                </div>

                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black text-outline uppercase tracking-widest">NAMA KONFIGURASI</label>
                        <input wire:model="name" type="text" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold uppercase focus:outline-none focus:ring-4 focus:ring-primary/10">
                        @error('name') <span class="text-[9px] text-red-500 font-bold uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-outline uppercase tracking-widest">TARIF PAJAK (%)</label>
                        <input wire:model.live="tax_rate" type="number" min="0" max="100" step="0.01" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10">
                        @error('tax_rate') <span class="text-[9px] text-red-500 font-bold uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-outline uppercase tracking-widest">BERLAKU MULAI</label>
                        <input wire:model="effective_from" type="date" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10">
                        @error('effective_from') <span class="text-[9px] text-red-500 font-bold uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-outline uppercase tracking-widest">BERLAKU SAMPAI</label>
                        <input wire:model="effective_to" type="date" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-primary/10">
                        @error('effective_to') <span class="text-[9px] text-red-500 font-bold uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black text-outline uppercase tracking-widest">DASAR HITUNG</label>
                        <select wire:model.live="calculation_base" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold uppercase focus:outline-none focus:ring-4 focus:ring-primary/10">
                            <option value="TOTAL_REVENUE">TOTAL PENDAPATAN</option>
                            <option value="PROFIT_BEFORE_TAX">LABA SEBELUM PAJAK</option>
                        </select>
                        @error('calculation_base') <span class="text-[9px] text-red-500 font-bold uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-outline uppercase tracking-widest">COA BEBAN PAJAK</label>
                        <select wire:model="expense_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold uppercase focus:outline-none focus:ring-4 focus:ring-primary/10">
                            <option value="">PILIH COA</option>
                            @foreach($expenseCoas as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ strtoupper($coa->name) }}</option>
                            @endforeach
                        </select>
                        @error('expense_coa_id') <span class="text-[9px] text-red-500 font-bold uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-outline uppercase tracking-widest">COA UTANG PAJAK</label>
                        <select wire:model="payable_coa_id" class="w-full px-5 py-4 bg-surface border border-surface-dim rounded-2xl text-xs font-bold uppercase focus:outline-none focus:ring-4 focus:ring-primary/10">
                            <option value="">PILIH COA</option>
                            @foreach($liabilityCoas as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->coa_code }} - {{ strtoupper($coa->name) }}</option>
                            @endforeach
                        </select>
                        @error('payable_coa_id') <span class="text-[9px] text-red-500 font-bold uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-between p-5 bg-surface rounded-2xl border border-surface-dim md:col-span-2">
                        <div>
                            <h4 class="text-[10px] font-black text-primary uppercase">AKTIF</h4>
                            <p class="text-[9px] text-outline font-medium uppercase">DIGUNAKAN SEBAGAI KONFIGURASI PAJAK BERJALAN</p>
                        </div>
                        <input type="checkbox" wire:model="is_active" class="w-5 h-5 rounded-lg border-surface-dim text-primary focus:ring-primary">
                    </div>
                </div>
            </section>

            <section class="bg-white border border-surface-dim rounded-[2rem] shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-surface-dim">
                    <h2 class="text-sm font-black text-primary uppercase tracking-widest">PREVIEW HITUNG</h2>
                </div>
                <div class="p-8 space-y-5">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-outline uppercase tracking-widest">DARI</label>
                            <input type="date" wire:model.live="preview_start" class="w-full px-4 py-3 bg-surface border border-surface-dim rounded-xl text-xs font-bold">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-outline uppercase tracking-widest">SAMPAI</label>
                            <input type="date" wire:model.live="preview_end" class="w-full px-4 py-3 bg-surface border border-surface-dim rounded-xl text-xs font-bold">
                        </div>
                    </div>

                    <div class="space-y-3 text-xs font-bold uppercase">
                        <div class="flex justify-between"><span class="text-outline">TOTAL PENDAPATAN</span><span>RP {{ number_format($this->preview['total_revenue'], 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-outline">BEBAN SEBELUM PAJAK</span><span>RP {{ number_format($this->preview['expense_before_tax'], 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-outline">LABA SEBELUM PAJAK</span><span>RP {{ number_format($this->preview['profit_before_tax'], 0, ',', '.') }}</span></div>
                        <div class="h-px bg-surface-dim"></div>
                        <div class="flex justify-between"><span class="text-outline">DASAR PAJAK</span><span>RP {{ number_format($this->preview['base_amount'], 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-outline">BEBAN PAJAK</span><span>RP {{ number_format($this->preview['tax_amount'], 0, ',', '.') }}</span></div>
                        <div class="flex justify-between text-primary text-sm"><span>LABA BERSIH</span><span>RP {{ number_format($this->preview['net_profit'], 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </section>
        </div>

        <section class="bg-white border border-surface-dim rounded-[2rem] shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-surface-dim">
                <h2 class="text-sm font-black text-primary uppercase tracking-widest">RIWAYAT KONFIGURASI</h2>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface border-b border-surface-dim uppercase text-[10px] tracking-widest font-black text-outline">
                        <th class="px-6 py-4">NAMA</th>
                        <th class="px-6 py-4">TARIF</th>
                        <th class="px-6 py-4">PERIODE</th>
                        <th class="px-6 py-4">DASAR HITUNG</th>
                        <th class="px-6 py-4">COA BEBAN</th>
                        <th class="px-6 py-4">COA UTANG</th>
                        <th class="px-6 py-4">STATUS</th>
                        <th class="px-6 py-4 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-dim">
                    @forelse($settings as $setting)
                        <tr wire:key="tax-setting-{{ $setting->id }}" class="{{ $editingId === $setting->id ? 'bg-primary/5' : '' }}">
                            <td class="px-6 py-4 text-xs font-bold uppercase">{{ $setting->name }}</td>
                            <td class="px-6 py-4 text-xs font-bold">{{ format_percent($setting->tax_rate) }}</td>
                            <td class="px-6 py-4 text-xs font-bold uppercase">
                                {{ $setting->effective_from?->format('d/m/Y') ?? '-' }}
                                -
                                {{ $setting->effective_to?->format('d/m/Y') ?? 'Seterusnya' }}
                            </td>
                            <td class="px-6 py-4 text-xs font-bold uppercase">{{ $setting->calculation_base === 'TOTAL_REVENUE' ? 'TOTAL PENDAPATAN' : 'LABA SEBELUM PAJAK' }}</td>
                            <td class="px-6 py-4 text-xs font-bold uppercase">{{ $setting->expenseCoa?->coa_code }} - {{ $setting->expenseCoa?->name }}</td>
                            <td class="px-6 py-4 text-xs font-bold uppercase">{{ $setting->payableCoa?->coa_code }} - {{ $setting->payableCoa?->name }}</td>
                            <td class="px-6 py-4 text-xs font-bold uppercase">{{ $setting->is_active ? 'AKTIF' : 'NONAKTIF' }}</td>
                            <td class="px-6 py-4 text-right">
                                <button type="button"
                                    wire:click.prevent="loadSetting({{ $setting->id }})"
                                    wire:loading.attr="disabled"
                                    onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
                                    class="text-xs font-black text-primary uppercase hover:underline disabled:opacity-40">
                                    {{ $editingId === $setting->id ? 'DIEDIT' : 'EDIT' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-xs font-bold text-outline uppercase">BELUM ADA KONFIGURASI</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
</div>
