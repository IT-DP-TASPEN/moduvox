<div class="p-0">
    <x-header title="Daftar Persetujuan" subtitle="Tinjau dan proses permintaan perubahan data" :user="$user" :role="$role">
        <x-slot:actions>
            <div class="flex items-center space-x-2">
                <select wire:model.live="statusFilter" class="bg-slate-50 border border-slate-200 rounded-xl text-xs px-4 py-2 font-bold text-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition-all">
                    <option value="PENDING">Menunggu (Pending)</option>
                    <option value="APPROVED">Disetujui</option>
                    <option value="REJECTED">Ditolak</option>
                </select>
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-10">
        @if (session()->has('success'))
        <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-[2rem] border border-emerald-100 flex items-center mb-10 animate-fade-in shadow-sm">
            <span class="material-symbols-outlined mr-3 text-lg">check_circle</span>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
        @endif
        @if (session()->has('error'))
        <div class="bg-rose-50 text-rose-700 px-6 py-4 rounded-[2rem] border border-rose-100 flex items-center mb-10 animate-fade-in shadow-sm">
            <span class="material-symbols-outlined mr-3 text-lg">error</span>
            <span class="font-bold text-sm">{{ session('error') }}</span>
        </div>
        @endif
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 text-[10px] uppercase tracking-[0.2em] font-extrabold">
                        <th class="px-10 py-6">Permohonan</th>
                        <th class="px-6 py-6">Ringkasan</th>
                        <th class="px-6 py-6">Oleh</th>
                        <th class="px-6 py-6">Waktu Pengajuan</th>
                        <th class="px-6 py-6">Status</th>
                        <th class="px-10 py-6 text-right">Tinjau</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requests as $req)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-10 py-8">
                            <div class="flex items-center space-x-4">
                                @php
                                    $actionColor = [
                                        'CREATE' => 'bg-emerald-100 text-emerald-600',
                                        'UPDATE' => 'bg-blue-100 text-blue-600',
                                        'DELETE' => 'bg-rose-100 text-rose-600'
                                    ][$req->action] ?? 'bg-slate-100 text-slate-600';
                                @endphp
                                <div class="w-12 h-12 rounded-2xl {{ $actionColor }} flex items-center justify-center shadow-sm">
                                    <span class="material-symbols-outlined text-xl">
                                        {{ $req->action == 'CREATE' ? 'add_circle' : ($req->action == 'UPDATE' ? 'edit_square' : 'delete_forever') }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm leading-tight mb-1 uppercase tracking-tight">{{ $this->approvalTitle($req) }}</p>
                                    <p class="text-[9px] font-black uppercase tracking-[0.2em] {{ $actionColor }} px-2 py-0.5 rounded-md border border-current/10 w-fit">{{ $req->action }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-8 max-w-md">
                            <p class="text-xs font-bold text-slate-700 leading-relaxed">{{ $this->compactSummary($req) ?: 'Lihat detail permohonan' }}</p>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-2">{{ $req->module_key }}</p>
                        </td>
                        <td class="px-6 py-8">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold uppercase">
                                    {{ substr($req->requester->name, 0, 1) }}
                                </div>
                                <span class="text-xs font-bold text-slate-900">{{ $req->requester->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-8">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest italic">{{ $req->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-6 py-8">
                            @php
                                $statusStyle = [
                                    'PENDING' => 'bg-amber-50 text-amber-600 border-amber-100',
                                    'APPROVED' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                    'REJECTED' => 'bg-rose-50 text-rose-600 border-rose-100'
                                ][$req->status] ?? 'bg-slate-50 text-slate-400 border-slate-100';
                            @endphp
                            <span class="px-3 py-1.5 rounded-xl border {{ $statusStyle }} text-[9px] font-black uppercase tracking-widest">
                                {{ $req->status }}
                            </span>
                        </td>
                        <td class="px-10 py-8 text-right">
                            <button wire:click="viewRequest({{ $req->id }})" class="w-10 h-10 flex items-center justify-center bg-slate-900 text-white rounded-xl hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                                <span class="material-symbols-outlined text-sm">visibility</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                            <td colspan="6" class="px-10 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <span class="material-symbols-outlined text-5xl text-slate-100 mb-4">move_to_inbox</span>
                                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Tidak ada antrean persetujuan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($requests->hasPages())
            <div class="px-10 py-6 bg-slate-50 border-t border-slate-200">
                {{ $requests->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Review Modal -->
    <div x-data="{ open: @entangle('showRequestModal') }" x-show="open" 
        class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm"
        x-transition x-cloak>
        <div @click.away="open = false" class="bg-white w-full max-w-4xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-slide-up">
            @if($selectedRequest)
            <div class="pearl-gradient p-10 text-white flex justify-between items-center relative">
                <div class="relative z-10">
                    <h3 class="text-2xl font-headline font-bold tracking-tight">Detail Permohonan</h3>
                            <p class="text-white/60 text-[10px] font-extrabold uppercase tracking-[0.2em] mt-1">{{ $this->approvalTitle($selectedRequest) }} | ID #{{ $selectedRequest->id }}</p>
                </div>
                <button @click="open = false" class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors relative z-10">
                    <span class="material-symbols-outlined">close</span>
                </button>
                <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            </div>
            
            <div class="p-10 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-8 mb-10 pb-10 border-b border-slate-100">
                    <div class="space-y-4">
                        <label class="text-[10px] uppercase tracking-widest font-black text-slate-400">Pengaju</label>
                        <div class="flex items-center space-x-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold">{{ substr($selectedRequest->requester->name, 0, 1) }}</div>
                            <span class="font-bold text-slate-900">{{ $selectedRequest->requester->name }}</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <label class="text-[10px] uppercase tracking-widest font-black text-slate-400">Jenis Aksi</label>
                        <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl flex items-center justify-between">
                            <span class="font-black text-indigo-600 tracking-widest">{{ $selectedRequest->action }}</span>
                            <span class="material-symbols-outlined text-indigo-400">bolt</span>
                        </div>
                    </div>
                </div>

                <!-- Data Comparison/Details -->
                <div class="space-y-6">
                    <label class="text-[10px] uppercase tracking-widest font-black text-slate-400">Ringkasan Permohonan</label>
                    
                    @php
                        $dataBefore = is_array($selectedRequest->data_before) ? $selectedRequest->data_before : (json_decode($selectedRequest->data_before, true) ?: []);
                        $dataAfter = is_array($selectedRequest->data_after) ? $selectedRequest->data_after : (json_decode($selectedRequest->data_after, true) ?: []);
                        $technicalBefore = $this->approvalTechnicalRows($selectedRequest, 'before');
                        $technicalAfter = $this->approvalTechnicalRows($selectedRequest, 'after');
                    @endphp

                    <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($this->approvalSummary($selectedRequest) as $row)
                            <div class="bg-white rounded-2xl border border-slate-100 p-4">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $row['label'] }}</p>
                                <p class="text-sm font-black text-slate-900 leading-snug">{{ $row['value'] ?: '-' }}</p>
                            </div>
                        @endforeach
                    </div>

                    @if($selectedRequest->module_key === 'journals')
                        @php
                            $journalRows = $this->approvalJournalEntries($selectedRequest);
                            $journalTotals = $this->approvalJournalTotals($selectedRequest);
                        @endphp
                        <div class="space-y-3">
                            <label class="text-[10px] uppercase tracking-widest font-black text-slate-400">Rincian Jurnal</label>
                            <div class="bg-white border border-slate-200 rounded-[2rem] overflow-hidden shadow-sm">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 text-[10px] uppercase tracking-[0.2em] font-extrabold">
                                            <th class="px-6 py-4 w-12">#</th>
                                            <th class="px-6 py-4">COA</th>
                                            <th class="px-6 py-4 text-right">Debit</th>
                                            <th class="px-6 py-4 text-right">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse($journalRows as $row)
                                            <tr>
                                                <td class="px-6 py-4 text-[10px] font-black text-slate-400">{{ $row['no'] }}</td>
                                                <td class="px-6 py-4 text-xs font-black text-slate-900">{{ $row['coa'] }}</td>
                                                <td class="px-6 py-4 text-right text-xs font-black text-emerald-600">{{ $row['debit_display'] }}</td>
                                                <td class="px-6 py-4 text-right text-xs font-black text-rose-600">{{ $row['credit_display'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-8 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tidak ada baris jurnal</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-slate-900 text-white">
                                            <td colspan="2" class="px-6 py-4 text-[10px] font-black uppercase tracking-widest">Total</td>
                                            <td class="px-6 py-4 text-right text-xs font-black">{{ number_format($journalTotals['debit'], 2, ',', '.') }}</td>
                                            <td class="px-6 py-4 text-right text-xs font-black">{{ number_format($journalTotals['credit'], 2, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endif

                    <label class="text-[10px] uppercase tracking-widest font-black text-slate-400">Detail Teknis</label>
                    
                    @if($selectedRequest->module_key === 'shu.distributions')
                    <div class="space-y-6">
                        <div class="bg-slate-900 p-8 rounded-[2rem] text-white flex justify-between items-center">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Periode SHU</p>
                                <p class="text-3xl font-black">{{ $dataAfter['periode'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Laba yang Dibagikan</p>
                                <p class="text-3xl font-black text-emerald-400">Rp {{ number_format($dataAfter['total_laba'], 2, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-[2rem] overflow-hidden shadow-sm">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 text-[10px] uppercase tracking-[0.2em] font-extrabold">
                                        <th class="px-8 py-5">Kriteria</th>
                                        <th class="px-6 py-5 text-center">%</th>
                                        <th class="px-6 py-5 text-right">Total SHU</th>
                                        <th class="px-6 py-5 text-center">Jumlah Penerima</th>
                                        <th class="px-8 py-5 text-right">Per Orang</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($dataAfter['details'] ?? [] as $detail)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-8 py-4 font-bold text-sm text-slate-900">{{ ucwords(strtolower($detail['kriteria'])) }}</td>
                                        <td class="px-6 py-4 text-center font-bold text-xs text-slate-600">{{ format_percent($detail['persentase']) }}</td>
                                        <td class="px-6 py-4 text-right font-black text-sm text-slate-900">Rp {{ number_format($detail['shu'], 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">{{ $detail['jumlah_orang'] }} Orang</span>
                                        </td>
                                        <td class="px-8 py-4 text-right font-black text-sm text-emerald-600">Rp {{ number_format($detail['per_orang'], 2, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
	                        </div>
	                    </div>
	                    @elseif($selectedRequest->module_key === 'savings.distribution')
	                    @php
	                        $distributionRows = $this->approvalSavingDistributionRows($selectedRequest);
	                        $distributionTotals = $this->approvalSavingDistributionTotals($selectedRequest);
	                    @endphp
	                    <div class="space-y-5">
	                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
	                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
	                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Baris CSV</p>
	                                <p class="text-lg font-black text-slate-900">{{ number_format($distributionTotals['count'], 0, ',', '.') }}</p>
	                            </div>
	                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
	                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Rekening Unik</p>
	                                <p class="text-lg font-black text-slate-900">{{ number_format($distributionTotals['unique_accounts'], 0, ',', '.') }}</p>
	                            </div>
	                            <div class="{{ $distributionTotals['duplicate_accounts'] > 0 ? 'bg-amber-50 border-amber-100' : 'bg-slate-50 border-slate-100' }} border rounded-2xl p-4">
	                                <p class="text-[9px] font-black {{ $distributionTotals['duplicate_accounts'] > 0 ? 'text-amber-600' : 'text-slate-400' }} uppercase tracking-widest mb-1">Rekening Duplikat</p>
	                                <p class="text-lg font-black {{ $distributionTotals['duplicate_accounts'] > 0 ? 'text-amber-700' : 'text-slate-900' }}">{{ number_format($distributionTotals['duplicate_accounts'], 0, ',', '.') }}</p>
	                            </div>
	                            <div class="{{ $distributionTotals['missing_accounts'] > 0 ? 'bg-rose-50 border-rose-100' : 'bg-slate-50 border-slate-100' }} border rounded-2xl p-4">
	                                <p class="text-[9px] font-black {{ $distributionTotals['missing_accounts'] > 0 ? 'text-rose-600' : 'text-slate-400' }} uppercase tracking-widest mb-1">Tidak Ditemukan</p>
	                                <p class="text-lg font-black {{ $distributionTotals['missing_accounts'] > 0 ? 'text-rose-700' : 'text-slate-900' }}">{{ number_format($distributionTotals['missing_accounts'], 0, ',', '.') }}</p>
	                            </div>
	                        </div>

	                        <div class="bg-white border border-slate-200 rounded-[2rem] overflow-hidden shadow-sm">
	                            <div class="max-h-96 overflow-auto custom-scrollbar">
	                                <table class="w-full text-left">
	                                    <thead class="sticky top-0 z-10">
	                                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 text-[10px] uppercase tracking-[0.2em] font-extrabold">
	                                            <th class="px-5 py-4 w-14">#</th>
	                                            <th class="px-5 py-4">Rekening</th>
	                                            <th class="px-5 py-4">Anggota</th>
	                                            <th class="px-5 py-4 text-right">Nominal</th>
	                                            <th class="px-5 py-4">Status Real</th>
	                                            <th class="px-5 py-4 text-right">Saldo Efektif</th>
	                                            <th class="px-5 py-4">Catatan</th>
	                                        </tr>
	                                    </thead>
	                                    <tbody class="divide-y divide-slate-100">
	                                        @foreach($distributionRows as $row)
	                                        <tr class="{{ $row['is_missing'] ? 'bg-rose-50/50' : ($row['is_duplicate'] ? 'bg-amber-50/40' : 'hover:bg-slate-50/50') }} transition-colors">
	                                            <td class="px-5 py-4 text-[10px] font-black text-slate-400">{{ $row['no'] }}</td>
	                                            <td class="px-5 py-4">
	                                                <p class="text-xs font-mono font-black text-slate-900">{{ $row['account_no'] }}</p>
	                                                @if($row['is_duplicate'])
	                                                    <span class="inline-flex mt-2 px-2 py-1 rounded-lg bg-amber-100 text-amber-700 text-[9px] font-black uppercase tracking-widest">Duplikat x{{ $row['duplicate_count'] }}</span>
	                                                @endif
	                                            </td>
	                                            <td class="px-5 py-4 text-xs font-bold text-slate-700">{{ $row['member'] }}</td>
	                                            <td class="px-5 py-4 text-right text-xs font-black text-slate-900">{{ $row['amount_display'] }}</td>
	                                            <td class="px-5 py-4">
	                                                <span class="px-2.5 py-1 rounded-lg {{ $row['is_missing'] ? 'bg-rose-100 text-rose-700' : 'bg-emerald-50 text-emerald-700' }} text-[9px] font-black uppercase tracking-widest">{{ $row['status'] }}</span>
	                                            </td>
	                                            <td class="px-5 py-4 text-right text-xs font-black text-slate-700">{{ $row['effective_balance_display'] }}</td>
	                                            <td class="px-5 py-4 text-xs font-bold text-slate-500">{{ $row['note'] ?: '-' }}</td>
	                                        </tr>
	                                        @endforeach
	                                    </tbody>
	                                    <tfoot>
	                                        <tr class="bg-slate-900 text-white">
	                                            <td colspan="3" class="px-5 py-4 text-[10px] font-black uppercase tracking-widest">Total Distribusi</td>
	                                            <td class="px-5 py-4 text-right text-xs font-black">Rp {{ number_format($distributionTotals['amount'], 2, ',', '.') }}</td>
	                                            <td colspan="3" class="px-5 py-4"></td>
	                                        </tr>
	                                    </tfoot>
	                                </table>
	                            </div>
	                        </div>
	                    </div>
	                    @elseif($selectedRequest->module_key === 'asset-rentals.index' && !empty($dataAfter['bulk_payments']))
	                    @php
	                        $bulkRows = $this->approvalBulkPaymentRows($selectedRequest);
                        $bulkTotals = $this->approvalBulkPaymentTotals($selectedRequest);
                    @endphp
                    <div class="space-y-6">
                        <div class="bg-white border border-slate-200 rounded-[2rem] overflow-hidden shadow-sm">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 text-[10px] uppercase tracking-[0.2em] font-extrabold">
                                        <th class="px-6 py-4 w-16">Baris</th>
                                        <th class="px-6 py-4">No Kontrak</th>
                                        <th class="px-6 py-4">Periode</th>
                                        <th class="px-6 py-4 text-right">Nominal</th>
                                        <th class="px-6 py-4">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($bulkRows as $row)
                                        <tr>
                                            <td class="px-6 py-4 text-[10px] font-black text-slate-400">{{ $row['row'] }}</td>
                                            <td class="px-6 py-4 text-xs font-mono font-black text-slate-900">{{ $row['contract_no'] }}</td>
                                            <td class="px-6 py-4 text-xs font-black text-slate-900">{{ $row['billing_period'] }}</td>
                                            <td class="px-6 py-4 text-right text-xs font-black text-emerald-600">Rp {{ number_format($row['amount'], 2, ',', '.') }}</td>
                                            <td class="px-6 py-4 text-xs font-bold text-slate-500">{{ $row['note'] ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-slate-900 text-white">
                                        <td colspan="3" class="px-6 py-4 text-[10px] font-black uppercase tracking-widest">{{ number_format($bulkTotals['count'], 0, ',', '.') }} Tagihan</td>
                                        <td class="px-6 py-4 text-right text-xs font-black">Rp {{ number_format($bulkTotals['amount'], 2, ',', '.') }}</td>
                                        <td class="px-6 py-4"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @elseif($selectedRequest->action == 'UPDATE')
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <p class="text-center py-2 bg-slate-50 rounded-lg text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Data Lama</p>
                            <div class="bg-slate-50/50 p-6 rounded-3xl border border-slate-100 space-y-4">
                                @forelse($technicalBefore as $row)
                                <div class="flex justify-between items-start border-b border-slate-100 pb-2 space-x-4">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase whitespace-nowrap mt-0.5">{{ $row['label'] }}</span>
                                    <span class="text-xs font-bold text-slate-600 text-right break-all">{{ $row['value'] }}</span>
                                </div>
                                @empty
                                <p class="text-[10px] text-slate-400 font-bold italic">Tidak ada data sebelumnya</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="space-y-3">
                            <p class="text-center py-2 bg-blue-50 rounded-lg text-[9px] font-black text-blue-400 uppercase tracking-[0.2em]">Rencana Data Baru</p>
                            <div class="bg-blue-50/20 p-6 rounded-3xl border border-blue-100 space-y-4">
                                @forelse($technicalAfter as $row)
                                @php $isChanged = (($dataAfter[$row['key']] ?? null) != ($dataBefore[$row['key']] ?? null)); @endphp
                                <div class="flex justify-between items-start border-b {{ $isChanged ? 'border-blue-200' : 'border-slate-100' }} pb-2 space-x-4">
                                    <span class="text-[10px] font-bold {{ $isChanged ? 'text-blue-600' : 'text-slate-400' }} uppercase whitespace-nowrap mt-0.5">{{ $row['label'] }}</span>
                                    <span class="text-xs font-black {{ $isChanged ? 'text-blue-700 underline decoration-blue-200 decoration-2 underline-offset-4' : 'text-slate-600' }} text-right break-all">{{ $row['value'] }}</span>
                                </div>
                                @empty
                                <p class="text-[10px] text-slate-400 font-bold italic">Tidak ada perubahan data</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100 grid grid-cols-2 gap-x-12 gap-y-6">
                        @foreach(($technicalAfter ?: $technicalBefore) as $row)
                        <div class="flex justify-between items-start border-b border-white pb-3 space-x-4">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest whitespace-nowrap mt-0.5">{{ $row['label'] }}</span>
                            <span class="text-sm font-black text-slate-900 text-right break-all">{{ $row['value'] }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                @if($selectedRequest->status == 'PENDING')
                <div class="mt-12 pt-10 border-t border-slate-100 space-y-8">
                    <div class="space-y-4">
                        <label class="text-[10px] uppercase tracking-widest font-black text-slate-400 ml-1">Alasan Penolakan (Wajib jika menolak)</label>
                        <textarea wire:model="rejectionReason" class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-[2rem] focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-medium text-sm text-slate-900" rows="3" placeholder="Berikan alasan mengapa permohonan ini ditolak..."></textarea>
                        @error('rejectionReason') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center space-x-4">
                        <button wire:click="reject({{ $selectedRequest->id }})" 
                                wire:loading.attr="disabled"
                                wire:target="reject, approve"
                                class="flex-1 py-5 bg-white border-2 border-slate-100 hover:border-rose-500 hover:bg-rose-50 text-slate-400 hover:text-rose-600 rounded-[2rem] font-bold transition-all text-xs uppercase tracking-[0.2em] disabled:opacity-50">
                            <span wire:loading.remove wire:target="reject">Tolak Permohonan</span>
                            <span wire:loading wire:target="reject">Memproses...</span>
                        </button>
                        <button wire:click="approve({{ $selectedRequest->id }})" 
                                wire:loading.attr="disabled"
                                wire:target="reject, approve"
                                class="flex-[2] py-5 bg-slate-900 hover:shadow-2xl hover:shadow-slate-900/40 text-white rounded-[2rem] font-black transition-all text-xs uppercase tracking-[0.2em] flex items-center justify-center space-x-3 disabled:opacity-50">
                            <span wire:loading.remove wire:target="approve" class="flex items-center space-x-3">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                <span>Setujui & Terapkan Data</span>
                            </span>
                            <span wire:loading wire:target="approve" class="flex items-center space-x-3">
                                <span class="material-symbols-outlined text-sm animate-spin">cycle</span>
                                <span>Memproses...</span>
                            </span>
                        </button>
                    </div>
                </div>
                @else
                <div class="mt-10 p-8 rounded-[2rem] {{ $selectedRequest->status == 'APPROVED' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }} border border-current/10">
                    <p class="text-[10px] font-black uppercase tracking-widest mb-2 italic">Histori Pemrosesan</p>
                    <p class="text-xs font-bold">Diproses oleh: {{ $selectedRequest->processor->name ?? 'System' }} pada {{ $selectedRequest->updated_at->format('d M Y H:i') }}</p>
                    @if($selectedRequest->reason)
                    <p class="mt-4 p-4 bg-white/50 rounded-xl text-xs font-medium italic">"{{ $selectedRequest->reason }}"</p>
                    @endif
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
