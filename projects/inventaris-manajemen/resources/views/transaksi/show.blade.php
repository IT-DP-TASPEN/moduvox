@extends('layouts.app')

@section('title', 'Detail Transaksi #' . $mutasi->id)
@section('page-title', 'Detail Transaksi')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <a href="{{ route('transaksi.index') }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-800">Detail Transaksi Mutasi</h2>
            <div class="flex items-center gap-3 mt-1 text-sm">
                <span class="font-mono text-gray-600 bg-white px-2 py-0.5 rounded border border-gray-200">#{{ $mutasi->id }}</span>
                @php
                    $jenisColor = 'blue';
                    if ($mutasi->jenis_mutasi === 'PEMBELIAN') $jenisColor = 'green';
                    if ($mutasi->jenis_mutasi === 'PENGHAPUSAN') $jenisColor = 'red';
                    
                    $statusColor = 'gray';
                    if ($mutasi->status === 'APPROVED') $statusColor = 'emerald';
                    if ($mutasi->status === 'PENDING') $statusColor = 'amber';
                    if ($mutasi->status === 'REJECTED') $statusColor = 'red';
                @endphp
                <span class="px-2 py-0.5 text-xs font-bold rounded bg-{{ $jenisColor }}-100 text-{{ $jenisColor }}-700">
                    {{ $mutasi->jenis_mutasi }}
                </span>
                <span class="px-2 py-0.5 text-xs font-bold rounded bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700">
                    {{ $mutasi->status }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    {{-- Left Column: Detail Transaksi --}}
    <div class="lg:col-span-2 space-y-6">
        
        {{-- Card: Info Transaksi --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-semibold text-gray-800">Informasi Transaksi</h3>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Tanggal Transaksi</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $mutasi->tgl_mutasi ? $mutasi->tgl_mutasi->format('d M Y') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Jenis Mutasi</dt>
                        <dd class="text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs font-bold rounded bg-{{ $jenisColor }}-100 text-{{ $jenisColor }}-700">{{ $mutasi->jenis_mutasi }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Kantor Asal</dt>
                        <dd class="text-sm text-gray-900">{{ $mutasi->kantorAsal->nama ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Kantor Tujuan</dt>
                        <dd class="text-sm text-gray-900">{{ $mutasi->kantorTujuan->nama ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Ruangan Asal</dt>
                        <dd class="text-sm text-gray-900">{{ $mutasi->ruanganAsal->nama ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Ruangan Tujuan</dt>
                        <dd class="text-sm text-gray-900">{{ $mutasi->ruanganTujuan->nama ?? '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-gray-500 mb-1">Keterangan</dt>
                        <dd class="text-sm text-gray-900">{{ $mutasi->keterangan ?: '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Card: Info Aset --}}
        @if($mutasi->inventaris)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Informasi Aset</h3>
                <a href="{{ route('inventaris.show', $mutasi->inventaris->id) }}" class="text-sm text-primary-600 hover:text-primary-700 hover:underline transition-colors font-medium">
                    Lihat Detail Aset <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">No. Rekening</dt>
                        <dd class="text-sm font-mono font-semibold text-gray-900">{{ $mutasi->inventaris->rekening }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Nama Aset</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $mutasi->inventaris->nama_aset }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Golongan</dt>
                        <dd class="text-sm text-gray-900">{{ $mutasi->inventaris->golongan ? $mutasi->inventaris->golongan->kode . ' - ' . $mutasi->inventaris->golongan->nama : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Jenis</dt>
                        <dd class="text-sm text-gray-900">{{ $mutasi->inventaris->jenis ? $mutasi->inventaris->jenis->kode . ' - ' . $mutasi->inventaris->jenis->nama : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Harga Perolehan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ App\Helpers\FormatHelper::rupiah($mutasi->inventaris->harga_perolehan) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Nilai Buku</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ App\Helpers\FormatHelper::rupiah($mutasi->inventaris->nilai_buku) }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        @endif
    </div>

    {{-- Right Column: Status & User --}}
    <div class="space-y-6">
        
        {{-- Card: Status --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-semibold text-gray-800">Status Transaksi</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="text-center p-4 rounded-lg bg-{{ $statusColor }}-50 border border-{{ $statusColor }}-100">
                    <p class="text-xs font-medium text-{{ $statusColor }}-800 uppercase mb-1">Status</p>
                    <p class="text-xl font-bold text-{{ $statusColor }}-600">{{ $mutasi->status }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1">Dibuat Oleh</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $mutasi->user->name ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1">Tanggal Dibuat</p>
                    <p class="text-sm text-gray-900">{{ $mutasi->created_at ? $mutasi->created_at->format('d M Y, H:i') : '-' }}</p>
                </div>

                @if($mutasi->approvalUser)
                <hr class="border-gray-100">
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1">Disetujui Oleh</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $mutasi->approvalUser->name }}</p>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
