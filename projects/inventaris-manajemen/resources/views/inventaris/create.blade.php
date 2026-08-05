@extends('layouts.app')

@section('title', 'Tambah Inventaris Baru')
@section('page-title', 'Tambah Inventaris')

@section('content')
<div class="card-premium max-w-4xl mx-auto">
    
    <div class="mb-8 pb-5 border-b border-gray-200 flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-gray-900 tracking-tight">Form Pencatatan Aset Baru</h3>
            <p class="text-sm text-gray-500 mt-1">Isi data dengan lengkap. Nomor Inventaris akan dibuat otomatis oleh sistem.</p>
        </div>
        <a href="{{ route('inventaris.index') }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
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

    <form action="{{ route('inventaris.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            
            {{-- Bagian Kiri: Informasi Utama --}}
            <div class="space-y-6">
                <div>
                    <h4 class="text-[11px] font-bold text-primary-600 uppercase tracking-wider mb-5">Informasi Dasar</h4>
                    
                    <div class="mb-5">
                        <label for="nama_aset" class="label-premium">Nama Aset <span class="text-danger-500">*</span></label>
                        <input type="text" id="nama_aset" name="nama_aset" value="{{ old('nama_aset') }}" class="input-premium" required>
                    </div>

                    <div class="grid grid-cols-2 gap-5 mb-5">
                        <div>
                            <label for="merk" class="label-premium">Merk</label>
                            <input type="text" id="merk" name="merk" value="{{ old('merk') }}" class="input-premium">
                        </div>
                        <div>
                            <label for="no_seri" class="label-premium">No. Seri/SN</label>
                            <input type="text" id="no_seri" name="no_seri" value="{{ old('no_seri') }}" class="input-premium">
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-[11px] font-bold text-primary-600 uppercase tracking-wider mb-5 mt-2">Keuangan & Pembelian</h4>
                    
                    <div class="mb-5">
                        <label for="tgl_perolehan" class="label-premium">Tanggal Perolehan <span class="text-danger-500">*</span></label>
                        <input type="date" id="tgl_perolehan" name="tgl_perolehan" value="{{ old('tgl_perolehan', date('Y-m-d')) }}" class="input-premium" required>
                    </div>

                    <div class="mb-5">
                        <label for="harga_perolehan" class="label-premium">Harga Perolehan (Rp) <span class="text-danger-500">*</span></label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <span class="text-gray-500 sm:text-sm font-medium">Rp</span>
                            </div>
                            <input type="number" id="harga_perolehan" name="harga_perolehan" value="{{ old('harga_perolehan') }}" class="input-premium pl-11" required>
                        </div>
                        <p class="mt-2 text-[11px] text-gray-500">Nilai Buku awal akan otomatis sama dengan Harga Perolehan.</p>
                    </div>

                    <div class="mb-5">
                        <label for="sumber_id" class="label-premium">Sumber Dana <span class="text-danger-500">*</span></label>
                        <select id="sumber_id" name="sumber_id" class="input-premium" required>
                            <option value="">-- Pilih --</option>
                            @foreach($sumberDanas as $sumber)
                                <option value="{{ $sumber->id }}" {{ old('sumber_id') == $sumber->id ? 'selected' : '' }}>{{ $sumber->kode }} - {{ $sumber->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Bagian Kanan: Klasifikasi & Lokasi --}}
            <div class="space-y-6">
                <div>
                    <h4 class="text-[11px] font-bold text-primary-600 uppercase tracking-wider mb-5">Klasifikasi Aset</h4>
                    
                    <div class="mb-5">
                        <label for="golongan_id" class="label-premium">Golongan Aset <span class="text-danger-500">*</span></label>
                        <select id="golongan_id" name="golongan_id" class="input-premium" required>
                            <option value="">-- Pilih Golongan --</option>
                            @foreach($golongans as $golongan)
                                <option value="{{ $golongan->id }}" {{ old('golongan_id') == $golongan->id ? 'selected' : '' }}>{{ $golongan->kode }} - {{ $golongan->nama }} ({{ $golongan->umur_standar }} Bln)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-5">
                        <label for="jenis_id" class="label-premium">Jenis Aset <span class="text-danger-500">*</span></label>
                        <select id="jenis_id" name="jenis_id" class="input-premium" required>
                            <option value="" data-nama="">-- Pilih Jenis --</option>
                            @foreach($jenises as $jenis)
                                <option value="{{ $jenis->id }}" data-nama="{{ strtolower($jenis->nama) }}" {{ old('jenis_id') == $jenis->id ? 'selected' : '' }}>{{ $jenis->kode }} - {{ $jenis->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <h4 class="text-[11px] font-bold text-primary-600 uppercase tracking-wider mb-5 mt-2">Lokasi & Penempatan</h4>
                    
                    <div class="mb-5">
                        <label for="kantor_id" class="label-premium">Kantor Cabang <span class="text-danger-500">*</span></label>
                        <select id="kantor_id" name="kantor_id" class="input-premium" required>
                            <option value="">-- Pilih Kantor --</option>
                            @foreach($kantors as $kantor)
                                <option value="{{ $kantor->id }}" {{ old('kantor_id') == $kantor->id ? 'selected' : '' }}>{{ $kantor->kode }} - {{ $kantor->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-5">
                        <label for="ruangan_id" class="label-premium">Ruangan <span class="text-danger-500">*</span></label>
                        <select id="ruangan_id" name="ruangan_id" class="input-premium bg-gray-50 text-gray-500" required disabled>
                            <option value="">-- Pilih Kantor Dahulu --</option>
                        </select>
                    </div>

                    <div class="mb-5">
                        <label for="lokasi_id" class="label-premium">Lokasi Gedung / Lantai <span class="text-danger-500">*</span></label>
                        <select id="lokasi_id" name="lokasi_id" class="input-premium" required>
                            <option value="">-- Pilih --</option>
                            @foreach($lokasis as $lokasi)
                                <option value="{{ $lokasi->id }}" {{ old('lokasi_id') == $lokasi->id ? 'selected' : '' }}>{{ $lokasi->kode }} - {{ $lokasi->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
        </div>

        {{-- Form Dinamis: Motor --}}
        <div id="form-motor" class="hidden mt-8 border-t border-gray-200 pt-8">
            <h4 class="text-[11px] font-bold text-primary-600 uppercase tracking-wider mb-5"><i class="fa-solid fa-motorcycle mr-2 text-primary-500"></i>Data Spesifik Kendaraan</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div>
                    <label class="label-premium">No. Polisi</label>
                    <input type="text" name="no_polisi" value="{{ old('no_polisi') }}" class="input-premium uppercase">
                </div>
                <div>
                    <label class="label-premium">No. BPKB</label>
                    <input type="text" name="no_bpkb" value="{{ old('no_bpkb') }}" class="input-premium">
                </div>
                <div>
                    <label class="label-premium">No. Mesin</label>
                    <input type="text" name="no_mesin" value="{{ old('no_mesin') }}" class="input-premium uppercase">
                </div>
                <div>
                    <label class="label-premium">No. Rangka</label>
                    <input type="text" name="no_rangka" value="{{ old('no_rangka') }}" class="input-premium uppercase">
                </div>
                <div class="grid grid-cols-2 gap-3 lg:col-span-2">
                    <div>
                        <label class="label-premium">Tahun Buat</label>
                        <input type="text" name="tahun_pembuatan" value="{{ old('tahun_pembuatan') }}" class="input-premium" maxlength="4">
                    </div>
                    <div>
                        <label class="label-premium">Tahun Rakit</label>
                        <input type="text" name="tahun_rakit" value="{{ old('tahun_rakit') }}" class="input-premium" maxlength="4">
                    </div>
                </div>
                <div>
                    <label class="label-premium">Warna</label>
                    <input type="text" name="warna" value="{{ old('warna') }}" class="input-premium">
                </div>
                <div>
                    <label class="label-premium">Tgl Pajak</label>
                    <input type="date" name="tgl_pajak" value="{{ old('tgl_pajak') }}" class="input-premium">
                </div>
                <div class="lg:col-span-2">
                    <label class="label-premium">Atas Nama</label>
                    <input type="text" name="atas_nama_motor" value="{{ old('atas_nama_motor') }}" class="input-premium">
                </div>
            </div>
        </div>

        {{-- Form Dinamis: Tanah --}}
        <div id="form-tanah" class="hidden mt-8 border-t border-gray-200 pt-8">
            <h4 class="text-[11px] font-bold text-primary-600 uppercase tracking-wider mb-5"><i class="fa-solid fa-map-location-dot mr-2 text-primary-500"></i>Data Spesifik Tanah/Bangunan</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div>
                    <label class="label-premium">No. SHM</label>
                    <input type="text" name="no_shm" value="{{ old('no_shm') }}" class="input-premium uppercase">
                </div>
                <div>
                    <label class="label-premium">No. SHGB</label>
                    <input type="text" name="no_shgb" value="{{ old('no_shgb') }}" class="input-premium uppercase">
                </div>
                <div>
                    <label class="label-premium">Tanggal SHM</label>
                    <input type="date" name="tanggal_shm" value="{{ old('tanggal_shm') }}" class="input-premium">
                </div>
                <div>
                    <label class="label-premium">Surat Ukur</label>
                    <input type="text" name="surat_ukur" value="{{ old('surat_ukur') }}" class="input-premium">
                </div>
                <div class="grid grid-cols-2 gap-3 lg:col-span-2">
                    <div>
                        <label class="label-premium">Luas Tanah (m2)</label>
                        <input type="text" name="luas_tanah" value="{{ old('luas_tanah') }}" class="input-premium">
                    </div>
                    <div>
                        <label class="label-premium">Luas Bangunan (m2)</label>
                        <input type="text" name="luas_bangunan" value="{{ old('luas_bangunan') }}" class="input-premium">
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <label class="label-premium">Atas Nama</label>
                    <input type="text" name="atas_nama_tanah" value="{{ old('atas_nama_tanah') }}" class="input-premium">
                </div>
            </div>
        </div>
        <div class="mt-8 border-t border-gray-200 pt-8">
            <div class="mb-6">
                <label for="keterangan" class="label-premium">Keterangan Tambahan</label>
                <textarea id="keterangan" name="keterangan" rows="3" class="input-premium">{{ old('keterangan') }}</textarea>
            </div>
            
            <div class="flex justify-end gap-3">
                <a href="{{ route('inventaris.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-save mr-2"></i> Simpan Aset Baru
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const oldRuangan = "{{ old('ruangan_id') }}";
        
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
        
        // Dynamic Form Logic
        function toggleDynamicForms() {
            const selectedOption = $('#jenis_id').find('option:selected');
            const jenisNama = selectedOption.data('nama') || '';
            
            $('#form-motor, #form-tanah').addClass('hidden');
            
            if (jenisNama.includes('motor') || jenisNama.includes('kendaraan')) {
                $('#form-motor').removeClass('hidden');
            } else if (jenisNama.includes('tanah') || jenisNama.includes('bangunan')) {
                $('#form-tanah').removeClass('hidden');
            }
        }
        
        $('#jenis_id').on('change', function() {
            toggleDynamicForms();
        });

        // Trigger on load if old value exists
        if ($('#kantor_id').val()) {
            loadRuangan($('#kantor_id').val(), oldRuangan);
        }
        toggleDynamicForms();
    });
</script>
@endpush
