@extends('layouts.app')

@section('title', 'Laporan Penyusutan')
@section('page-title', 'Laporan Penyusutan')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-2xl mx-auto">
    <div class="mb-6 border-b border-gray-100 pb-4 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-file-invoice text-xl"></i>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Cetak Laporan Penyusutan</h3>
            <p class="text-sm text-gray-500">Pilih periode dan cabang untuk mencetak rincian penyusutan aktiva tetap.</p>
        </div>
    </div>

    <form action="{{ route('reports.penyusutan.generate') }}" method="GET" target="_blank" class="space-y-6">
        <div class="space-y-6">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">Bulan <span class="text-red-500">*</span></label>
                    <select id="bulan" name="bulan" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm" required>
                        @php $currentMonth = date('n'); @endphp
                        @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $idx => $namaBulan)
                            <option value="{{ $idx + 1 }}" {{ $currentMonth == ($idx + 1) ? 'selected' : '' }}>{{ $namaBulan }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                    <select id="tahun" name="tahun" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm" required>
                        @php $currentYear = date('Y'); @endphp
                        @for($y = 2020; $y <= $currentYear + 1; $y++)
                            <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div>
                <label for="kantor_id" class="block text-sm font-medium text-gray-700 mb-1">Kantor Cabang</label>
                @if(auth()->user()->hasRole('Super Admin'))
                <select id="kantor_id" name="kantor_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                    <option value="">-- Seluruh Cabang (Konsolidasi) --</option>
                    @foreach($kantors as $kantor)
                        <option value="{{ $kantor->id }}">{{ $kantor->kode }} - {{ $kantor->nama }}</option>
                    @endforeach
                </select>
                @else
                <input type="text" class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-500 sm:text-sm" value="{{ auth()->user()->kantor->nama ?? 'Pusat' }}" readonly>
                <input type="hidden" name="kantor_id" value="{{ auth()->user()->kantor_id }}">
                @endif
            </div>

            <div class="pt-4 border-t border-gray-100 flex gap-3 justify-end">
                <button type="submit" name="export_excel" value="1" class="px-5 py-2.5 rounded-lg bg-green-600 text-sm font-medium text-white hover:bg-green-700 shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-file-excel"></i> Export Excel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> Cetak Web / PDF
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
