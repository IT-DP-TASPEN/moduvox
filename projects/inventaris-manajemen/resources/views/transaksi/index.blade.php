@extends('layouts.app')

@section('title', 'Daftar Inventaris')
@section('page-title', 'Data Inventaris')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Filter Pencarian</h3>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @if(auth()->user()->isHeadOffice())
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Kantor Cabang</label>
            <select id="filter_kantor" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                <option value="">Semua Kantor</option>
                @foreach($kantors as $kantor)
                    <option value="{{ $kantor->id }}">{{ $kantor->kode }} - {{ $kantor->nama }}</option>
                @endforeach
            </select>
        </div>
        @endif
        
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Golongan Aset</label>
            <select id="filter_golongan" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                <option value="">Semua Golongan</option>
                @foreach($golongans as $gol)
                    <option value="{{ $gol->id }}">{{ $gol->kode }} - {{ $gol->nama }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Aset</label>
            <select id="filter_jenis" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                <option value="">Semua Jenis</option>
                @foreach($jenises as $jns)
                    <option value="{{ $jns->id }}">{{ $jns->kode }} - {{ $jns->nama }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select id="filter_status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                <option value="">Semua Status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <h3 class="text-xl font-bold text-gray-800"><i class="fa-solid fa-right-left mr-2 text-amber-500"></i> Histori Transaksi Mutasi</h3>
        <div class="flex gap-2 w-full md:w-auto">
            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="w-full md:w-auto bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center justify-center gap-2 shadow-sm">
                <i class="fa-solid fa-file-excel"></i> Import Data
            </button>
            <a href="javascript:void(0)" onclick="Swal.fire('Info', 'Fitur Mutasi Baru sedang dalam tahap pengembangan.', 'info')" class="w-full md:w-auto bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center justify-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i> Mutasi Baru
            </a>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table id="table-transaksi" class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-y border-gray-200">
                <tr>
                    <th scope="col" class="px-4 py-3 font-semibold w-12 text-center">No</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Tgl Transaksi</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Rekening / Aset</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Jenis</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Kantor (Tujuan/Asal)</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Keterangan</th>
                    <th scope="col" class="px-4 py-3 font-semibold">User / Status</th>
                    <th scope="col" class="px-4 py-3 font-semibold text-right w-16"><i class="fa-solid fa-ellipsis-vertical"></i></th>
                </tr>
            </thead>
            <tbody>
                <!-- DataTables will populate this -->
            </tbody>
        </table>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('importModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('transaksi.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fa-solid fa-file-excel text-green-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Import Data Transaksi</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-4">Upload file Excel (.xlsx atau .csv) untuk mengimpor histori transaksi/mutasi aset.</p>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File Excel</label>
                                    <input type="file" name="file" accept=".xlsx,.csv,.xls" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Import Data
                    </button>
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let table;
    
    $(document).ready(function() {
        table = $('#table-transaksi').DataTable({
            processing: true,
            serverSide: true,
            order: [[1, 'desc']],
            ajax: {
                url: "{{ route('transaksi.data') }}",
                data: function(d) {
                    d.kantor_id = $('#filter_kantor').val();
                }
            },
            columns: [
                { 
                    data: 'id', 
                    name: 'id', 
                    orderable: false, 
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'format_tanggal', name: 'tgl_mutasi' },
                { data: 'rekening', name: 'rekening', render: function(data, type, row) {
                    let text = `<div class="font-mono text-xs font-medium text-gray-600">${data}</div>`;
                    if (row.nama_aset) text += `<div class="text-xs text-gray-500">${row.nama_aset}</div>`;
                    return text;
                }},
                { data: 'jenis_label', name: 'jenis_mutasi', orderable: false, searchable: false },
                { data: 'kantor_info', name: 'kantor_tujuan_id', orderable: false, searchable: false },
                { data: 'keterangan_short', name: 'keterangan' },
                { data: 'nama_user', name: 'user_id', render: function(data, type, row) {
                    let text = `<div class="font-medium text-gray-800">${data}</div>`;
                    if (row.status_label) text += `<div class="mt-1">${row.status_label}</div>`;
                    return text;
                }},
                { 
                    data: 'id', 
                    name: 'aksi', 
                    orderable: false, 
                    searchable: false,
                    className: 'text-right',
                    render: function(data) {
                        return `
                            <div class="flex justify-end gap-1">
                                <a href="/transaksi/${data}" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 flex items-center justify-center transition" title="Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </div>
                        `;
                    }
                }
            ],
            dom: '<"flex flex-col sm:flex-row justify-between items-center mb-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-4"ip>',
            drawCallback: function() {
                $('.dataTables_length select').addClass('border-gray-300 rounded-lg text-sm py-1.5 focus:ring-amber-500 focus:border-amber-500 mx-2');
                $('.dataTables_filter input').addClass('border-gray-300 rounded-lg text-sm py-1.5 px-3 focus:ring-amber-500 focus:border-amber-500 ml-2');
                $('.paginate_button').addClass('px-3 py-1 border border-gray-200 bg-white text-sm hover:bg-gray-50');
                $('.paginate_button.current').addClass('bg-amber-50 text-amber-600 border-amber-200 font-medium');
            }
        });

        // Trigger filter reload
        $('#filter_kantor, #filter_golongan, #filter_jenis, #filter_status').on('change', function() {
            table.ajax.reload();
            updateSelectedCount();
        });

        // Check All logic
        $('#check-all').on('click', function() {
            $('.row-checkbox').prop('checked', this.checked);
            updateSelectedCount();
        });

        // Individual checkbox change
        $('#table-inventaris tbody').on('change', '.row-checkbox', function() {
            updateSelectedCount();
            if(!this.checked) {
                $('#check-all').prop('checked', false);
            }
        });
    });

    function updateSelectedCount() {
        $('#selected-count').text($('.row-checkbox:checked').length);
    }

    function cetakLabelMassal() {
        let checked = [];
        $('.row-checkbox:checked').each(function() {
            checked.push($(this).val());
        });

        if(checked.length === 0) {
            toastr.warning('Pilih minimal satu aset untuk dicetak.');
            return;
        }

        // Simpan id yang dipilih ke hidden input di dalam modal
        $('#print_ids').val(checked.join(','));
        // Buka modal
        document.getElementById('printModal').classList.remove('hidden');
    }

    function submitPrint() {
        let ids = $('#print_ids').val();
        let template = $('input[name="print_template"]:checked').val();
        let url = `{{ route('inventaris.print-massal') }}?ids=${ids}&template=${template}`;
        
        // Tutup modal
        document.getElementById('printModal').classList.add('hidden');
        
        // Buka tab baru
        window.open(url, '_blank');
    }

    function deleteData(id) {
        confirmDelete(`/inventaris/${id}`, 'table-inventaris');
    }
</script>
@endpush

<!-- Print Modal -->
<div id="printModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('printModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fa-solid fa-print text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Cetak Label Massal</h3>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 mb-4">Pilih template cetak yang akan digunakan untuk mencetak barcode.</p>
                            
                            <input type="hidden" id="print_ids" value="">
                            
                            <div class="space-y-3">
                                @if(config('label.templates'))
                                    @foreach(config('label.templates') as $key => $template)
                                    <label class="flex items-start p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <input type="radio" name="print_template" value="{{ $key }}" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ $loop->first ? 'checked' : '' }}>
                                        </div>
                                        <div class="ml-3">
                                            <span class="block text-sm font-medium text-gray-900">{{ $template['name'] }}</span>
                                            <span class="block text-xs text-gray-500">{{ $template['description'] }}</span>
                                        </div>
                                    </label>
                                    @endforeach
                                @else
                                    <div class="text-sm text-red-500">Konfigurasi template tidak ditemukan.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="submitPrint()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Lanjutkan Cetak
                </button>
                <button type="button" onclick="document.getElementById('printModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>
