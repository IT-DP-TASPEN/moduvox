<div class="p-0 print:bg-white print:m-0">
    @php
        $isEditMode = $isEditMode ?? false;
    @endphp
    <div class="print:hidden">
        <x-header :title="$isEditMode ? 'Perubahan Pengajuan Pinjaman' : 'Pendaftaran Pinjaman'" :subtitle="$isEditMode ? 'Perubahan data pinjaman yang belum approved dan belum dicairkan' : 'Pembukaan fasilitas pinjaman baru untuk anggota'" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <div class="flex items-center space-x-3">
                @can('loans.simulation')
                    <a href="{{ route('loans.simulation') }}" wire:navigate class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-sm">calculate</span>
                        <span>Simulasi Kredit</span>
                    </a>
                @endcan
                @can('loans.inquiry')
                    <a href="{{ route('loans.inquiry') }}" wire:navigate class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-sm">arrow_back</span>
                        <span>Kembali ke Inquiry</span>
                    </a>
                @endcan
            </div>
        </x-slot>
    </x-header>
    </div>

    <div class="p-10 print:p-0">
        @if($isEditMode)
            <div class="mb-8 bg-white rounded-2xl shadow-sm border border-slate-200/70 overflow-hidden print:hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-black text-slate-900 uppercase tracking-widest">Pilih Pinjaman yang Bisa Diedit</p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">Pengajuan pending atau pinjaman approved yang belum dicairkan</p>
                    </div>
                    <span class="shrink-0 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                        {{ $pendingLoanCount ?? 0 }} Data
                    </span>
                    @if($selectedLoan ?? null)
                        <button type="button" wire:click="clearSelection" class="shrink-0 flex items-center space-x-2 bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                            <span class="material-symbols-outlined text-sm">swap_horiz</span>
                            <span>Ganti Pinjaman</span>
                        </button>
                    @endif
                </div>

                <div class="p-6">
                    @if(!($selectedLoan ?? null))
                        <div>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                                <input wire:model.live.debounce.300ms="searchLoan" type="text"
                                    class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-xs text-slate-900 shadow-sm"
                                    placeholder="Cari ID pengajuan, nama anggota, CIF, produk, nominal, atau pengaju...">
                            </div>

                            @if(count($loanResults ?? []) > 0)
                                <div class="mt-4 border border-slate-200 rounded-xl overflow-hidden bg-white divide-y divide-slate-100">
                                    @foreach($loanResults as $loan)
                                        <button type="button" wire:click="selectLoan('{{ $loan['type'] }}', {{ $loan['id'] }})"
                                            class="w-full px-5 py-4 grid grid-cols-[1fr_auto_auto] gap-4 items-center text-left hover:bg-slate-50 transition-all group">
                                            <div class="min-w-0">
                                                <p class="text-xs font-black text-slate-900 uppercase truncate">{{ $loan['title'] }}</p>
                                                <p class="mt-1 text-[10px] text-slate-500 font-bold tracking-widest uppercase truncate">{{ $loan['subtitle'] }}</p>
                                            </div>
                                            <span class="px-3 py-1 rounded-lg border {{ $loan['type'] === 'request' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-blue-50 text-blue-600 border-blue-100' }} text-[9px] font-black uppercase tracking-widest">
                                                {{ $loan['type'] === 'request' ? 'Belum Approved' : 'Belum Cair' }}
                                            </span>
                                            <span class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900 transition-all">
                                                <span class="material-symbols-outlined text-sm">edit</span>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-4 py-12 text-center text-slate-300 border border-dashed border-slate-200 rounded-xl bg-slate-50/40">
                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">inbox</span>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-relaxed">
                                        {{ ($pendingLoanCount ?? 0) > 0 ? 'Hasil pencarian tidak ditemukan' : 'Tidak ada pinjaman yang bisa diedit' }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-black text-slate-900 uppercase truncate">{{ $selectedLoan['title'] }}</p>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1 truncate">{{ $selectedLoan['subtitle'] }} | Status: {{ $selectedLoan['status'] }}</p>
                            </div>
                            <span class="shrink-0 px-3 py-1 bg-amber-50 text-amber-600 border border-amber-100 rounded-lg text-[9px] font-black uppercase tracking-widest">{{ $selectedLoan['type'] === 'request' ? 'Menunggu Approval' : 'Belum Dicairkan' }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if(!$isEditMode || ($selectedLoan ?? null))
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 print:block">
            
            <!-- FORM SECTIONS -->
            <div class="lg:col-span-2 print:hidden">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col h-full">
                    <form wire:submit.prevent="submit" class="flex flex-col flex-grow">
                        <div class="p-8 bg-white space-y-12 flex-grow">

                            <!-- SECTION 1: Cek Data Anggota -->
                            <div>
                                <div class="border-b border-slate-200 pb-2 mb-6">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span
                                            class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">person_search</span>
                                        Data Anggota & Rekening Sumber</p>
                                </div>

                                <div class="col-span-2 space-y-4">
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                                        <input wire:model.live.debounce.300ms="searchCif" type="text"
                                            class="w-full pl-12 pr-6 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900"
                                            placeholder="Cari Nama Anggota atau Nomor CIF (Min 3 Karakter)...">

                                        @if(count($cifResults) > 0)
                                        <div class="absolute z-10 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                                            @foreach($cifResults as $res)
                                            <button type="button" wire:click="selectCif({{ $res->id }})"
                                                class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-all border-b border-slate-50 last:border-0 group">
                                                <div class="text-left">
                                                    <p class="text-xs font-black text-slate-900 uppercase">{{ $res->name }}</p>
                                                    <p class="text-[10px] text-slate-500 font-bold tracking-widest">{{ $res->cif_no }} | {{ $res->nik }}</p>
                                                </div>
                                                <span class="material-symbols-outlined text-slate-300 group-hover:text-slate-900 transition-all">add_circle</span>
                                            </button>
                                            @endforeach
                                        </div>
                                        @endif
                                        @error('cif_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    @if($selectedCif)
                                    <div class="p-6 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between animate-in zoom-in-95 duration-300">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                                                <span class="material-symbols-outlined">person</span>
                                            </div>
                                            <div class="space-y-0.5">
                                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ $selectedCif->name }}</h4>
                                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $selectedCif->cif_no }} • NIK: {{ $selectedCif->nik }}</p>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="updatedSearchCif"
                                            class="text-[10px] font-black uppercase tracking-widest text-rose-500 hover:text-rose-600 underline">Ganti Anggota</button>
                                    </div>
                                    @endif

                                    <div class="space-y-2 mt-4">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tabungan Auto-Debit <span class="text-rose-500">*</span></label>
                                        <select wire:model="saving_account_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            @if(!$cif_id)
                                                <option value="">Pilih Anggota Terlebih Dahulu</option>
                                            @elseif(count($availableSavingAccounts) === 0)
                                                <option value="">Tidak ada rekening simpanan aktif</option>
                                            @else
                                                <option value="">Pilih Rekening Sumber Auto-Debit...</option>
                                                @foreach($availableSavingAccounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_no }} - {{ $acc->product->name }} (Sisa Saldo: Rp {{ number_format($acc->balance, 2, ',', '.') }})</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('saving_account_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 2: Produk & Parameter -->
                            <div>
                                <div class="border-b border-slate-200 pb-2 mb-6">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span
                                            class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">payments</span>
                                        Produk & Parameter Kredit</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Produk Kredit <span class="text-rose-500">*</span></label>
                                        <select wire:model.live="loan_product_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            <option value="">Pilih Produk Kredit...</option>
                                            @foreach($loanProducts as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->product_code }})</option>
                                            @endforeach
                                        </select>
                                        @error('loan_product_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Plafon Kredit <span class="text-rose-500">*</span></label>
                                        <div class="relative" x-data="{ 
                                            display: '',
                                            raw: @entangle('principal_amount').live,
                                            format(v) { return v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
                                            init() { this.display = this.format(this.raw || 0); }
                                        }">
                                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</div>
                                            <input type="text" x-model="display"
                                                @input="display = format($event.target.value); raw = display.replace(/\./g, '')"
                                                class="w-full pl-12 pr-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-lg text-slate-900"
                                                placeholder="0">
                                        </div>
                                        @error('principal_amount') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tenor (Bulan) <span class="text-rose-500">*</span></label>
                                        <input wire:model.live="tenor" type="number" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                        @error('tenor') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tgl Realisasi <span class="text-rose-500">*</span></label>
                                        <input wire:model.live="disbursement_date" type="date" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                        @error('disbursement_date') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Suku Bunga (% p.a) <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <input wire:model.live="interest_rate" type="number" step="0.01" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700 pr-10">
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">%</span>
                                        </div>
                                        @error('interest_rate') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Metode Perhitungan <span class="text-rose-500">*</span></label>
                                        @if($is_diskonto_product)
                                            <div class="w-full px-5 py-3.5 bg-amber-50 border border-amber-200 rounded-2xl flex items-center space-x-2 font-black text-xs text-amber-700">
                                                <span class="material-symbols-outlined text-sm">lock</span>
                                                <span>Flat Rate (Diskonto)</span>
                                            </div>
                                        @else
                                            <select wire:model.live="calculation_method" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                                <option value="FLAT">Flat (Tetap)</option>
                                                <option value="EFFECTIVE">Efektif (Menurun)</option>
                                                <option value="ANNUITY">Anuitas</option>
                                            </select>
                                        @endif
                                        @error('calculation_method') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>

                                    @if($is_diskonto_product)
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Bunga di Muka</label>
                                        <div class="relative">
                                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-sm font-bold text-amber-500/70">Rp</span>
                                            <input type="text" readonly value="{{ number_format((float)($diskonto_upfront_amount ?? 0), 2, ',', '.') }}" class="w-full pl-12 pr-5 py-3.5 bg-amber-50 border border-amber-200 rounded-2xl font-black text-sm text-amber-700">
                                        </div>
                                    </div>
                                    @endif

                                    <div class="space-y-2 md:col-span-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Marketing (Opsional)</label>
                                        <select wire:model="marketing_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            <option value="">Pilih Marketing yang Membawa Nasabah...</option>
                                            @foreach($marketings as $mk)
                                                <option value="{{ $mk->id }}">{{ $mk->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>


                                    @if($is_using_insurance)
                                    <!-- Insurance selection moved to Section 6 -->
                                    @endif

                                </div>
                            </div>

                            <!-- SECTION 3: Pekerjaan & Penghasilan -->
                            <div>
                                <div class="border-b border-slate-200 pb-2 mb-6">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span
                                            class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">badge</span>
                                        Pekerjaan & Penghasilan</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tujuan Penggunaan <span class="text-rose-500">*</span></label>
                                        <select wire:model="applicant_purpose" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            <option value="MODAL_USAHA">Modal Usaha</option>
                                            <option value="INVESTASI">Investasi</option>
                                            <option value="KONSUMTIF">Konsumtif</option>
                                            <option value="RENOVASI">Renovasi Rumah</option>
                                            <option value="LAINNYA">Lainnya</option>
                                        </select>
                                        @error('applicant_purpose') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Pekerjaan</label>
                                        <input wire:model="applicant_occupation" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Perusahaan/Usaha</label>
                                        <input wire:model="applicant_company_name" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Penghasilan Per Bulan</label>
                                        <div class="relative" x-data="{ display: '', raw: @entangle('applicant_monthly_income').live, format(v) { return v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }, init() { this.display = this.format(this.raw || 0); } }">
                                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</div>
                                            <input type="text" x-model="display" @input="display = format($event.target.value); raw = display.replace(/\./g, '')" class="w-full pl-12 pr-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900" placeholder="0">
                                        </div>
                                    </div>
                                    <div class="md:col-span-2 space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Alamat Tempat Kerja</label>
                                        <textarea wire:model="applicant_company_address" rows="2" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 4: Agunan -->
                            <div>
                                <div class="border-b border-slate-200 pb-2 mb-6">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span
                                            class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">account_balance</span>
                                        Data Agunan / Jaminan</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status Kolektibilitas <span class="text-rose-500">*</span></label>
                                        <select wire:model="collateral_type" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            <option value="KL1">KL 1 - Lancar</option>
                                            <option value="KL2">KL 2 - Perhatian Khusus</option>
                                            <option value="KL3">KL 3 - Kurang Lancar</option>
                                            <option value="KL4">KL 4 - Diragukan</option>
                                            <option value="KL5">KL 5 - Macet</option>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. Sertifikat / BPKB</label>
                                        <input wire:model="collateral_certificate_no" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nilai Taksasi Agunan</label>
                                        <div class="relative" x-data="{ display: '', raw: @entangle('collateral_value').live, format(v) { return v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }, init() { this.display = this.format(this.raw || 0); } }">
                                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</div>
                                            <input type="text" x-model="display" @input="display = format($event.target.value); raw = display.replace(/\./g, '')" class="w-full pl-12 pr-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900" placeholder="0">
                                        </div>
                                    </div>
                                    <div class="md:col-span-2 space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Keterangan / Deskripsi Agunan</label>
                                        <textarea wire:model="collateral_description" rows="2" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700" placeholder="Contoh: Tanah SHM No. 123 Luas 100m2..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 5: Penjamin -->
                            <div>
                                <div class="border-b border-slate-200 pb-2 mb-6">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span
                                            class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">group</span>
                                        Data Penjamin (Opsional)</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Penjamin</label>
                                        <input wire:model="guarantor_name" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">NIK Penjamin</label>
                                        <input wire:model="guarantor_nik" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Hubungan</label>
                                        <select wire:model="guarantor_relation" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                            <option value="">Pilih Hubungan...</option>
                                            <option value="PASANGAN">Suami / Istri</option>
                                            <option value="ORANG_TUA">Orang Tua</option>
                                            <option value="ANAK">Anak</option>
                                            <option value="SAUDARA">Saudara Kandung</option>
                                            <option value="LAINNYA">Lainnya</option>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">No. HP Penjamin</label>
                                        <input wire:model="guarantor_phone" type="text" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                    </div>
                                </div>
                            </div>

                             <!-- SECTION 6: Biaya-biaya -->
                             <div>
                                 <div class="mb-6 animate-in fade-in duration-500">
                                     <label class="flex items-center space-x-4 cursor-pointer p-6 bg-white border border-slate-200 rounded-[2rem] transition-all hover:bg-slate-50 shadow-sm">
                                         <div class="relative flex items-center justify-center">
                                             <input type="checkbox" wire:model.live="is_using_insurance" class="w-6 h-6 text-slate-900 rounded-lg focus:ring-slate-900 border-slate-300 transition-all cursor-pointer">
                                         </div>
                                         <div class="flex flex-col">
                                             <span class="text-sm font-black text-slate-900 uppercase tracking-tight">Gunakan Proteksi Asuransi</span>
                                             <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Aktifkan untuk menyertakan asuransi dalam pinjaman ini</span>
                                         </div>
                                     </label>
                                 </div>

                                 <div class="border-b border-slate-200 pb-2 mb-6">
                                     <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span
                                             class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">receipt_long</span>
                                         Biaya & Potongan Administrasi</p>
                                 </div>                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                    @if($is_using_insurance)
                                    <div class="space-y-2 md:col-span-2 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Produk Asuransi Pinjaman <span class="text-rose-500">*</span></label>
                                        <select wire:model.live="insurance_product_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700 shadow-sm">
                                            <option value="">Pilih Partner Asuransi...</option>
                                            @foreach($insuranceProducts as $ins)
                                                <option value="{{ $ins->id }}">{{ $ins->name }} ({{ $ins->provider->name }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif

                                    <!-- Group 1: Provisi -->
                                    <div class="space-y-2 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Biaya Provisi</label>
                                        <div class="flex items-center space-x-3">
                                            <div class="relative w-1/3">
                                                <input type="number" step="0.01" wire:model.live="provision_rate" wire:change="calculateFees" class="w-full pl-4 pr-8 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900" placeholder="0.00">
                                                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 font-black text-[10px]">%</div>
                                            </div>
                                            <div class="relative flex-grow" x-data="{ display: '', raw: @entangle('provision_fee').live, format(v) { return v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }, init() { this.display = this.format(this.raw || 0); $watch('raw', val => this.display = this.format(val || 0)); } }">
                                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</div>
                                                <input type="text" x-model="display" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-sm text-slate-500" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Group 2: Administrasi -->
                                    <div class="space-y-2 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Biaya Administrasi</label>
                                        <div class="flex items-center space-x-3">
                                            <div class="relative w-1/3">
                                                <input type="number" step="0.01" wire:model.live="admin_rate" wire:change="calculateFees" class="w-full pl-4 pr-8 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900" placeholder="0.00">
                                                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 font-black text-[10px]">%</div>
                                            </div>
                                            <div class="relative flex-grow" x-data="{ display: '', raw: @entangle('admin_fee').live, format(v) { return v.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }, init() { this.display = this.format(this.raw || 0); $watch('raw', val => this.display = this.format(val || 0)); } }">
                                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</div>
                                                <input type="text" x-model="display" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-sm text-slate-500" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Group 3: Asuransi -->
                                    <div class="space-y-2 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Biaya Asuransi</label>
                                        <div class="flex items-center space-x-3">
                                            <div class="relative w-1/3">
                                                <input type="number" step="0.01" wire:model.live="insurance_rate" wire:change="calculateFees" class="w-full pl-4 pr-8 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900" placeholder="0.00">
                                                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 font-black text-[10px]">%</div>
                                            </div>
                                            <div class="relative flex-grow" x-data="{ display: '', raw: @entangle('insurance_fee').live, format(v) { return Math.round(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }, init() { this.display = this.format(this.raw || 0); $watch('raw', val => this.display = this.format(val || 0)); } }">
                                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</div>
                                                <input type="text" x-model="display" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-sm text-slate-500" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Group 4: Nominal Lainnya -->
                                    <div class="space-y-2 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Flagging & Materai</label>
                                        <div class="flex items-center space-x-3">
                                            <div class="relative w-1/2" x-data="{ display: '', raw: @entangle('flagging_fee').live, format(v) { return Math.round(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }, init() { this.display = this.format(this.raw || 0); $watch('raw', val => this.display = this.format(val || 0)); } }">
                                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-black text-[9px] uppercase">Flag</div>
                                                <input type="text" x-model="display" x-on:input="raw = $event.target.value.replace(/\D/g, ''); display = format(raw); $wire.calculateFees();" class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900" placeholder="0">
                                            </div>
                                            <div class="relative w-1/2" x-data="{ display: '', raw: @entangle('stamp_duty_fee').live, format(v) { return Math.round(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }, init() { this.display = this.format(this.raw || 0); $watch('raw', val => this.display = this.format(val || 0)); } }">
                                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-black text-[9px] uppercase">Mat</div>
                                                <input type="text" x-model="display" x-on:input="raw = $event.target.value.replace(/\D/g, ''); display = format(raw); $wire.calculateFees();" class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Group 5: Angsuran di Muka (hanya non-diskonto) -->
                                    @if(!$is_diskonto_product)
                                    <div class="space-y-2 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Angsuran di Muka</label>
                                        <div class="flex items-center space-x-3">
                                            <div class="relative w-1/3">
                                                <input type="number" wire:model.live="prepaid_count" wire:change="calculateFees" class="w-full pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900" placeholder="0">
                                                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 font-black text-[9px] uppercase">Bln</div>
                                            </div>
                                            <div class="relative flex-grow" x-data="{ display: '', raw: @entangle('prepaid_amount').live, format(v) { return Math.round(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }, init() { this.display = this.format(this.raw || 0); $watch('raw', val => this.display = this.format(val || 0)); } }">
                                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</div>
                                                <input type="text" x-model="display" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-sm text-slate-500" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Group 6: Tabungan Mengendap (hanya non-diskonto) -->
                                    <div class="space-y-2 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tabungan Mengendap</label>
                                        <div class="flex items-center space-x-3">
                                            <div class="relative w-1/3">
                                                <input type="number" wire:model.live="blocked_count" wire:change="calculateFees" class="w-full pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-900" placeholder="0">
                                                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 font-black text-[9px] uppercase">Bln</div>
                                            </div>
                                            <div class="relative flex-grow" x-data="{ display: '', raw: @entangle('blocked_amount').live, format(v) { return Math.round(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }, init() { this.display = this.format(this.raw || 0); $watch('raw', val => this.display = this.format(val || 0)); } }">
                                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rp</div>
                                                <input type="text" x-model="display" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-sm text-slate-500" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Form Footer -->
                        <div class="px-8 py-8 border-t border-slate-100 flex justify-between items-center bg-slate-50/50 mt-auto">
                            <div class="max-w-md">
                                @if($errors->any())
                                <div class="flex items-center text-rose-500 animate-fade-in">
                                    <span class="material-symbols-outlined text-sm mr-2 text-rose-600">error</span>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Gagal: {{ $errors->first() }}</span>
                                </div>
                                @elseif (session()->has('success'))
                                <div class="flex items-center text-emerald-600 animate-fade-in">
                                    <span class="material-symbols-outlined text-sm mr-2">check_circle</span>
                                    <span class="text-[10px] font-black uppercase tracking-widest">{{ session('success') }}</span>
                                </div>
                                @else
                                <div class="space-y-1">
                                    <h4 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Konfirmasi Data</h4>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed">Pastikan semua informasi aplikasi kredit nasabah sudah benar.</p>
                                </div>
                                @endif
                            </div>
                            <div class="flex items-center space-x-4">
                                <button type="submit" class="px-10 py-4 bg-slate-900 text-white hover:shadow-xl hover:shadow-slate-900/20 font-black text-[10px] uppercase tracking-[0.2em] rounded-2xl transition-all active:scale-95 flex items-center group">
                                    <div wire:loading wire:target="submit" class="w-4 h-4 border-2 border-slate-400 border-t-white rounded-full animate-spin mr-3"></div>
                                    <span wire:loading.remove wire:target="submit" class="material-symbols-outlined text-sm mr-3 group-hover:rotate-12 transition-transform">verified_user</span>
                                    <span>{{ $isEditMode ? 'Simpan Perubahan' : 'Simpan & Ajukan Kredit' }}</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PROJECTION PANEL (Right Sidebar) -->
            <div class="lg:col-span-1 print:block print:w-full lg:sticky lg:top-10 self-start space-y-6 z-20">
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-200/60 overflow-hidden animate-slide-up print:border-none print:shadow-none print:rounded-none transition-all hover:shadow-2xl hover:shadow-slate-200/60 duration-500">
                    <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="material-symbols-outlined text-indigo-500 text-3xl">analytics</span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Ringkasan Pengajuan</h3>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Kalkulasi Simulasi Kredit</p>
                            </div>
                        </div>
                        <button onclick="window.print()" type="button"
                            class="print:hidden flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-3 py-1.5 rounded-lg font-bold text-[10px] hover:bg-slate-50 transition-all shadow-sm">
                            <span class="material-symbols-outlined text-[14px]">print</span>
                            <span>Cetak</span>
                        </button>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Plafon Kredit</p>
                                <p class="text-sm font-black text-slate-900">Rp{{ number_format((float)($principal_amount ?? 0), 2, ',', '.') }}</p>
                            </div>
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Tenor</p>
                                <p class="text-sm font-black text-slate-900">{{ $tenor ?? 0 }} Bulan</p>
                            </div>
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Suku Bunga</p>
                                <p class="text-sm font-black text-slate-900">{{ format_percent($interest_rate ?? 0) }} p.a</p>
                            </div>
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Metode Hitung</p>
                                <p class="text-sm font-black text-indigo-600">{{ $calculation_method }}</p>
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                            <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest mb-2">Potongan & Biaya</p>
                            <div class="flex justify-between text-[10px] font-bold text-slate-500">
                                <span>Provisi</span>
                                <span>Rp{{ number_format((float)($provision_fee ?? 0), 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-[10px] font-bold text-slate-500">
                                <span>Administrasi</span>
                                <span>Rp{{ number_format((float)($admin_fee ?? 0), 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-[10px] font-bold text-slate-500">
                                <span>Asuransi</span>
                                <span>Rp{{ number_format((float)($insurance_fee ?? 0), 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-[10px] font-bold text-slate-500">
                                <span>Flagging</span>
                                <span>Rp{{ number_format((float)($this->parseAmount($flagging_fee) ?? 0), 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-[10px] font-bold text-slate-500">
                                <span>Materai</span>
                                <span>Rp{{ number_format((float)($this->parseAmount($stamp_duty_fee) ?? 0), 2, ',', '.') }}</span>
                            </div>
                            @if($is_diskonto_product && $diskonto_upfront_amount > 0)
                            <div class="flex justify-between text-[10px] font-black text-amber-600 bg-amber-50 px-2 py-1 rounded-lg border border-amber-100 mt-1">
                                <span>Bunga di Muka Diskonto ({{ $tenor }} bln)</span>
                                <span>Rp{{ number_format((float)($diskonto_upfront_amount ?? 0), 2, ',', '.') }}</span>
                            </div>
                            @endif
                            @if(!$is_diskonto_product && $prepaid_count > 0)
                            <div class="flex justify-between text-[10px] font-bold text-slate-500">
                                <span>Angsuran di Muka ({{ $prepaid_count }} bln)</span>
                                <span>Rp{{ number_format((float)($prepaid_amount ?? 0), 2, ',', '.') }}</span>
                            </div>
                            @endif
                            @if(!$is_diskonto_product && $blocked_count > 0)
                            <div class="flex justify-between text-[10px] font-bold text-slate-500 text-amber-600">
                                <span>Tabungan Mengendap ({{ $blocked_count }} bln)</span>
                                <span>Rp{{ number_format((float)($blocked_amount ?? 0), 2, ',', '.') }}</span>
                            </div>
                            @endif
                        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100 space-y-3">
                            <p class="text-[10px] font-black text-emerald-800 uppercase tracking-widest border-b border-emerald-200/50 pb-2">Estimasi Dana Cair (Net)</p>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-bold text-emerald-600 uppercase">Plafon - Potongan</span>
                                <span class="text-sm font-black text-emerald-700">
                                    Rp{{ number_format((float)($this->parseAmount($principal_amount) - ($provision_fee ?? 0) - ($admin_fee ?? 0) - ($insurance_fee ?? 0) - $this->parseAmount($flagging_fee) - $this->parseAmount($stamp_duty_fee) - ($prepaid_amount ?? 0) - ($blocked_amount ?? 0) - ($diskonto_upfront_amount ?? 0)), 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        </div>

                        <hr class="border-dashed border-slate-200">

                        <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                            @if(!empty($this->simulasi))
                                @if($is_diskonto_product)
                                    {{-- Diskonto: bunga dibayar tiap bulan, pokok di akhir --}}
                                    @php $lastRow = collect($this->simulasi)->last(); @endphp
                                    <p class="text-[10px] font-black text-amber-700 uppercase tracking-widest mb-1">Angsuran Bunga / Bulan</p>
                                    <p class="text-xl font-black text-amber-600">Rp{{ number_format($this->simulasi[0]['interest_amount'] ?? 0, 2, ',', '.') }}</p>
                                    <p class="text-[9px] text-amber-400 font-bold uppercase mt-1">Pokok = 0, hanya bunga flat per bulan</p>
                                    <div class="mt-2 p-2 bg-amber-100/50 rounded-lg border border-amber-200/50 space-y-1">
                                        <p class="text-[9px] text-amber-600 font-bold uppercase">Sisa Pokok Terakhir (bln {{ $tenor }}): <span class="text-amber-800 font-black">Rp{{ number_format($lastRow['principal_amount'] ?? 0, 2, ',', '.') }}</span></p>
                                        <p class="text-[9px] text-amber-500 font-bold uppercase">Total Bunga Diskonto (Dipotong Cair): <span class="text-amber-700">Rp{{ number_format($diskonto_upfront_amount ?? 0, 2, ',', '.') }}</span></p>
                                        <p class="text-[8px] text-amber-400 font-medium italic mt-1">*Bunga sudah lunas di depan untuk seluruh tenor</p>
                                    </div>
                                @else
                                    <p class="text-[10px] font-black text-indigo-700 uppercase tracking-widest mb-1">Estimasi Angsuran / Bulan</p>
                                    <p class="text-xl font-black text-indigo-600">Rp{{ number_format($this->simulasi[0]['total_amount'] ?? 0, 2, ',', '.') }}</p>
                                    <p class="text-[9px] text-indigo-400 font-bold uppercase mt-2">*Berdasarkan simulasi awal</p>
                                @endif
                            @else
                                <div class="py-2 text-center opacity-60">
                                    <span class="material-symbols-outlined text-2xl mb-2 text-indigo-400">calculate</span>
                                    <p class="text-[9px] font-bold uppercase tracking-widest text-indigo-600">Lengkapi parameter <br>untuk melihat simulasi</p>
                                </div>
                            @endif
                        </div>

                        @if(!empty($this->simulasi))
                            <div class="flex justify-between items-center px-1 border-t border-slate-100 pt-4 mt-2">
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Total Akumulasi Pembayaran</p>
                                <p class="text-sm font-black text-slate-900">
                                    Rp{{ number_format((float) array_sum(array_column($this->simulasi, 'total_amount')), 2, ',', '.') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- SCHEDULE TABLE -->
                @if(!empty($this->simulasi))
                <div class="mt-6 animate-slide-up">
                    <div class="border-b border-slate-200 pb-2 mb-4">
                        <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest">
                            <span class="material-symbols-outlined text-xs align-middle mr-1 text-slate-400">calendar_month</span>
                            Jadwal Angsuran
                        </p>
                    </div>
                    
                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm print:border-none print:shadow-none print:rounded-none">
                        <div class="max-h-[32rem] overflow-y-auto overflow-x-auto print:max-h-none print:overflow-visible custom-scrollbar">
                            <table class="w-full text-left border-collapse min-w-[500px]">
                                <thead class="bg-slate-50 border-b border-slate-200 sticky top-0 z-10 print:static">
                                    <tr>
                                        <th class="py-2.5 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-center">Bulan</th>
                                        <th class="py-2.5 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Angsuran</th>
                                        <th class="py-2.5 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Pokok</th>
                                        <th class="py-2.5 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Bunga</th>
                                        <th class="py-2.5 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right border-l border-slate-100 bg-emerald-50/30">Sisa Saldo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($this->simulasi as $item)
                                    <tr class="hover:bg-slate-50 transition-colors print:break-inside-avoid">
                                        <td class="py-2.5 px-3 text-[10px] font-bold text-slate-900 text-center">{{ $item['installment_number'] }}</td>
                                        <td class="py-2.5 px-3 text-[10px] font-bold text-indigo-600 text-right">Rp{{ number_format($item['total_amount'], 2, ',', '.') }}</td>
                                        <td class="py-2.5 px-3 text-[10px] font-bold text-slate-700 text-right">Rp{{ number_format($item['principal_amount'], 2, ',', '.') }}</td>
                                        <td class="py-2.5 px-3 text-[10px] font-bold text-rose-500 text-right">
                                            Rp{{ number_format($item['interest_amount'], 2, ',', '.') }}
                                            @if($is_diskonto_product)
                                                <div class="text-[8px] text-rose-400 italic">Dibayar Dimuka</div>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3 text-[10px] font-black text-emerald-600 text-right border-l border-emerald-50 bg-emerald-50/10">Rp{{ number_format($item['balance'], 2, ',', '.') }}</td>
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
        @endif
    </div>
</div>
