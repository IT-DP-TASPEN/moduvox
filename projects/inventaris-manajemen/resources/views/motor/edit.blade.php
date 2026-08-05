@extends('layouts.app')

@section('title', 'Edit Inventaris')
@section('page-title', 'Edit Inventaris')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-4xl mx-auto">
    
    <div class="mb-6 pb-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Edit Data Aset: {{ $inventaris->rekening }}</h3>
            <p class="text-sm text-gray-500">
                @if($inventaris->akumulasi_penyusutan > 0)
                    <span class="text-red-500 font-medium">Perhatian:</span> Beberapa kolom terkunci karena aset ini sudah mengalami penyusutan.
                @else
                    Ubah informasi aset. Nomor Rekening tidak akan berubah untuk menjaga riwayat data.
                @endif
            </p>
        </div>
        <a href="{{ route('inventaris.show', $inventaris->id) }}" class="text-sm text-gray-500 hover:text-amber-600 font-medium flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Kembali
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

    <form action="{{ route('inventaris.update', $inventaris->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        @php
            $isLocked = $inventaris->akumulasi_penyusutan > 0 && !auth()->user()->hasRole('Super Admin');
        @endphp
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            
            {{-- Bagian Kiri: Informasi Utama --}}
            <div class="space-y-6">
                <div>
                    <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Informasi Dasar</h4>
                    
                    <div class="mb-4">
                        <label for="nama_aset" class="block text-sm font-medium text-gray-700 mb-1">Nama Aset <span class="text-red-500">*</span></label>
                        <input type="text" id="nama_aset" name="nama_aset" value="{{ old('nama_aset', $inventaris->nama_aset) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="merk" class="block text-sm font-medium text-gray-700 mb-1">Merk</label>
                            <input type="text" id="merk" name="merk" value="{{ old('merk', $inventaris->merk) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="no_seri" class="block text-sm font-medium text-gray-700 mb-1">No. Seri/SN</label>
                            <input type="text" id="no_seri" name="no_seri" value="{{ old('no_seri', $inventaris->no_seri) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Keuangan & Pembelian</h4>
                    
                    <div class="mb-4">
                        <label for="tgl_perolehan" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Perolehan <span class="text-red-500">*</span></label>
                        <input type="date" id="tgl_perolehan" name="tgl_perolehan" value="{{ old('tgl_perolehan', $inventaris->tgl_perolehan->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm {{ $isLocked ? 'bg-gray-100' : '' }}" required {{ $isLocked ? 'readonly' : '' }}>
                    </div>

                    <div class="mb-4">
                        <label for="harga_perolehan" class="block text-sm font-medium text-gray-700 mb-1">Harga Perolehan (Rp) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" id="harga_perolehan" name="harga_perolehan" value="{{ old('harga_perolehan', intval($inventaris->harga_perolehan)) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm pl-10 {{ $isLocked ? 'bg-gray-100' : '' }}" required {{ $isLocked ? 'readonly' : '' }}>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="sumber_id" class="block text-sm font-medium text-gray-700 mb-1">Sumber Dana <span class="text-red-500">*</span></label>
                        <select id="sumber_id" name="sumber_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" required>
                            <option value="">-- Pilih --</option>
                            @foreach($sumberDanas as $sumber)
                                <option value="{{ $sumber->id }}" {{ old('sumber_id', $inventaris->sumber_id) == $sumber->id ? 'selected' : '' }}>{{ $sumber->kode }} - {{ $sumber->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Bagian Kanan: Klasifikasi & Lokasi --}}
            <div class="space-y-6">
                <div>
                    <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Klasifikasi Aset</h4>
                    
                    <div class="mb-4">
                        <label for="golongan_id" class="block text-sm font-medium text-gray-700 mb-1">Golongan Aset <span class="text-red-500">*</span></label>
                        <select id="golongan_id" name="golongan_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" required>
                            <option value="">-- Pilih Golongan --</option>
                            @foreach($golongans as $golongan)
                                <option value="{{ $golongan->id }}" {{ old('golongan_id', $inventaris->golongan_id) == $golongan->id ? 'selected' : '' }}>{{ $golongan->kode }} - {{ $golongan->nama }} ({{ $golongan->umur_standar }} Bln)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="jenis_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Aset <span class="text-red-500">*</span></label>
                        <select id="jenis_id" name="jenis_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" required>
                            <option value="">-- Pilih Jenis --</option>
                            @foreach($jenises as $jenis)
                                <option value="{{ $jenis->id }}" {{ old('jenis_id', $inventaris->jenis_id) == $jenis->id ? 'selected' : '' }}>{{ $jenis->kode }} - {{ $jenis->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Lokasi & Penempatan</h4>
                    
                    <div class="mb-4">
                        <label for="kantor_id" class="block text-sm font-medium text-gray-700 mb-1">Kantor Cabang <span class="text-red-500">*</span></label>
                        <select id="kantor_id" name="kantor_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" required>
                            <option value="">-- Pilih Kantor --</option>
                            @foreach($kantors as $kantor)
                                <option value="{{ $kantor->id }}" {{ old('kantor_id', $inventaris->kantor_id) == $kantor->id ? 'selected' : '' }}>{{ $kantor->kode }} - {{ $kantor->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="ruangan_id" class="block text-sm font-medium text-gray-700 mb-1">Ruangan <span class="text-red-500">*</span></label>
                        <select id="ruangan_id" name="ruangan_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" required>
                            <option value="">-- Pilih --</option>
                            @foreach($ruangans as $ruangan)
                                <option value="{{ $ruangan->id }}" {{ old('ruangan_id', $inventaris->ruangan_id) == $ruangan->id ? 'selected' : '' }}>{{ $ruangan->kode }} - {{ $ruangan->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="lokasi_id" class="block text-sm font-medium text-gray-700 mb-1">Lokasi Gedung / Lantai <span class="text-red-500">*</span></label>
                        <select id="lokasi_id" name="lokasi_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" required>
                            <option value="">-- Pilih --</option>
                            @foreach($lokasis as $lokasi)
                                <option value="{{ $lokasi->id }}" {{ old('lokasi_id', $inventaris->lokasi_id) == $lokasi->id ? 'selected' : '' }}>{{ $lokasi->kode }} - {{ $lokasi->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
        </div>
        
        <div class="mt-6 border-t border-gray-100 pt-6">
            <div class="mb-4">
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan Tambahan</label>
                <textarea id="keterangan" name="keterangan" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">{{ old('keterangan', $inventaris->keterangan) }}</textarea>
            </div>
            
            <div class="flex justify-end gap-3">
                <a href="{{ route('inventaris.show', $inventaris->id) }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-amber-500 text-sm font-medium text-white hover:bg-amber-600 shadow-sm transition">
                    <i class="fa-solid fa-save mr-2"></i> Update Aset
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const oldRuangan = "{{ old('ruangan_id', $inventaris->ruangan_id) }}";
        
        function loadRuangan(kantor_id, selected = null) {
            const ruanganSelect = $('#ruangan_id');
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
        
        $('#kantor_id').on('change', function() {
            loadRuangan($(this).val());
        });
    });
</script>
@endpush
