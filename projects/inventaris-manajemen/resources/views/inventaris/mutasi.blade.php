@extends('layouts.app')

@section('title', 'Mutasi Aset: ' . $inventaris->rekening)
@section('page-title', 'Form Mutasi Aset')

@section('content')
<div class="card-premium max-w-3xl mx-auto">
    
    <div class="mb-8 pb-5 border-b border-gray-200 flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-gray-900 tracking-tight">Proses Mutasi Pemindahan Aset</h3>
            <p class="text-sm text-gray-500 mt-1">Pindahkan aset ke lokasi/cabang baru. Histori pemindahan akan dicatat.</p>
        </div>
        <a href="{{ route('inventaris.show', $inventaris->id) }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left mr-2"></i> Batal
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Asset Summary --}}
    <div class="mb-8 bg-gray-50 rounded-xl p-5 border border-gray-100 flex items-start gap-4">
        <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-box text-xl"></i>
        </div>
        <div class="flex-1">
            <h4 class="font-bold text-gray-900">{{ $inventaris->nama_aset }}</h4>
            <div class="mt-1 flex flex-wrap gap-x-6 gap-y-2 text-sm">
                <div><span class="text-gray-500">No. Rekening:</span> <span class="font-medium text-gray-800">{{ $inventaris->rekening }}</span></div>
                <div><span class="text-gray-500">Lokasi Saat Ini:</span> <span class="font-medium text-gray-800">{{ $inventaris->kantor?->nama ?? '-' }} - {{ $inventaris->ruangan?->nama ?? '-' }}</span></div>
            </div>
        </div>
    </div>

    <form action="{{ route('mutasi.store', $inventaris->id) }}" method="POST">
        @csrf
        
        <div class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="kantor_tujuan_id" class="label-premium">Kantor Cabang Tujuan <span class="text-danger-500">*</span></label>
                    <select id="kantor_tujuan_id" name="kantor_tujuan_id" class="input-premium" required>
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($kantors as $kantor)
                            <option value="{{ $kantor->id }}" {{ old('kantor_tujuan_id') == $kantor->id ? 'selected' : '' }}>{{ $kantor->kode }} - {{ $kantor->nama }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="tgl_mutasi" class="label-premium">Tanggal Mutasi Efektif <span class="text-danger-500">*</span></label>
                    <input type="date" id="tgl_mutasi" name="tgl_mutasi" value="{{ old('tgl_mutasi', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" class="input-premium" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="ruangan_tujuan_id" class="label-premium">Ruangan Tujuan <span class="text-danger-500">*</span></label>
                    <select id="ruangan_tujuan_id" name="ruangan_tujuan_id" class="input-premium bg-gray-50 text-gray-500" required disabled>
                        <option value="">-- Pilih Kantor Dahulu --</option>
                    </select>
                </div>
                
                <div>
                    <label for="lokasi_tujuan_id" class="label-premium">Lokasi Gedung / Lantai <span class="text-danger-500">*</span></label>
                    <select id="lokasi_tujuan_id" name="lokasi_tujuan_id" class="input-premium" required>
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach($lokasis as $lokasi)
                            <option value="{{ $lokasi->id }}" {{ old('lokasi_tujuan_id') == $lokasi->id ? 'selected' : '' }}>{{ $lokasi->kode }} - {{ $lokasi->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="keterangan" class="label-premium">Alasan / Keterangan Mutasi <span class="text-danger-500">*</span></label>
                <textarea id="keterangan" name="keterangan" rows="3" class="input-premium" placeholder="Contoh: Pemindahan aset karena karyawan pindah dinas ke cabang terkait." required>{{ old('keterangan') }}</textarea>
            </div>
            
        </div>
        
        <div class="mt-8 border-t border-gray-200 pt-6 flex justify-end gap-3">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-right-left mr-2"></i> Proses Mutasi
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const oldRuangan = "{{ old('ruangan_tujuan_id') }}";
        
        function loadRuangan(kantor_id, selected = null) {
            const ruanganSelect = $('#ruangan_tujuan_id');
            ruanganSelect.empty().append('<option value="">Sedang memuat...</option>').prop('disabled', true);
            
            if (!kantor_id) {
                ruanganSelect.empty().append('<option value="">-- Pilih Kantor Dahulu --</option>');
                return;
            }
            
            $.get(`/api/master/ruangan-by-kantor/${kantor_id}`, function(data) {
                ruanganSelect.empty().append('<option value="">-- Pilih Ruangan --</option>');
                
                data.forEach(function(ruangan) {
                    const isSelected = selected == ruangan.id ? 'selected' : '';
                    ruanganSelect.append(`<option value="${ruangan.id}" ${isSelected}>${ruangan.kode} - ${ruangan.nama}</option>`);
                });
                
                ruanganSelect.prop('disabled', false).removeClass('bg-gray-50');
            });
        }
        
        $('#kantor_tujuan_id').on('change', function() {
            loadRuangan($(this).val());
        });
        
        // Trigger on load if old value exists
        if ($('#kantor_tujuan_id').val()) {
            loadRuangan($('#kantor_tujuan_id').val(), oldRuangan);
        }
    });
</script>
@endpush
