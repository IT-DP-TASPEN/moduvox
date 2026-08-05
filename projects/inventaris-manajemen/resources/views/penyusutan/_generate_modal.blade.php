<!-- Modal Generate Penyusutan -->
<div id="modal-generate" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeGenerateModal()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div id="modal-generate-panel" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md scale-95 opacity-0 duration-200">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-gray-100">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fa-solid fa-calculator text-amber-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">Proses Penyusutan Baru</h3>
                            <p class="text-sm text-gray-500 mt-1">Pilih periode penyusutan. Sistem akan menghitung beban seluruh aset yang aktif.</p>
                            
                            <div class="mt-4">
                                <form id="form-generate" action="{{ route('penyusutan.store') }}" method="POST">
                                    @csrf
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="mb-4">
                                            <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                                            <select id="bulan" name="bulan" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm" required>
                                                @php $currentMonth = date('n'); @endphp
                                                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $idx => $namaBulan)
                                                    <option value="{{ $idx + 1 }}" {{ $currentMonth == ($idx + 1) ? 'selected' : '' }}>{{ $namaBulan }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                                            <select id="tahun" name="tahun" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm" required>
                                                @php $currentYear = date('Y'); @endphp
                                                @for($y = $currentYear - 2; $y <= $currentYear + 1; $y++)
                                                    <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" onclick="Swal.fire({title: 'Konfirmasi', text: 'Apakah Anda yakin ingin memproses penyusutan untuk periode ini? Proses ini akan mengupdate nilai buku seluruh aset.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Proses', cancelButtonText: 'Batal'}).then((result) => { if (result.isConfirmed) { document.getElementById('form-generate').submit(); } })" class="inline-flex w-full justify-center rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 transition sm:ml-3 sm:w-auto">
                        <i class="fa-solid fa-play mr-2 mt-0.5"></i> Proses Sekarang
                    </button>
                    <button type="button" onclick="closeGenerateModal()" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition sm:mt-0 sm:w-auto">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openGenerateModal() {
        $('#modal-generate').removeClass('hidden');
        setTimeout(() => {
            $('#modal-generate-panel').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
        }, 10);
    }

    function closeGenerateModal() {
        $('#modal-generate-panel').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => {
            $('#modal-generate').addClass('hidden');
        }, 200);
    }
</script>
