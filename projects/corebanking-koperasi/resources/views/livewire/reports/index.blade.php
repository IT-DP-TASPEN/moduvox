<div class="p-0">
    <x-header title="Pusat Laporan" subtitle="Unduh dan pantau laporan data operasional" :user="$user" :role="$role">
        <x-slot:actions>
            @if($report_type)
            <button wire:click="downloadReport"
                class="flex items-center space-x-2 bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                <span class="material-symbols-outlined text-sm">download</span>
                <span>Unduh Laporan (CSV)</span>
            </button>
            @endif
        </x-slot:actions>
    </x-header>

    <div class="p-10">
        @if (session()->has('error'))
            <div class="mb-6 bg-rose-50 text-rose-700 px-6 py-4 rounded-2xl border border-rose-100 flex items-center shadow-sm">
                <span class="material-symbols-outlined mr-3 text-lg">error</span>
                <span class="font-bold text-sm">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
            <!-- Filter Section -->
            <div class="p-8 border-b border-slate-100 bg-slate-50/30">
                <div class="flex flex-wrap items-end gap-4">
                    <div class="space-y-1" style="flex: 2 1 280px;">
                        <label class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Jenis Laporan</label>
                        <select wire:model.live="report_type" class="w-full pl-3 pr-8 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all">
                            <option value="">-- PILIH LAPORAN --</option>
                            <optgroup label="TEMPLATE PUSAT LAPORAN SIRARA">
                                @foreach($templateReports as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="LAPORAN SISTEM">
                            <option value="cifs">DATA CIF (ANGGOTA)</option>
                            <option value="savings">DATA SIMPANAN</option>
                            <option value="deposits">DATA SIMPANAN BERJANGKA</option>
                            <option value="loans">DATA PINJAMAN</option>
                            <option value="assets">DATA INVENTARIS / ASET</option>
                            <option value="asset_rentals">DATA ASET DISEWAKAN</option>
                            <option value="audit">LOG AKTIVITAS (AUDIT)</option>
                            <option value="balance_sheet">NERACA (BALANCE SHEET)</option>
                            <option value="income_statement">LABA RUGI (INCOME STATEMENT)</option>
                            <option value="cash_flow">ARUS KAS (CASH FLOW)</option>
                            <option value="equity_change">PERUBAHAN EKUITAS</option>
                            </optgroup>
                        </select>
                    </div>

                    @if($report_type !== 'audit')
                    <div class="space-y-1" style="flex: 1 1 180px;">
                        <label class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Cabang</label>
                        <select wire:model.live="branch_id" class="w-full pl-3 pr-8 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all">
                            <option value="">SEMUA CABANG</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ strtoupper($b->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div class="space-y-1" style="flex: 1 1 180px;">
                        <!-- Spacer for audit log -->
                    </div>
                    @endif

                    <div class="space-y-1" style="flex: 1 1 180px;">
                        <label class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Produk</label>
                        <select wire:model.live="product_id" class="w-full pl-3 pr-8 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all" @if(!in_array($report_type, ['savings', 'loans', 'deposits', 'saving_nominative', 'saving_opening', 'saving_closing', 'loan_nominative', 'loan_deferred_interest', 'loan_repayment', 'loan_disbursement', 'loan_settlement', 'deposit_nominative', 'deposit_interest_payment', 'deposit_withdrawal', 'deposit_placement'])) disabled @endif>
                            <option value="">SEMUA PRODUK</option>
                            @foreach($this->products as $product)
                                <option value="{{ $product->id }}">{{ strtoupper($product->product_code . ' - ' . $product->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1" style="flex: 1 1 160px;">
                        <label class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Status / Aksi</label>
                        <select wire:model.live="status" class="w-full pl-3 pr-8 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all" @if(!$report_type || in_array($report_type, ['balance_sheet', 'income_statement', 'cash_flow', 'equity_change', 'loan_repayment', 'loan_disbursement', 'loan_settlement', 'deposit_interest_payment', 'deposit_withdrawal', 'deposit_placement', 'saving_closing'])) disabled @endif>
                            <option value="">SEMUA STATUS</option>
                            @foreach($this->statuses as $key => $label)
                                <option value="{{ $key }}">{{ strtoupper($label) }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($report_type === 'balance_sheet')
                    <div class="space-y-1" style="flex: 1 1 160px;">
                        <!-- Neraca dihitung kumulatif sampai tanggal akhir. -->
                    </div>
                    @else
                    <div class="space-y-1" style="flex: 1 1 160px;">
                        <label class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Dari Tanggal</label>
                        <input type="date" wire:model.live="date_start" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all">
                    </div>
                    @endif

                    <div class="space-y-1">
                        <label class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">{{ $report_type === 'balance_sheet' ? 'Per Tanggal' : 'Sampai Tanggal' }}</label>
                        <input type="date" wire:model.live="date_end" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all">
                    </div>
                </div>
            </div>

            <!-- Table Preview -->
            @if($report_type)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 uppercase text-[10px] tracking-widest font-extrabold text-slate-400">
                            @if(array_key_exists($report_type, $templateReports))
                                @foreach($this->templateReportColumns() as $column)
                                    <th class="px-6 py-4 {{ in_array($column, ['Plafond', 'Baki Debet', 'Baki Debet Awal', 'Pokok', 'Bunga', 'Denda', 'Pinalti', 'Total', 'Baki Debet Akhir', 'Provisi', 'Administrasi', 'Premi Asuransi', 'Bunga Diterima Dimuka', 'Nominal', 'Brutto', 'Pajak', 'Netto', 'Saldo']) ? 'text-right' : '' }}">{{ $column }}</th>
                                @endforeach
                            @elseif(in_array($report_type, ['savings', 'saving_nominative', 'saving_opening', 'saving_closing']))
                                <th class="px-6 py-4">NO. REKENING</th>
                                <th class="px-6 py-4">NO. CIF</th>
                                <th class="px-6 py-4">NASABAH</th>
                                <th class="px-6 py-4">PRODUK</th>
                                <th class="px-6 py-4">CABANG</th>
                                <th class="px-6 py-4 text-right">SALDO</th>
                                <th class="px-6 py-4">TGL STATUS</th>
                                <th class="px-6 py-4">STATUS</th>
                            @elseif(in_array($report_type, ['loans', 'loan_nominative']))
                                <th class="px-6 py-4">NO. PINJAMAN</th>
                                <th class="px-6 py-4">NO. CIF</th>
                                <th class="px-6 py-4">NASABAH</th>
                                <th class="px-6 py-4">REK. AUTODEBET</th>
                                <th class="px-6 py-4">PRODUK</th>
                                <th class="px-6 py-4">CABANG</th>
                                <th class="px-6 py-4 text-right">PLAFON</th>
                                <th class="px-6 py-4 text-right">BAKI DEBET</th>
                                <th class="px-6 py-4">STATUS</th>
                            @elseif(in_array($report_type, ['loan_repayment', 'loan_disbursement', 'loan_settlement']))
                                <th class="px-6 py-4">TANGGAL</th>
                                <th class="px-6 py-4">NO. PINJAMAN</th>
                                <th class="px-6 py-4">NO. CIF</th>
                                <th class="px-6 py-4">NASABAH</th>
                                <th class="px-6 py-4">PRODUK</th>
                                <th class="px-6 py-4">CABANG</th>
                                <th class="px-6 py-4">TIPE</th>
                                <th class="px-6 py-4 text-right">POKOK</th>
                                <th class="px-6 py-4 text-right">BUNGA</th>
                                <th class="px-6 py-4 text-right">DENDA</th>
                                <th class="px-6 py-4 text-right">TOTAL</th>
                            @elseif(in_array($report_type, ['deposits', 'deposit_nominative']))
                                <th class="px-6 py-4">NO. SIMPANAN BERJANGKA</th>
                                <th class="px-6 py-4">NO. CIF</th>
                                <th class="px-6 py-4">NASABAH</th>
                                <th class="px-6 py-4">REK. PEMBAYARAN BUNGA</th>
                                <th class="px-6 py-4">PRODUK</th>
                                <th class="px-6 py-4">CABANG</th>
                                <th class="px-6 py-4 text-right">NOMINAL</th>
                                <th class="px-6 py-4 text-right">BUNGA/BULAN</th>
                                <th class="px-6 py-4">TGL PENEMPATAN</th>
                                <th class="px-6 py-4">TGL JATUH TEMPO</th>
                                <th class="px-6 py-4">STATUS</th>
                            @elseif(in_array($report_type, ['deposit_interest_payment', 'deposit_withdrawal', 'deposit_placement']))
                                <th class="px-6 py-4">TANGGAL</th>
                                <th class="px-6 py-4">NO. DEPOSITO</th>
                                <th class="px-6 py-4">NO. CIF</th>
                                <th class="px-6 py-4">NASABAH</th>
                                <th class="px-6 py-4">PRODUK</th>
                                <th class="px-6 py-4">REK. PEMBAYARAN</th>
                                <th class="px-6 py-4">TIPE</th>
                                <th class="px-6 py-4 text-right">NOMINAL</th>
                                <th class="px-6 py-4">CHANNEL</th>
                            @elseif($report_type == 'cifs')
                                <th class="px-6 py-4">No. CIF</th>
                                <th class="px-6 py-4">Nama Lengkap</th>
                                <th class="px-6 py-4">Cabang</th>
                                <th class="px-6 py-4">NIK</th>
                                <th class="px-6 py-4">Status</th>
                            @elseif($report_type == 'assets')
                                <th class="px-6 py-4">No.</th>
                                <th class="px-6 py-4">Nomor Rekening/Seri</th>
                                <th class="px-6 py-4">Nama Aktiva</th>
                                <th class="px-6 py-4">Tanggal Perolehan</th>
                                <th class="px-6 py-4 text-right">Usia Pakai</th>
                                <th class="px-6 py-4">Tanggal Habis Buku</th>
                                <th class="px-6 py-4 text-right">Nilai Perolehan</th>
                                <th class="px-6 py-4 text-right">Nilai Buku Bulan lalu</th>
                                <th class="px-6 py-4 text-right">Nilai Penyusutan</th>
                                <th class="px-6 py-4 text-right">Akumulasi Penyusutan</th>
                                <th class="px-6 py-4 text-right">Nilai Buku Bulan Sekarang</th>
                            @elseif($report_type == 'asset_rentals')
                                <th class="px-6 py-4">NO. KONTRAK</th>
                                <th class="px-6 py-4">ASET</th>
                                <th class="px-6 py-4">PENYEWA</th>
                                <th class="px-6 py-4">CABANG</th>
                                <th class="px-6 py-4">PERIODE SEWA</th>
                                <th class="px-6 py-4 text-right">TARIF BULANAN</th>
                                <th class="px-6 py-4">PEMBAYARAN TERAKHIR</th>
                                <th class="px-6 py-4 text-right">NOMINAL PEMBAYARAN</th>
                                <th class="px-6 py-4">STATUS</th>
                            @elseif($report_type == 'audit')
                                <th class="px-6 py-4">Waktu</th>
                                <th class="px-6 py-4">Pengguna</th>
                                <th class="px-6 py-4">Aksi</th>
                                <th class="px-6 py-4">Deskripsi</th>
                            @elseif(in_array($report_type, ['balance_sheet', 'income_statement', 'cash_flow', 'equity_change']))
                                <th class="px-6 py-4">Section</th>
                                <th class="px-6 py-4">Akun</th>
                                <th class="px-6 py-4 text-right">Nominal</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($results as $row)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            @if(array_key_exists($report_type, $templateReports))
                                @foreach($this->templateReportRow($row, $loop->iteration) as $index => $cell)
                                    @php($column = $this->templateReportColumns()[$index] ?? '')
                                    <td class="px-6 py-4 text-xs font-bold text-slate-700 {{ in_array($column, ['Plafond', 'Baki Debet', 'Baki Debet Awal', 'Pokok', 'Bunga', 'Denda', 'Pinalti', 'Total', 'Baki Debet Akhir', 'Provisi', 'Administrasi', 'Premi Asuransi', 'Bunga Diterima Dimuka', 'Nominal', 'Brutto', 'Pajak', 'Netto', 'Saldo']) ? 'text-right' : '' }}">{{ is_numeric($cell) && !is_string($cell) && in_array($column, ['Plafond', 'Baki Debet', 'Baki Debet Awal', 'Pokok', 'Bunga', 'Denda', 'Pinalti', 'Total', 'Baki Debet Akhir', 'Provisi', 'Administrasi', 'Premi Asuransi', 'Bunga Diterima Dimuka', 'Nominal', 'Brutto', 'Pajak', 'Netto', 'Saldo']) ? number_format((float) $cell, 2, ',', '.') : strtoupper((string) $cell) }}</td>
                                @endforeach
                            @elseif(in_array($report_type, ['savings', 'saving_nominative', 'saving_opening', 'saving_closing']))
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->account_no) }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->cif->cif_no ?? '-') }}</td>
                                <td class="px-6 py-4 font-bold text-xs text-slate-700">{{ strtoupper($row->cif->name ?? '-') }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-wider">{{ strtoupper($row->product->name ?? '-') }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-wider">{{ strtoupper($row->branch->name ?? '-') }}</td>
                                <td class="px-6 py-4 text-right font-black text-sm text-slate-900">Rp {{ number_format($row->balance, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ ($report_type === 'saving_closing' ? $row->closed_at : $row->opened_at) ? ($report_type === 'saving_closing' ? \Carbon\Carbon::parse($row->closed_at)->format('d/m/Y') : $row->opened_at->format('d/m/Y')) : '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-[10px] font-bold uppercase tracking-wider">{{ strtoupper($row->status) }}</span>
                                </td>
                            @elseif(in_array($report_type, ['loans', 'loan_nominative']))
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->account_no) }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->cif->cif_no ?? '-') }}</td>
                                <td class="px-6 py-4 font-bold text-xs text-slate-700">{{ strtoupper($row->cif->name ?? '-') }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->savingAccount->account_no ?? '-') }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-wider">{{ strtoupper($row->product->name ?? '-') }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-wider">{{ strtoupper($row->branch->name ?? '-') }}</td>
                                <td class="px-6 py-4 text-right font-black text-sm text-slate-900">Rp {{ number_format($row->principal_amount, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-black text-sm text-slate-900">Rp {{ number_format($row->outstanding_total, 2, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-[10px] font-bold uppercase tracking-wider">{{ strtoupper($row->status) }}</span>
                                </td>
                            @elseif(in_array($report_type, ['loan_repayment', 'loan_disbursement', 'loan_settlement']))
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ $row->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->loanAccount->account_no ?? '-') }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->loanAccount->cif->cif_no ?? '-') }}</td>
                                <td class="px-6 py-4 font-bold text-xs text-slate-700">{{ strtoupper($row->loanAccount->cif->name ?? '-') }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-wider">{{ strtoupper($row->loanAccount->product->name ?? '-') }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-wider">{{ strtoupper($row->loanAccount->branch->name ?? '-') }}</td>
                                <td class="px-6 py-4"><span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-[10px] font-bold uppercase tracking-wider">{{ strtoupper($row->transaction_type) }}</span></td>
                                <td class="px-6 py-4 text-right font-black text-sm text-slate-900">Rp {{ number_format($row->amount_principal, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-black text-sm text-slate-900">Rp {{ number_format($row->amount_interest, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-black text-sm text-slate-900">Rp {{ number_format($row->amount_penalty, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-black text-sm text-slate-900">Rp {{ number_format($row->total_amount, 2, ',', '.') }}</td>
                            @elseif(in_array($report_type, ['deposits', 'deposit_nominative']))
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->account_no) }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->cif->cif_no ?? '-') }}</td>
                                <td class="px-6 py-4 font-bold text-xs text-slate-700">{{ strtoupper($row->cif->name ?? '-') }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->savingAccount->account_no ?? '-') }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-wider">{{ strtoupper($row->product->name ?? '-') }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-wider">{{ strtoupper($row->branch->name ?? '-') }}</td>
                                <td class="px-6 py-4 text-right font-black text-sm text-slate-900">Rp {{ number_format($row->amount, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-black text-sm text-slate-900">Rp {{ number_format($row->schedules->first()?->net_interest ?? 0, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ $row->placement_date ? $row->placement_date->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ $row->maturity_date ? $row->maturity_date->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-[10px] font-bold uppercase tracking-wider">{{ strtoupper($row->status) }}</span>
                                </td>
                            @elseif(in_array($report_type, ['deposit_interest_payment', 'deposit_withdrawal', 'deposit_placement']))
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ $row->transaction_date?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->account->account_no ?? '-') }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->account->cif->cif_no ?? '-') }}</td>
                                <td class="px-6 py-4 font-bold text-xs text-slate-700">{{ strtoupper($row->account->cif->name ?? '-') }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-wider">{{ strtoupper($row->account->product->name ?? '-') }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->account->savingAccount->account_no ?? '-') }}</td>
                                <td class="px-6 py-4"><span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-[10px] font-bold uppercase tracking-wider">{{ strtoupper($row->type) }}</span></td>
                                <td class="px-6 py-4 text-right font-black text-sm text-slate-900">Rp {{ number_format($row->amount, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-wider">{{ strtoupper($row->channel ?? '-') }}</td>
                            @elseif($report_type == 'cifs')
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->cif_no) }}</td>
                                <td class="px-6 py-4 font-bold text-xs text-slate-700">{{ strtoupper($row->name) }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-wider">{{ strtoupper($row->branch->name ?? '-') }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500">{{ $row->nik }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-[10px] font-bold uppercase tracking-wider">{{ strtoupper($row->status) }}</span>
                                </td>
                            @elseif($report_type == 'assets')
                                @foreach($this->assetInventoryRow($row, $loop->iteration) as $index => $cell)
                                    <td class="px-6 py-4 text-xs font-bold text-slate-700 {{ in_array($index, [4, 6, 7, 8, 9, 10], true) ? 'text-right' : '' }}">{{ in_array($index, [6, 7, 8, 9, 10], true) ? number_format((float) $cell, 2, ',', '.') : strtoupper((string) $cell) }}</td>
                                @endforeach
                            @elseif($report_type == 'asset_rentals')
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->contract_no) }}</td>
                                <td class="px-6 py-4">
                                    <p class="font-mono font-bold text-xs text-slate-900">{{ strtoupper($row->asset->asset_code ?? '-') }}</p>
                                    <p class="text-[10px] font-bold text-slate-500 uppercase">{{ strtoupper($row->asset->name ?? '-') }}</p>
                                </td>
                                <td class="px-6 py-4 font-bold text-xs text-slate-700">{{ strtoupper($row->rekanan->name ?? '-') }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-wider">{{ strtoupper($row->branch->name ?? '-') }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ $row->rental_start_date ? $row->rental_start_date->format('d/m/Y') : '-' }} - {{ $row->rental_end_date ? $row->rental_end_date->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-4 text-right font-black text-sm text-slate-900">Rp {{ number_format($row->monthly_rate, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">{{ $row->latestPaidBilling?->paid_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-6 py-4 text-right font-black text-sm text-slate-900">Rp {{ number_format($row->latestPaidBilling?->amount ?? 0, 2, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-[10px] font-bold uppercase tracking-wider">{{ strtoupper($row->status) }}</span>
                                </td>
                            @elseif($report_type == 'audit')
                                <td class="px-6 py-4">
                                    <p class="text-xs font-bold text-slate-900">{{ $row->created_at->format('d M Y') }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold">{{ $row->created_at->format('H:i:s') }}</p>
                                </td>
                                <td class="px-6 py-4 font-bold text-xs text-slate-700">{{ strtoupper($row->user->name ?? 'SYSTEM') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-[10px] font-bold uppercase tracking-wider">{{ strtoupper($row->action) }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 font-medium max-w-sm">{{ strtoupper($row->description ?? '') }}</td>
                            @elseif(in_array($report_type, ['balance_sheet', 'income_statement', 'cash_flow', 'equity_change']))
                                <td class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $row['section'] ?? '-' }}</td>
                                <td class="px-6 py-4 text-xs font-bold text-slate-900">{{ $row['account'] ?? '-' }}</td>
                                <td class="px-6 py-4 text-right font-black text-sm text-slate-900">Rp {{ number_format((float) ($row['amount'] ?? 0), 2, ',', '.') }}</td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="px-6 py-16 text-center">
                                <span class="material-symbols-outlined text-4xl text-slate-300 mb-3">inbox</span>
                                <p class="text-sm font-bold text-slate-500">Tidak ada data untuk filter tersebut.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(method_exists($results, 'links'))
            <div class="p-6 bg-slate-50/50 border-t border-slate-100">
                {{ $results->links() }}
            </div>
            @endif

            @else
            <div class="px-6 py-32 text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner border border-slate-100">
                    <span class="material-symbols-outlined text-4xl text-slate-300">find_in_page</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Pilih Jenis Laporan</h3>
                <p class="text-sm font-medium text-slate-500 max-w-md mx-auto">Silakan pilih jenis laporan pada menu dropdown di atas untuk melihat preview data dan mengunduh laporan.</p>
            </div>
            @endif
        </div>
    </div>
</div>
