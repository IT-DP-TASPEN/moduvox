@extends('layouts.app')

@section('title', 'Reset Data')
@section('page-title', 'Reset Data Inventaris')

@section('content')
{{-- Warning Banner --}}
<div class="mb-6 rounded-xl border border-red-200 bg-gradient-to-r from-red-50 to-orange-50 p-5">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
            <i class="fa-solid fa-triangle-exclamation text-red-600 text-xl"></i>
        </div>
        <div>
            <h3 class="text-lg font-bold text-red-800">Zona Berbahaya</h3>
            <p class="text-sm text-red-700 mt-1">
                Halaman ini berisi aksi yang bersifat <strong>permanen dan tidak dapat dibatalkan</strong>.
                Pastikan Anda sudah memahami dampaknya sebelum melanjutkan. Seluruh aktivitas di halaman ini dicatat ke Audit Trail.
            </p>
        </div>
    </div>
</div>

{{-- Reset Master Inventaris Card --}}
<div class="card-premium border-2 border-red-100">
    <div class="flex items-start gap-5 mb-6">
        <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-red-100 flex items-center justify-center">
            <i class="fa-solid fa-rotate-left text-red-600 text-2xl"></i>
        </div>
        <div class="flex-1">
            <h3 class="text-xl font-bold text-gray-900 tracking-tight">Reset Master Inventaris</h3>
            <p class="text-sm text-gray-500 mt-1">
                Menghapus <strong>seluruh data inventaris</strong> beserta semua data transaksi yang bergantung padanya.
                Setelah reset, database kembali ke kondisi seperti baru install untuk modul inventaris.
            </p>
        </div>
    </div>

    {{-- Data yang akan dihapus --}}
    <div class="mb-6">
        <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-database text-gray-400"></i>
            Data yang akan dihapus
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @php
                $items = [
                    ['label' => 'Master Inventaris', 'count' => $counts['inventaris'], 'icon' => 'fa-boxes-stacked', 'color' => 'red'],
                    ['label' => 'Batch Penyusutan', 'count' => $counts['penyusutan_batch'], 'icon' => 'fa-calculator', 'color' => 'orange'],
                    ['label' => 'Detail Penyusutan', 'count' => $counts['penyusutan_detail'], 'icon' => 'fa-list-ol', 'color' => 'orange'],
                    ['label' => 'Mutasi Inventaris', 'count' => $counts['inv_mutasi'], 'icon' => 'fa-right-left', 'color' => 'amber'],
                    ['label' => 'Improvement Inventaris', 'count' => $counts['inv_improvement'], 'icon' => 'fa-arrow-up-right-dots', 'color' => 'amber'],
                    ['label' => 'Data Motor/Kendaraan', 'count' => $counts['inv_motors'], 'icon' => 'fa-motorcycle', 'color' => 'yellow'],
                    ['label' => 'Data Tanah/Bangunan', 'count' => $counts['inv_tanahs'], 'icon' => 'fa-map-location-dot', 'color' => 'yellow'],
                    ['label' => 'API Journal', 'count' => $counts['api_journals'], 'icon' => 'fa-file-invoice-dollar', 'color' => 'slate'],
                    ['label' => 'API Log', 'count' => $counts['api_logs'], 'icon' => 'fa-scroll', 'color' => 'slate'],
                ];
            @endphp
            @foreach($items as $item)
            <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3">
                <div class="w-8 h-8 rounded-lg bg-{{ $item['color'] }}-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid {{ $item['icon'] }} text-{{ $item['color'] }}-500 text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500 truncate">{{ $item['label'] }}</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($item['count']) }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Data yang TIDAK dihapus --}}
    <div class="mb-6">
        <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-shield-halved text-green-500"></i>
            Data yang TIDAK dihapus (aman)
        </h4>
        <div class="flex flex-wrap gap-2">
            @php
                $safeItems = [
                    'Master Kantor', 'Master Golongan', 'Master Jenis', 'Master Lokasi',
                    'Master Ruangan', 'Master Sumber Dana', 'User & Role', 'Audit Trail'
                ];
            @endphp
            @foreach($safeItems as $safe)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                <i class="fa-solid fa-check text-green-500"></i>
                {{ $safe }}
            </span>
            @endforeach
        </div>
    </div>

    {{-- Tombol Reset --}}
    <div class="border-t border-gray-200 pt-5">
        @php
            $totalRecords = array_sum($counts);
        @endphp
        @if($totalRecords > 0)
        <button type="button" onclick="confirmReset()" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-red-600 text-white text-sm font-bold hover:bg-red-700 focus:ring-4 focus:ring-red-200 transition-all duration-200 shadow-lg shadow-red-200">
            <i class="fa-solid fa-rotate-left"></i>
            Reset Master Inventaris
            <span class="ml-1 px-2 py-0.5 rounded-md bg-red-500 text-[11px] font-bold">{{ number_format($totalRecords) }} record</span>
        </button>
        @else
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                <i class="fa-solid fa-check text-gray-400"></i>
            </div>
            <span>Tidak ada data inventaris yang perlu direset. Database sudah dalam kondisi bersih.</span>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmReset() {
        Swal.fire({
            title: '<span class="text-red-600"><i class="fa-solid fa-triangle-exclamation mr-2"></i>Peringatan!</span>',
            html: `
                <div class="text-left text-sm space-y-3 mt-2">
                    <p class="text-gray-700">Anda akan menghapus <strong class="text-red-600">{{ number_format($totalRecords) }} record</strong> dari <strong>9 tabel</strong>:</p>
                    <ul class="list-disc list-inside text-gray-600 text-xs space-y-1 bg-red-50 rounded-lg p-3">
                        <li>Master Inventaris ({{ number_format($counts['inventaris']) }})</li>
                        <li>Batch Penyusutan ({{ number_format($counts['penyusutan_batch']) }})</li>
                        <li>Detail Penyusutan ({{ number_format($counts['penyusutan_detail']) }})</li>
                        <li>Mutasi ({{ number_format($counts['inv_mutasi']) }})</li>
                        <li>Improvement ({{ number_format($counts['inv_improvement']) }})</li>
                        <li>Data Motor ({{ number_format($counts['inv_motors']) }})</li>
                        <li>Data Tanah ({{ number_format($counts['inv_tanahs']) }})</li>
                        <li>API Journal ({{ number_format($counts['api_journals']) }})</li>
                        <li>API Log ({{ number_format($counts['api_logs']) }})</li>
                    </ul>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <p class="text-yellow-800 font-semibold text-xs"><i class="fa-solid fa-circle-info mr-1"></i> Aksi ini tidak dapat dibatalkan!</p>
                    </div>
                    <p class="text-gray-700">Ketik <strong class="font-mono bg-gray-100 px-2 py-0.5 rounded text-red-600">RESET INVENTARIS</strong> untuk melanjutkan:</p>
                </div>
            `,
            input: 'text',
            inputPlaceholder: 'Ketik RESET INVENTARIS',
            inputAttributes: {
                autocomplete: 'off',
                autocapitalize: 'characters',
            },
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-rotate-left mr-1"></i> Ya, Reset Sekarang!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            focusCancel: true,
            customClass: {
                input: 'text-center font-mono font-bold tracking-wider',
            },
            inputValidator: (value) => {
                if (value !== 'RESET INVENTARIS') {
                    return 'Teks konfirmasi tidak sesuai! Ketik persis: RESET INVENTARIS';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form
                showLoading();
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("system.reset.inventaris") }}';
                
                let csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                let confirmation = document.createElement('input');
                confirmation.type = 'hidden';
                confirmation.name = 'confirmation';
                confirmation.value = result.value;
                form.appendChild(confirmation);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endpush
