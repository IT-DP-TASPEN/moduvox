@extends('layouts.app')

@section('title', 'Detail Inventaris: ' . $inventaris->rekening)
@section('page-title', 'Detail Inventaris')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <a href="{{ route('inventaris.index') }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ $inventaris->nama_aset }}</h2>
            <div class="flex items-center gap-3 mt-1 text-sm">
                <span class="font-mono text-gray-600 bg-white px-2 py-0.5 rounded border border-gray-200">{{ $inventaris->rekening }}</span>
                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-{{ $inventaris->status->color() }}-100 text-{{ $inventaris->status->color() }}-700">
                    {{ $inventaris->status->label() }}
                </span>
            </div>
        </div>
    </div>
    
    <div class="flex gap-2">
        <a href="{{ route('inventaris.print', $inventaris->id) }}" class="bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition shadow-sm flex items-center gap-2" target="_blank">
            <i class="fa-solid fa-qrcode"></i> Cetak Label
        </a>
        <a href="{{ route('mutasi.create', $inventaris->id) }}" class="bg-blue-50 text-blue-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-100 transition flex items-center gap-2">
            <i class="fa-solid fa-right-left"></i> Mutasi
        </a>
        <a href="{{ route('inventaris.edit', $inventaris->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square"></i> Edit
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    {{-- Left Column: Asset Info --}}
    <div class="lg:col-span-2 space-y-6">
        
        {{-- Card: Info Dasar --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Informasi Dasar</h3>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Merk</dt>
                        <dd class="text-sm text-gray-900">{{ $inventaris->merk ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Nomor Seri (SN)</dt>
                        <dd class="text-sm font-mono text-gray-900">{{ $inventaris->no_seri ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Golongan Aset</dt>
                        <dd class="text-sm text-gray-900">{{ $inventaris->golongan ? $inventaris->golongan->kode . ' - ' . $inventaris->golongan->nama : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Jenis Aset</dt>
                        <dd class="text-sm text-gray-900">{{ $inventaris->jenis ? $inventaris->jenis->kode . ' - ' . $inventaris->jenis->nama : '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-gray-500 mb-1">Keterangan</dt>
                        <dd class="text-sm text-gray-900">{{ $inventaris->keterangan ?: '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Tabs: Mutasi & Penyusutan --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ tab: 'mutasi' }">
            <div class="border-b border-gray-100 flex overflow-x-auto">
                <button @click="tab = 'mutasi'" :class="tab === 'mutasi' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-medium text-sm whitespace-nowrap transition">
                    Riwayat Transaksi
                </button>
                <button @click="tab = 'penyusutan'" :class="tab === 'penyusutan' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-medium text-sm whitespace-nowrap transition">
                    Histori Penyusutan
                </button>
            </div>
            
            <div class="p-0">
                {{-- Tab Mutasi --}}
                <div x-show="tab === 'mutasi'" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Asal</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Tujuan</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($inventaris->mutasi as $mutasi)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $mutasi->tgl_mutasi->format('d M Y') }}</td>
                                <td class="px-6 py-4">{{ $mutasi->kantorAsal->nama ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $mutasi->kantorTujuan->nama ?? '-' }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $mutasi->keterangan }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada riwayat mutasi untuk aset ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Tab Penyusutan --}}
                <div x-show="tab === 'penyusutan'" style="display: none;" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Periode</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Nilai Sblm</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Beban</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Nilai Ssdh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($inventaris->penyusutanDetail->sortByDesc(function($item) { return $item->batch->periode_ym ?? ''; }) as $susut)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('penyusutan.show', $susut->batch_id) }}" class="text-primary-600 hover:text-primary-700 hover:underline font-medium transition-colors">
                                        {{ $susut->batch->periode_label ?? $susut->batch->periode_ym }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-right">{{ App\Helpers\FormatHelper::rupiah($susut->nilai_buku_sebelum) }}</td>
                                <td class="px-6 py-4 text-right text-red-500 font-medium">-{{ App\Helpers\FormatHelper::rupiah($susut->beban_bulan_ini) }}</td>
                                <td class="px-6 py-4 text-right">{{ App\Helpers\FormatHelper::rupiah($susut->nilai_buku_sesudah) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada histori penyusutan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>

    {{-- Right Column: Financials & Location --}}
    <div class="space-y-6">
        
        {{-- Card: Keuangan --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-semibold text-gray-800">Nilai & Keuangan</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="bg-amber-50 rounded-lg p-4 border border-amber-100">
                    <p class="text-xs font-medium text-amber-800 uppercase mb-1">Nilai Buku Saat Ini</p>
                    <p class="text-2xl font-bold text-amber-600">{{ App\Helpers\FormatHelper::rupiah($inventaris->nilai_buku) }}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">Harga Perolehan</p>
                        <p class="text-sm font-semibold text-gray-900">{{ App\Helpers\FormatHelper::rupiah($inventaris->harga_perolehan) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">Akumulasi Susut</p>
                        <p class="text-sm font-semibold text-gray-900 text-red-500">{{ App\Helpers\FormatHelper::rupiah($inventaris->akumulasi_penyusutan) }}</p>
                    </div>
                </div>

                <hr class="border-gray-100">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">Tgl Perolehan</p>
                        <p class="text-sm text-gray-900">{{ $inventaris->tgl_perolehan ? $inventaris->tgl_perolehan->format('d M Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">Umur Ekonomis</p>
                        <p class="text-sm text-gray-900">{{ $inventaris->umur_bulan ?? 0 }} / {{ $inventaris->golongan->umur_standar ?? '-' }} Bln</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs font-medium text-gray-500 mb-1">Sumber Dana</p>
                        <p class="text-sm text-gray-900">{{ $inventaris->sumberDana->nama ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Lokasi --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-semibold text-gray-800">Penempatan Aset</h3>
            </div>
            <div class="p-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none w-8">
                        <div class="h-full w-px bg-gray-200"></div>
                    </div>
                    
                    <div class="relative flex gap-4 mb-6">
                        <div class="w-8 h-8 rounded-full bg-blue-100 border border-white shadow-sm flex items-center justify-center shrink-0 z-10">
                            <i class="fa-solid fa-building text-blue-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Kantor Cabang</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $inventaris->kantor->nama ?? '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="relative flex gap-4 mb-6">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 border border-white shadow-sm flex items-center justify-center shrink-0 z-10">
                            <i class="fa-solid fa-door-open text-indigo-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Ruangan</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $inventaris->ruangan->nama ?? '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="relative flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 border border-white shadow-sm flex items-center justify-center shrink-0 z-10">
                            <i class="fa-solid fa-map-pin text-emerald-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Lokasi / Lantai</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $inventaris->lokasi->nama ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
