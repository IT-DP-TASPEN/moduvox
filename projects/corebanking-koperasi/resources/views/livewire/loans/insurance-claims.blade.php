<div class="p-0">
    <x-header title="Klaim Asuransi Pinjaman" subtitle="Pengajuan, persetujuan, dan pencatatan pembayaran klaim pinjaman" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <select wire:model.live="statusFilter" class="pl-3 pr-10 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 appearance-none shadow-sm">
                        <option value="">Semua Status</option>
                        <option value="SUBMITTED">SUBMITTED</option>
                        <option value="APPROVED">APPROVED</option>
                        <option value="PARTIALLY_PAID">PARTIALLY_PAID</option>
                        <option value="PAID">PAID</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400"><span class="material-symbols-outlined text-sm">expand_more</span></div>
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari claim / loan / CIF" class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 w-72 shadow-sm">
                </div>
            </div>
        </x-slot>
    </x-header>

    <div class="p-10 space-y-8">
        @if (session()->has('success'))
            <div class="bg-emerald-50 text-emerald-700 p-6 border border-emerald-100 rounded-[2rem] flex items-center gap-4"><div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600"><span class="material-symbols-outlined text-xl">check_circle</span></div><span class="font-bold text-sm">{{ session('success') }}</span></div>
        @endif
        @if (session()->has('error'))
            <div class="bg-rose-50 text-rose-700 p-6 border border-rose-100 rounded-[2rem] flex items-center gap-4"><div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600"><span class="material-symbols-outlined text-xl">error</span></div><span class="font-bold text-sm">{{ session('error') }}</span></div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="px-8 py-5 bg-slate-50/60 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-700">
                        <span class="material-symbols-outlined text-sm">health_and_safety</span>
                    </div>
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-900">Ajukan Klaim Meninggal Dunia</h3>
                </div>
                <div class="p-8 space-y-5">
                    <div>
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Pilih Loan Aktif Berasuransi (Searchable)</label>
                        <input list="eligible-loans" wire:model.live.debounce.250ms="loanLookup" placeholder="Ketik no rekening / nama anggota..." class="mt-1 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                        <datalist id="eligible-loans">
                            @foreach($loansEligible as $loan)
                                <option value="{{ $loan->account_no }} - {{ $loan->cif->name }} (OS {{ number_format($loan->outstanding_total, 2, ',', '.') }})"></option>
                            @endforeach
                        </datalist>
                        @if($selectedLoanId)
                            <p class="mt-2 text-[10px] font-black uppercase tracking-widest text-emerald-600">Loan terpilih: ID {{ $selectedLoanId }}</p>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Kejadian</label>
                            <input type="date" wire:model="incidentDate" class="mt-1 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Keterangan</label>
                            <input type="text" wire:model="claimRemarks" class="mt-1 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                        </div>
                    </div>
                    <button wire:click="submitDeathClaim" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-slate-900/15">Submit Klaim</button>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="px-8 py-5 bg-slate-50/60 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-700">
                        <span class="material-symbols-outlined text-sm">receipt_long</span>
                    </div>
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-900">Approve / Payment Klaim</h3>
                </div>
                <div class="p-8 space-y-5">
                    <div>
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Pilih Klaim</label>
                        <select wire:model.live="selectedClaimId" class="mt-1 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                        <option value="">Pilih klaim...</option>
                        @foreach($claims as $claim)
                            <option value="{{ $claim->id }}">{{ $claim->claim_no }} - {{ $claim->loanAccount->account_no }} ({{ $claim->status }})</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nominal Approve</label>
                            <input type="number" step="0.01" wire:model="approvedAmount" placeholder="0.00" class="mt-1 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nominal Bayar</label>
                            <input type="number" step="0.01" wire:model="paidAmount" placeholder="0.00" class="mt-1 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button wire:click="approveClaim" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-amber-600/15">Approve + Jurnal</button>
                        <button wire:click="recordPayment" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-emerald-600/15">Bayar + Jurnal</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Claim</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase">Loan / CIF</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Claim</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Approved</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-right">Paid</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Status</th>
                            <th class="py-5 px-6 text-[10px] font-black tracking-widest text-slate-400 uppercase text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($claims as $claim)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 font-black text-xs text-slate-900">{{ $claim->claim_no }}</td>
                                <td class="py-4 px-6 text-xs font-bold text-slate-900">{{ $claim->loanAccount->account_no }}<p class="text-[9px] text-slate-400 font-black tracking-widest uppercase">{{ $claim->loanAccount->cif->name }}</p></td>
                                <td class="py-4 px-6 text-right font-black text-xs text-slate-900">{{ number_format($claim->claim_amount, 2, ',', '.') }}</td>
                                <td class="py-4 px-6 text-right font-black text-xs text-amber-600">{{ number_format($claim->approved_amount, 2, ',', '.') }}</td>
                                <td class="py-4 px-6 text-right font-black text-xs text-emerald-600">{{ number_format($claim->paid_amount, 2, ',', '.') }}</td>
                                <td class="py-4 px-6 text-center">
                                    @php
                                        $statusClass = match($claim->status) {
                                            'SUBMITTED' => 'bg-amber-50 text-amber-600 border-amber-100',
                                            'APPROVED' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                                            'PARTIALLY_PAID' => 'bg-orange-50 text-orange-600 border-orange-100',
                                            'PAID' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            default => 'bg-slate-50 text-slate-600 border-slate-100'
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded border {{ $statusClass }}">{{ $claim->status }}</span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button wire:click="selectClaimForApproval({{ $claim->id }})" class="text-[10px] px-3 py-1 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors font-black uppercase">Set Approve</button>
                                        <button wire:click="selectClaimForPayment({{ $claim->id }})" class="text-[10px] px-3 py-1 rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors font-black uppercase">Set Payment</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-24 text-center text-slate-300">
                                    @if(!filled(trim($search)) && !filled($statusFilter))
                                        <span class="material-symbols-outlined text-6xl mb-4 opacity-50">search</span>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Silakan cari claim / loan / CIF<br>untuk menampilkan data klaim</p>
                                    @else
                                        <span class="material-symbols-outlined text-6xl mb-4 opacity-50">inventory_2</span>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Data klaim tidak ditemukan</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($claims->hasPages())
                <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                    {{ $claims->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </div>
    </div>
</div>
