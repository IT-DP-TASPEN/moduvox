<div class="p-0">
    <x-header title="Pelunasan Pinjaman" subtitle="Penyelesaian penuh pinjaman aktif dengan kontrol nominal dan approval" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="No rekening / CIF / nama..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 w-72 shadow-sm">
            </div>
        </x-slot>
    </x-header>

    <div class="p-10">
        @if (session()->has('success'))
            <div class="mb-8 bg-emerald-50 text-emerald-700 p-6 border border-emerald-100 rounded-[2rem] flex items-center gap-4 animate-in fade-in duration-500">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600"><span class="material-symbols-outlined text-xl">check_circle</span></div>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-8 bg-rose-50 text-rose-700 p-6 border border-rose-100 rounded-[2rem] flex items-center gap-4 animate-in fade-in duration-500">
                <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600"><span class="material-symbols-outlined text-xl">error</span></div>
                <span class="font-bold text-sm">{{ session('error') }}</span>
            </div>
        @endif

        @if($viewMode === 'grid')
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden animate-in fade-in duration-500">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50">
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center w-20">Opsi</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">No. Rekening</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Nasabah</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Produk</th>
                                <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Outstanding</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($loans as $loan)
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="py-4 px-6 text-center">
                                        <button wire:click="viewAccount({{ $loan->id }})" class="w-8 h-8 flex items-center justify-center bg-white text-slate-400 hover:bg-slate-900 hover:text-white rounded-lg shadow-sm border border-slate-100 transition-all mx-auto">
                                            <span class="material-symbols-outlined text-sm">visibility</span>
                                        </button>
                                    </td>
                                    <td class="py-4 px-6 font-black text-xs text-slate-900">{{ $loan->account_no }}</td>
                                    <td class="py-4 px-6 text-xs font-bold text-slate-900">{{ $loan->cif->name }}<p class="text-[9px] text-slate-400 font-black tracking-widest uppercase">{{ $loan->cif->cif_no }}</p></td>
                                    <td class="py-4 px-6"><span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black bg-slate-100 text-slate-600 uppercase tracking-widest">{{ $loan->product->name }}</span></td>
                                    <td class="py-4 px-6 text-right font-black text-xs text-rose-600">Rp {{ number_format($loan->outstanding_total, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-24 text-center text-slate-300">
                                        @if(!filled(trim($search)))
                                            <span class="material-symbols-outlined text-6xl mb-4 opacity-50">search</span>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Silakan cari rekening, CIF, atau nama anggota<br>untuk menampilkan data pelunasan kredit</p>
                                        @else
                                            <span class="material-symbols-outlined text-6xl mb-4 opacity-50">drafts</span>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Data tidak ditemukan untuk pencarian saat ini</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($loans->hasPages())<div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">{{ $loans->links(data: ['scrollTo' => false]) }}</div>@endif
            </div>
        @else
            <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <button wire:click="closeView" class="p-2 bg-white hover:bg-slate-200 rounded-xl transition-all shadow-sm border border-slate-200"><span class="material-symbols-outlined text-sm">arrow_back</span></button>
                            <div>
                                <h2 class="font-extrabold text-sm text-slate-900 tracking-wider uppercase">Konfirmasi Pelunasan: <span class="text-indigo-600">{{ $selectedAccount->account_no }}</span></h2>
                                <p class="text-[10px] uppercase font-bold text-slate-500 tracking-widest mt-1">{{ $selectedAccount->cif->name }} | {{ $selectedAccount->product->name }}</p>
                            </div>
                        </div>
                        <span class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-xl border bg-rose-50 text-rose-600 border-rose-100">Total Pelunasan Rp {{ number_format((float) $payment_amount, 2, ',', '.') }}</span>
                    </div>
                </div>

                <div class="p-10 bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60">
                    @if($hasOpenInsuranceClaim)
                        <div class="mb-6 p-5 rounded-2xl border border-amber-200 bg-amber-50">
                            <p class="text-[10px] font-black uppercase tracking-widest text-amber-700 mb-2">Klaim Asuransi Sedang Berjalan</p>
                            <p class="text-xs font-bold text-amber-700">Pelunasan manual dinonaktifkan. Silakan lanjutkan proses melalui menu Klaim Asuransi Pinjaman.</p>
                        </div>
                    @endif

                    <div class="mb-6 p-5 rounded-2xl border border-indigo-100 bg-indigo-50/70">
                        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-500 mb-2">Sumber Dana Pelunasan</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Rekening Tabungan</p>
                                <p class="text-sm font-black text-slate-900 mt-1">
                                    {{ $selectedAccount->savingAccount->account_no ?? 'TIDAK TERHUBUNG' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Saldo Efektif</p>
                                <p class="text-sm font-black mt-1 {{ ($selectedAccount->savingAccount?->effective_balance ?? 0) >= (float) $payment_amount ? 'text-emerald-600' : 'text-rose-600' }}">
                                    Rp {{ number_format((float) ($selectedAccount->savingAccount?->effective_balance ?? 0), 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Pokok Pinjaman</label>
                            <input type="text" value="{{ $principal_amount_display }}" readonly class="w-full px-5 py-4 bg-slate-100 border border-slate-200 rounded-2xl font-black text-sm text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kewajiban Bunga</label>
                            <input type="text" wire:model.live="interest_obligation_display" wire:blur="formatInterestObligation" inputmode="decimal" placeholder="0,00" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                            @error('interest_obligation_amount') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Denda</label>
                            <input type="text" wire:model.live="penalty_amount_display" wire:blur="formatPenaltyAmount" inputmode="decimal" placeholder="0,00" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                            @error('penalty_amount') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-2 mb-6">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Total Pelunasan</label>
                        <input type="text" wire:model="payment_amount_display" readonly class="w-full px-5 py-4 bg-slate-900 border border-slate-900 rounded-2xl font-black text-lg text-white shadow-lg shadow-slate-900/10">
                        @error('payment_amount') <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100 mb-6 text-[10px] font-bold text-amber-700 uppercase tracking-wider">
                        Total pelunasan diposting sesuai mapping COA produk: pokok, pendapatan bunga, dan pendapatan denda.
                    </div>

                    <button wire:click="requestSettlement" @disabled($hasOpenInsuranceClaim) class="bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:text-slate-500 disabled:shadow-none text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-lg shadow-emerald-600/20 transition-all active:scale-95 flex items-center space-x-2">
                        <span class="material-symbols-outlined text-sm">task_alt</span>
                        <span>Proses Pelunasan</span>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <div @if($showInsuranceConfirmationModal) class="fixed inset-0 z-[60] flex items-center justify-center p-6" @else class="hidden" @endif>
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" wire:click="closeInsuranceConfirmationModal"></div>
        <div class="relative w-full max-w-lg bg-white rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="p-8 border-b border-slate-100">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Konfirmasi Produk Asuransi</h3>
                <p class="text-xs font-bold text-slate-500 mt-2">Pinjaman ini menggunakan asuransi. Anda ingin lanjutkan pelunasan atau proses klaim asuransi terlebih dahulu?</p>
            </div>
            <div class="p-8 space-y-3">
                <button wire:click="confirmInsuranceAndProcessSettlement" class="w-full py-4 bg-emerald-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-emerald-700 transition-all">
                    Lanjutkan Pelunasan
                </button>
                <a href="{{ route('loans.insurance-claims') }}" class="block w-full py-4 bg-amber-500 text-white text-center rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-amber-600 transition-all">
                    Klaim Asuransi Dulu
                </a>
                <button wire:click="closeInsuranceConfirmationModal" class="w-full py-4 bg-white border border-slate-200 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-50 transition-all">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>
