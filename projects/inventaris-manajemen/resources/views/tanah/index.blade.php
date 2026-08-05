@extends('layouts.app')

@section('title', 'Data Tanah')
@section('page-title', 'Data Inventaris Tanah')

@section('content')
<div class="card-premium mb-6">
    <div class="flex justify-between items-center mb-5">
        <h3 class="text-lg font-bold text-gray-900 tracking-tight">Filter Pencarian</h3>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
    </div>
</div>

<div class="card-premium">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <h3 class="text-xl font-bold text-gray-900 tracking-tight"><i class="fa-solid fa-map-location-dot mr-2 text-primary-600"></i> Daftar Aset Tanah</h3>
        <div class="flex gap-3 w-full md:w-auto">
            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="btn-secondary text-success-600 hover:text-success-700 w-full md:w-auto">
                <i class="fa-solid fa-file-excel mr-2"></i> Import Data
            </button>
            <a href="{{ route('inventaris.create') }}" class="btn-primary w-full md:w-auto">
                <i class="fa-solid fa-plus mr-2"></i> Tambah Tanah
            </a>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table id="table-tanah" class="w-full text-left border-collapse whitespace-nowrap text-[13px]">
            <thead>
                <tr class="bg-gray-50 border-y border-gray-200">
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Rekening</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Nama Aset</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">No. Sertifikat</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Kantor</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-right">Nilai Buku</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-center w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
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
            <form action="{{ route('tanah.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fa-solid fa-file-excel text-green-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Import Data Tanah</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-4">Upload file Excel (.xlsx atau .csv) untuk mengimpor data tanah & bangunan.</p>
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
        table = $('#table-tanah').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('tanah.data') }}",
                data: function(d) {
                    d.kantor_id = $('#filter_kantor').val();
                }
            },
            columns: [
                { data: 'rekening', name: 'rekening', className: 'px-3 py-2', render: function(data) {
                    return `<span class="font-mono text-[11px] text-gray-500">${data}</span>`;
                }},
                { data: 'nama_aset', name: 'nama_aset', className: 'px-3 py-2 max-w-[220px]', render: function(data, type, row) {
                    let text = `<div class="font-medium text-gray-800 truncate" title="${data}">${data}</div>`;
                    if (row.merk) text += `<div class="text-[11px] text-gray-400 truncate">${row.merk}</div>`;
                    return text;
                }},
                { data: 'no_sertifikat', name: 'tanah.no_shm', className: 'px-3 py-2 text-gray-600' },
                { data: 'nama_kantor', name: 'kantor_id', className: 'px-3 py-2 text-gray-600' },
                { data: 'format_nilai_buku', name: 'nilai_buku', className: 'px-3 py-2 text-right font-medium text-gray-700' },
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
                                <a href="/tanah/${data}/edit" class="w-7 h-7 rounded-md bg-blue-50 text-blue-500 hover:bg-blue-100 flex items-center justify-center transition text-xs" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
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

        $('#filter_kantor').on('change', function() {
            table.ajax.reload();
        });
    });
</script>
@endpush
