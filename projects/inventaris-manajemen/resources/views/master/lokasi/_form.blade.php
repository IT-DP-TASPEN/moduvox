<!-- Modal Lokasi -->
<div id="modal-lokasi" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div id="modal-lokasi-panel" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg scale-95 opacity-0 duration-200">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-gray-100">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fa-solid fa-map-location-dot text-amber-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">Form Lokasi</h3>
                            <div class="mt-4">
                                <form id="form-lokasi">
                                    <input type="hidden" id="lokasi_id" name="id">
                                    
                                    <div class="mb-4">
                                        <label for="kode" class="block text-sm font-medium text-gray-700 mb-1">Kode Lokasi</label>
                                        <input type="text" id="kode" name="kode" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm" placeholder="Contoh: 01">
                                        <p id="error-kode" class="error-message hidden mt-1 text-xs text-red-500"></p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi</label>
                                        <input type="text" id="nama" name="nama" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm" placeholder="Contoh: Lantai 1">
                                        <p id="error-nama" class="error-message hidden mt-1 text-xs text-red-500"></p>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="submit" form="form-lokasi" class="inline-flex w-full justify-center rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 transition sm:ml-3 sm:w-auto">
                        <i class="fa-solid fa-save mr-2 mt-0.5"></i> Simpan
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition sm:mt-0 sm:w-auto">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
