@extends('layouts.app')

@section('title', 'Penyusutan Aset')
@section('page-title', 'Penyusutan Aset')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Riwayat Penyusutan Aset</h3>
            <p class="text-sm text-gray-500">Daftar batch penyusutan yang telah digenerate setiap bulan.</p>
        </div>
        <button type="button" onclick="openGenerateModal()" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-calculator"></i> Proses Penyusutan Baru
        </button>
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

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-y border-gray-200">
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">ID Batch</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Periode</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Tgl Proses</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Approved By</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($batches as $batch)
                <tr>
                    <td class="px-4 py-4 font-mono text-gray-600">#{{ $batch->id }}</td>
                    <td class="px-4 py-4 font-semibold text-gray-900">{{ $batch->periode_label }}</td>
                    <td class="px-4 py-4">{{ $batch->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-4">
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $batch->status->color() }}-100 text-{{ $batch->status->color() }}-700">
                            {{ $batch->status->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-gray-600">
                        @if($batch->approved_by)
                            {{ $batch->approver->name }} <br>
                            <span class="text-xs text-gray-400">{{ $batch->approved_at->format('d/m/Y H:i') }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-4 py-4 text-right">
                        <a href="{{ route('penyusutan.show', $batch->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-medium transition">
                            Lihat Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        Belum ada riwayat penyusutan. Klik "Proses Penyusutan Baru" untuk memulai.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('penyusutan._generate_modal')

@endsection
