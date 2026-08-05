@extends('layouts.app')

@section('title', 'Status Jurnal API FinCloud')
@section('page-title', 'Monitor Jurnal API')

@section('content')
<div x-data="journalDetail()" class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Log Jurnal FinCloud</h3>
                <p class="text-sm text-gray-500">Memantau status pengiriman jurnal otomatis ke Core Banking FinCloud.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('api-journals.export', request()->all() + ['format' => 'excel']) }}" class="px-4 py-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-lg text-sm font-medium transition inline-flex items-center gap-2">
                    <i class="fa-solid fa-file-excel"></i> Export Excel
                </a>
                <a href="{{ route('api-journals.export', request()->all() + ['format' => 'pdf']) }}" target="_blank" class="px-4 py-2 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg text-sm font-medium transition inline-flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>

        {{-- Filter Panel --}}
        <form action="{{ route('api-journals.index') }}" method="GET" class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Cabang</label>
                    <select name="kantor" class="w-full border border-gray-300 bg-white px-3 py-2 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Cabang</option>
                        @foreach($kantors as $k)
                            <option value="{{ $k->kode }}" {{ request('kantor') == $k->kode ? 'selected' : '' }}>
                                {{ $k->kode }} - {{ $k->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Bulan</label>
                    <select name="bulan" class="w-full border border-gray-300 bg-white px-3 py-2 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Bulan</option>
                        @php
                            $bulans = [
                                1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
                                5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
                                9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
                            ];
                        @endphp
                        @foreach($bulans as $num => $name)
                            <option value="{{ $num }}" {{ request('bulan') == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun</label>
                    <select name="tahun" class="w-full border border-gray-300 bg-white px-3 py-2 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Tahun</option>
                        @for($y = date('Y'); $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                    <select name="state" class="w-full border border-gray-300 bg-white px-3 py-2 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        @foreach($states as $state)
                            <option value="{{ $state->value }}" {{ request('state') == $state->value ? 'selected' : '' }}>{{ $state->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Reff ID / Core Reff" class="w-full border border-gray-300 bg-white px-3 py-2 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <a href="{{ route('api-journals.index') }}" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 transition">Reset</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Terapkan Filter</button>
            </div>
        </form>

        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-check mt-0.5"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif
        
        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-xl">
                    <i class="fa-solid fa-list-ol"></i>
                </div>
                <div>
                    <div class="text-xs text-blue-600 font-semibold uppercase">Total Jurnal (Sesuai Filter)</div>
                    <div class="text-2xl font-bold text-blue-800">{{ number_format($totalJournals, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 text-xl">
                    <i class="fa-solid fa-rupiah-sign"></i>
                </div>
                <div>
                    <div class="text-xs text-emerald-600 font-semibold uppercase">Total Amount (Sesuai Filter)</div>
                    <div class="text-2xl font-bold text-emerald-800">Rp {{ number_format($totalAmount, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50 border-y border-gray-200">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Reff ID</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Cabang</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Batch</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Amount (Rp)</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Core Reff</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($journals as $journal)
                        @php
                            // Extract Cabang from reff_id (format: IV-KK...)
                            $cabangCode = substr($journal->reff_id, 3, 2);
                            $cabangName = $kantors->where('kode', $cabangCode)->first()->nama ?? 'Unknown';
                            
                            $payloadAmount = 0;
                            if(isset($journal->payload['amount'])) {
                                $payloadAmount = (float) str_replace(',', '.', $journal->payload['amount']);
                            }
                        @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-4 font-mono font-medium text-gray-900">{{ $journal->reff_id }}</td>
                        <td class="px-4 py-4">
                            <div class="font-medium text-gray-900">{{ $cabangCode }}</div>
                            <div class="text-xs text-gray-500">{{ $cabangName }}</div>
                        </td>
                        <td class="px-4 py-4 text-blue-600 font-mono text-xs">
                            <a href="{{ route('penyusutan.show', $journal->batch_id) }}" class="hover:underline" title="{{ $journal->batch->periode_label ?? '' }}">
                                #{{ $journal->batch_id }}
                            </a>
                        </td>
                        <td class="px-4 py-4 text-right font-medium text-gray-900">
                            {{ number_format($payloadAmount, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-4">
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $journal->state->color() }}-100 text-{{ $journal->state->color() }}-700">
                                {{ $journal->state->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-4 font-mono text-xs text-gray-500">{{ $journal->core_reff ?? '-' }}</td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Tombol Detail --}}
                                <button 
                                    @click="openDetail({{ $journal->id }})" 
                                    class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-medium transition inline-flex items-center gap-1.5"
                                    title="Lihat Detail Request & Response"
                                >
                                    <i class="fa-solid fa-eye"></i> Detail
                                </button>

                                {{-- Tombol Retry --}}
                                @if(in_array($journal->state->value, ['FAILED', 'DRAFT']))
                                <form action="{{ route('api-journals.retry', $journal->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg text-xs font-medium transition inline-flex items-center gap-1.5" onclick="return confirm('Retry pengiriman jurnal ini?')">
                                        <i class="fa-solid fa-rotate-right"></i> Retry
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            Belum ada data pengiriman jurnal.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $journals->links() }}
        </div>
    </div>

    {{-- ==================== MODAL DETAIL ==================== --}}
    <div 
        x-show="showModal" 
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="showModal = false"
    >
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showModal = false"></div>

        {{-- Scroll wrapper --}}
        <div class="flex items-start justify-center min-h-full p-4 py-8">

        {{-- Modal Content --}}
        <div 
            x-show="showModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl flex flex-col"
        >
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fa-solid fa-file-code text-blue-500"></i>
                        Detail Jurnal API
                    </h3>
                    <p class="text-sm text-gray-500 mt-0.5" x-text="'Reff: ' + (detail?.reff_id ?? '-')"></p>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition p-1 rounded-lg hover:bg-gray-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="px-6 py-5 space-y-6">
                
                {{-- Loading --}}
                <template x-if="loading">
                    <div class="flex items-center justify-center py-16">
                        <div class="flex flex-col items-center gap-3">
                            <i class="fa-solid fa-spinner fa-spin text-2xl text-blue-500"></i>
                            <span class="text-sm text-gray-500">Memuat data...</span>
                        </div>
                    </div>
                </template>

                <template x-if="!loading && detail">
                    <div class="space-y-6">
                        {{-- Info Ringkasan --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-xs text-gray-400 font-medium uppercase mb-1">Status</div>
                                <span 
                                    class="px-2.5 py-1 text-xs font-medium rounded-full"
                                    :class="`bg-${detail.state_color}-100 text-${detail.state_color}-700`"
                                    x-text="detail.state_label"
                                ></span>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-xs text-gray-400 font-medium uppercase mb-1">Core Reff</div>
                                <div class="text-sm font-mono font-medium text-gray-800" x-text="detail.core_reff ?? '-'"></div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-xs text-gray-400 font-medium uppercase mb-1">Dibuat</div>
                                <div class="text-sm text-gray-800" x-text="detail.created_at"></div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-xs text-gray-400 font-medium uppercase mb-1">Terakhir Update</div>
                                <div class="text-sm text-gray-800" x-text="detail.updated_at"></div>
                            </div>
                        </div>

                        {{-- Payload Request (dari journal) --}}
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="flex items-center justify-center w-7 h-7 bg-blue-100 rounded-lg">
                                    <i class="fa-solid fa-arrow-up-from-bracket text-blue-600 text-xs"></i>
                                </span>
                                <h4 class="font-semibold text-gray-800">Request Body (Payload ke API Core)</h4>
                            </div>
                            <div class="bg-gray-900 rounded-xl p-4 overflow-x-auto">
                                <pre class="text-green-400 text-xs font-mono whitespace-pre-wrap leading-relaxed" x-text="formatJson(detail.payload)"></pre>
                            </div>
                        </div>

                        {{-- Response Body (dari journal) --}}
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="flex items-center justify-center w-7 h-7 bg-emerald-100 rounded-lg">
                                    <i class="fa-solid fa-arrow-down-to-bracket text-emerald-600 text-xs"></i>
                                </span>
                                <h4 class="font-semibold text-gray-800">Response Body (Balasan dari API Core)</h4>
                            </div>
                            <template x-if="detail.response_body">
                                <div class="bg-gray-900 rounded-xl p-4 overflow-x-auto">
                                    <pre class="text-amber-400 text-xs font-mono whitespace-pre-wrap leading-relaxed" x-text="formatJson(detail.response_body)"></pre>
                                </div>
                            </template>
                            <template x-if="!detail.response_body">
                                <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-400 italic">
                                    Belum ada response dari API Core.
                                </div>
                            </template>
                        </div>

                        {{-- Log Riwayat Pengiriman --}}
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="flex items-center justify-center w-7 h-7 bg-purple-100 rounded-lg">
                                    <i class="fa-solid fa-clock-rotate-left text-purple-600 text-xs"></i>
                                </span>
                                <h4 class="font-semibold text-gray-800">Riwayat Pengiriman API</h4>
                            </div>
                            
                            <template x-if="detail.logs && detail.logs.length > 0">
                                <div class="space-y-4">
                                    <template x-for="(log, index) in detail.logs" :key="log.id">
                                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                                            {{-- Log Header --}}
                                            <div 
                                                class="flex items-center justify-between px-4 py-3 bg-gray-50 cursor-pointer hover:bg-gray-100 transition"
                                                @click="log._expanded = !log._expanded"
                                            >
                                                <div class="flex items-center gap-3">
                                                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded"
                                                        :class="log.http_status >= 200 && log.http_status < 300 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                                        x-text="log.method + ' ' + log.http_status"
                                                    ></span>
                                                    <span class="text-xs text-gray-500 font-mono truncate max-w-xs" x-text="log.endpoint"></span>
                                                    <span class="text-xs text-gray-400" x-text="log.duration_ms + 'ms'"></span>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span class="text-xs text-gray-400" x-text="log.created_at"></span>
                                                    <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform" :class="log._expanded ? 'rotate-180' : ''"></i>
                                                </div>
                                            </div>

                                            {{-- Log Body (expandable) --}}
                                            <div x-show="log._expanded" x-transition class="border-t border-gray-200">
                                                <div class="grid md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                                                    {{-- Request --}}
                                                    <div class="p-4">
                                                        <div class="text-xs font-semibold text-gray-500 uppercase mb-2 flex items-center gap-1.5">
                                                            <i class="fa-solid fa-arrow-right text-blue-400"></i> Request Body
                                                        </div>
                                                        <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto max-h-64 overflow-y-auto">
                                                            <pre class="text-green-400 text-xs font-mono whitespace-pre-wrap leading-relaxed" x-text="formatJson(log.request_payload)"></pre>
                                                        </div>
                                                    </div>
                                                    {{-- Response --}}
                                                    <div class="p-4">
                                                        <div class="text-xs font-semibold text-gray-500 uppercase mb-2 flex items-center gap-1.5">
                                                            <i class="fa-solid fa-arrow-left text-amber-400"></i> Response Body
                                                        </div>
                                                        <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto max-h-64 overflow-y-auto">
                                                            <pre class="text-amber-400 text-xs font-mono whitespace-pre-wrap leading-relaxed" x-text="formatJson(log.response_payload)"></pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="!detail.logs || detail.logs.length === 0">
                                <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-400 italic">
                                    Belum ada riwayat pengiriman.
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
                <button @click="showModal = false" class="px-5 py-2 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-xl text-sm font-medium transition">
                    Tutup
                </button>
            </div>
        </div>
        </div> {{-- end scroll wrapper --}}
    </div>
</div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@push('scripts')
<script>
    function journalDetail() {
        return {
            showModal: false,
            loading: false,
            detail: null,

            async openDetail(id) {
                this.showModal = true;
                this.loading = true;
                this.detail = null;

                try {
                    const response = await fetch(`/api-journals/${id}/detail`);
                    if (!response.ok) throw new Error('Gagal memuat data');
                    const data = await response.json();
                    
                    // Add _expanded property to logs
                    if (data.logs) {
                        data.logs = data.logs.map((log, index) => ({
                            ...log,
                            _expanded: index === 0 // expand first log by default
                        }));
                    }
                    
                    this.detail = data;
                } catch (err) {
                    console.error(err);
                    this.detail = null;
                    Swal.fire('Error', 'Gagal memuat detail jurnal: ' + err.message, 'error');
                } finally {
                    this.loading = false;
                }
            },

            formatJson(obj) {
                if (!obj) return '-';
                if (typeof obj === 'string') {
                    try {
                        obj = JSON.parse(obj);
                    } catch (e) {
                        return obj;
                    }
                }
                return JSON.stringify(obj, null, 2);
            }
        };
    }
</script>
@endpush
