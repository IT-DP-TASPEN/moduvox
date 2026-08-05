@extends('layouts.app')

@section('title', 'Daftar Inventaris')
@section('page-title', 'Data Inventaris')

@section('content')
<div class="card-premium mb-6">
    <div class="flex justify-between items-center mb-5">
        <h3 class="text-lg font-bold text-gray-900 tracking-tight">Filter Pencarian</h3>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        @if(auth()->user()->isHeadOffice())
        <div>
            <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Kantor Cabang</label>
            <select id="filter_kantor" class="input-premium">
                <option value="">Semua Kantor</option>
                @foreach($kantors as $kantor)
                    <option value="{{ $kantor->id }}">{{ $kantor->kode }} - {{ $kantor->nama }}</option>
                @endforeach
            </select>
        </div>
        @endif
        
        <div>
            <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Golongan Aset</label>
            <select id="filter_golongan" class="input-premium">
                <option value="">Semua Golongan</option>
                @foreach($golongans as $gol)
                    <option value="{{ $gol->id }}">{{ $gol->kode }} - {{ $gol->nama }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Jenis Aset</label>
            <select id="filter_jenis" class="input-premium">
                <option value="">Semua Jenis</option>
                @foreach($jenises as $jns)
                    <option value="{{ $jns->id }}">{{ $jns->kode }} - {{ $jns->nama }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Status</label>
            <select id="filter_status" class="input-premium">
                <option value="">Semua Status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="card-premium">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <h3 class="text-xl font-bold text-gray-900 tracking-tight"><i class="fa-solid fa-boxes-stacked mr-2 text-primary-600"></i> Master Inventaris</h3>
        <div class="flex gap-3 w-full md:w-auto">
            <button type="button" onclick="cetakLabelMassal()" class="btn-secondary w-full md:w-auto">
                <i class="fa-solid fa-qrcode mr-2"></i> Cetak Label (<span id="selected-count">0</span>)
            </button>
            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="btn-secondary text-success-600 hover:text-success-700 w-full md:w-auto">
                <i class="fa-solid fa-file-excel mr-2"></i> Import Data
            </button>
            <a href="{{ route('inventaris.create') }}" class="btn-primary w-full md:w-auto">
                <i class="fa-solid fa-plus mr-2"></i> Tambah Aset Baru
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="table-inventaris" class="w-full text-left border-collapse whitespace-nowrap text-[13px]">
            <thead>
                <tr class="bg-gray-50 border-y border-gray-200">
                    <th class="px-3 py-3 text-center w-8"><input type="checkbox" id="check-all" class="rounded border-gray-300 text-primary-600 focus:ring-primary-600"></th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">No. Rekening</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Nama Aset</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Kantor</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Gol</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Tgl Perolehan</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-right">Nilai Buku</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-center">Status</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-center w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <!-- Loaded via AJAX -->
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
            <form action="{{ route('inventaris.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fa-solid fa-file-excel text-green-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Import Master Inventaris</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-4">Upload file Excel (.xlsx atau .csv) untuk mengimpor data massal.</p>
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
@endsection

@push('scripts')
<script>
    let table;
    
    $(document).ready(function() {
        table = $('#table-inventaris').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('inventaris.data') }}",
                data: function(d) {
                    d.kantor_id = $('#filter_kantor').val();
                    d.golongan_id = $('#filter_golongan').val();
                    d.jenis_id = $('#filter_jenis').val();
                    d.status = $('#filter_status').val();
                }
            },
            columns: [
                { 
                    data: 'id', 
                    name: 'id', 
                    orderable: false, 
                    searchable: false,
                    className: 'px-3 py-2 text-center',
                    render: function(data) {
                        return `<input type="checkbox" class="row-checkbox rounded border-gray-300 text-primary-600 focus:ring-primary-600" value="${data}">`;
                    }
                },
                { data: 'rekening', name: 'rekening', className: 'px-3 py-2', render: function(data) {
                    return `<span class="font-mono text-[11px] text-gray-500">${data}</span>`;
                }},
                { data: 'nama_aset', name: 'nama_aset', className: 'px-3 py-2 max-w-[220px]', render: function(data, type, row) {
                    let text = `<div class="font-medium text-gray-800 truncate" title="${data}">${data}</div>`;
                    if (row.merk) text += `<div class="text-[11px] text-gray-400 truncate">${row.merk}</div>`;
                    return text;
                }},
                { data: 'nama_kantor', name: 'kantor_id', className: 'px-3 py-2', render: function(data) {
                    return `<span class="text-gray-600">${data}</span>`;
                }},
                { data: 'nama_golongan', name: 'golongan_id', className: 'px-3 py-2', render: function(data) {
                    return `<span class="inline-block px-2 py-0.5 text-[11px] font-semibold rounded bg-slate-100 text-slate-600">${data}</span>`;
                }},
                { data: 'format_tgl_perolehan', name: 'tgl_perolehan', className: 'px-3 py-2 text-gray-500' },
                { data: 'format_nilai_buku', name: 'nilai_buku', className: 'px-3 py-2 text-right font-medium text-gray-700' },
                { data: 'status_label', name: 'status', className: 'px-3 py-2 text-center' },
                { 
                    data: 'id', 
                    name: 'aksi', 
                    orderable: false, 
                    searchable: false,
                    className: 'px-3 py-2 text-center',
                    render: function(data) {
                        return `
                            <div class="flex justify-center gap-1">
                                <a href="/inventaris/${data}" class="w-7 h-7 rounded-md bg-gray-50 text-gray-500 hover:bg-gray-100 flex items-center justify-center transition text-xs" title="Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="/inventaris/${data}/edit" class="w-7 h-7 rounded-md bg-blue-50 text-blue-500 hover:bg-blue-100 flex items-center justify-center transition text-xs" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <button onclick="deleteData(${data})" class="w-7 h-7 rounded-md bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition text-xs" title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            dom: '<"flex flex-col sm:flex-row justify-between items-center mb-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-4"ip>',
            drawCallback: function() {
                $('.dataTables_length select').addClass('border-gray-300 rounded-lg text-sm py-1.5 focus:ring-primary-500 focus:border-primary-500 mx-2');
                $('.dataTables_filter input').addClass('border-gray-300 rounded-lg text-sm py-1.5 px-3 focus:ring-primary-500 focus:border-primary-500 ml-2');
                $('.paginate_button').addClass('px-3 py-1.5 border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors cursor-pointer');
                $('.paginate_button.current').addClass('bg-primary-50 text-primary-700 border-primary-200');
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
