@extends('layouts.app')

@section('title', 'Detail Batch Penyusutan #' . $batch->id)
@section('page-title', 'Detail Batch Penyusutan')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <a href="{{ route('penyusutan.index') }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-800">Batch #{{ $batch->id }} ({{ $batch->periode_label }})</h2>
            <div class="flex items-center gap-3 mt-1 text-sm">
                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $batch->status->color() }}-100 text-{{ $batch->status->color() }}-700">
                    {{ $batch->status->label() }}
                </span>
                <span class="text-gray-500"><i class="fa-regular fa-clock mr-1"></i> Digenerate: {{ $batch->created_at->format('d M Y H:i') }}</span>
            </div>
        </div>
    </div>
    
    <div class="flex gap-2">
        @if($batch->isDraft())
        <form action="{{ route('penyusutan.approve', $batch->id) }}" method="POST">
            @csrf
            <button type="button" onclick="event.preventDefault(); Swal.fire({title: 'Peringatan', text: 'Setelah disetujui, jurnal penyusutan akan otomatis terkirim ke FinCloud Core Banking dan tidak dapat dibatalkan. Lanjutkan?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Lanjutkan', cancelButtonText: 'Batal'}).then((result) => { if (result.isConfirmed) { this.closest('form').submit(); } })" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-check-double"></i> Approve & Kirim Jurnal
            </button>
        </form>
        @else
        <a href="javascript:void(0)" onclick="Swal.fire('Info', 'Pengecekan status jurnal langsung ke Core Banking sedang dalam tahap pengembangan.', 'info')" class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-100 transition shadow-sm flex items-center gap-2">
            <i class="fa-solid fa-list-check"></i> Cek Status Jurnal
        </a>
        @endif
    </div>
</div>

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

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    
    {{-- Summary Column --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 border-b border-gray-100 pb-3">Ringkasan Beban</h3>
            
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1">Total Aset Disusutkan</p>
                    <p class="text-xl font-bold text-gray-900">{{ number_format($batch->details->count(), 0, ',', '.') }} Aset</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1">Total Beban Periode Ini</p>
                    <p class="text-xl font-bold text-red-600">{{ App\Helpers\FormatHelper::rupiah($batch->details->sum('beban_bulan_ini')) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 border-b border-gray-100 pb-3">Status Persetujuan</h3>
            
            <div class="space-y-4">
                @if($batch->approved_by)
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $batch->approver->name }}</p>
                        <p class="text-xs text-gray-500">{{ $batch->approved_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                </div>
                @else
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Menunggu Approval</p>
                        <p class="text-xs text-gray-500">Oleh Checker / Spv</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Data Column --}}
    <div class="lg:col-span-3 space-y-6">
        
        {{-- Group Summary untuk Jurnal --}}
        <div class="card-premium mb-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-gray-900 tracking-tight">Summary Jurnal Per Cabang & Golongan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-y border-gray-200">
                            <th class="px-5 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kantor Cabang</th>
                            <th class="px-5 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Golongan Aset</th>
                            <th class="px-5 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-right">Total Beban (Debet & Kredit)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach($summary as $row)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900">{{ $row->nama_kantor }}</div>
                                <div class="text-xs text-gray-500">{{ $row->kode_kantor }}</div>
                            </td>
                            <td class="px-5 py-4 font-medium text-gray-800">{{ $row->nama_golongan }}</td>
                            <td class="px-5 py-4 text-right text-gray-900 font-bold">{{ App\Helpers\FormatHelper::rupiah($row->total_beban) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Detail Aset --}}
        <div class="card-premium">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-gray-900 tracking-tight">Rincian Aset Terproses</h3>
            </div>
            <div class="overflow-x-auto" style="max-height: 500px;">
                <table class="w-full text-left border-collapse relative">
                    <thead class="sticky top-0 bg-gray-50 border-y border-gray-200 z-10">
                        <tr>
                            <th class="px-5 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider">No Rekening</th>
                            <th class="px-5 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Nama Aset</th>
                            <th class="px-5 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-right">Beban Bulan Ini</th>
                            <th class="px-5 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-right">Nilai Buku Baru</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach($batch->details as $detail)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-4 font-mono text-xs">
                                <a href="{{ route('inventaris.show', $detail->inventaris_id) }}" class="text-primary-600 hover:text-primary-700 hover:underline transition-colors font-medium">
                                    {{ $detail->inventaris->rekening }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-gray-900 truncate max-w-[200px]" title="{{ $detail->inventaris->nama_aset }}">{{ $detail->inventaris->nama_aset }}</td>
                            <td class="px-5 py-4 text-right text-red-600 font-medium">-{{ App\Helpers\FormatHelper::rupiah($detail->beban_bulan_ini) }}</td>
                            <td class="px-5 py-4 text-right font-bold text-gray-900">{{ App\Helpers\FormatHelper::rupiah($detail->nilai_buku_sesudah) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
