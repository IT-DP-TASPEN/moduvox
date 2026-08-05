@extends('layouts.app')

@section('title', 'Data Penyusutan')
@section('page-title', 'Data Inventaris Penyusutan')

@section('content')
<div class="card-premium mb-6">
    <div class="flex justify-between items-center mb-5">
        <h3 class="text-lg font-bold text-gray-900 tracking-tight">Filter Pencarian</h3>
        @if(auth()->user()->hasRole('Super Admin'))
        <button onclick="document.getElementById('modal-import-penyusutan').classList.remove('hidden')" class="btn-premium btn-primary text-sm flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            Import Data Histori
        </button>
        @endif
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @if(auth()->user()->isHeadOffice())
        <div>
            <label class="label-premium">Kantor Cabang</label>
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
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-gray-900 tracking-tight">Aset Dalam Masa Penyusutan</h3>
    </div>

    <div class="overflow-x-auto">
        <table id="table-penyusutan" class="w-full text-left border-collapse text-[13px]">
            <thead>
                <tr class="bg-gray-50 border-y border-gray-200">
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Periode</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">No. Rekening</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Nama Aset</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Kantor</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Gol</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-right whitespace-nowrap">Beban Penyusutan</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-right whitespace-nowrap">Akum. Penyusutan</th>
                    <th class="px-3 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-right whitespace-nowrap">Nilai Buku</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Import -->
<div id="modal-import-penyusutan" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('modal-import-penyusutan').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative z-10 inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('penyusutan_list.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Import Data Histori Penyusutan</h3>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">File Excel (.xlsx)</label>
                                <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 border border-gray-300 rounded-lg focus:outline-none">
                                <p class="mt-2 text-xs text-gray-500">Peringatan: Karena data berjumlah besar (~93k baris), proses import ini mungkin memakan waktu beberapa menit. Jangan tutup halaman saat proses berlangsung.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">Import</button>
                    <button type="button" onclick="document.getElementById('modal-import-penyusutan').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
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
        table = $('#table-penyusutan').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('penyusutan_list.data') }}",
                data: function(d) {
                    d.kantor_id = $('#filter_kantor').val();
                }
            },
            columns: [
                { data: 'periode', name: 'batch.periode_ym', className: 'px-3 py-2 whitespace-nowrap align-top text-gray-600 font-medium' },
                { data: 'rekening', name: 'inventaris.rekening', className: 'px-3 py-2 whitespace-nowrap align-top', render: function(data, type, row) {
                    if (!row.inventaris_id) return data;
                    return `<a href="/inventaris/${row.inventaris_id}" class="font-mono text-[11px] text-primary-600 hover:text-primary-700 hover:underline transition-colors">${data}</a>`;
                }},
                { data: 'nama_aset', name: 'inventaris.nama_aset', className: 'px-3 py-2 align-top max-w-[220px]', render: function(data, type, row) {
                    let text = `<div class="font-medium text-gray-800 truncate" title="${data}">${data}</div>`;
                    if (row.merk) text += `<div class="text-[11px] text-gray-400 truncate">${row.merk}</div>`;
                    return text;
                }},
                { data: 'nama_kantor', name: 'kantor.nama', className: 'px-3 py-2 whitespace-nowrap align-top text-gray-600' },
                { data: 'nama_golongan', name: 'inventaris.golongan_id', className: 'px-3 py-2 whitespace-nowrap align-top', render: function(data) {
                    return `<span class="inline-block px-2 py-0.5 text-[11px] font-semibold rounded bg-slate-100 text-slate-600">${data}</span>`;
                }},
                { data: 'format_beban', name: 'beban_bulan_ini', className: 'px-3 py-2 text-right whitespace-nowrap align-top text-gray-700' },
                { data: 'format_akumulasi', name: 'akumulasi', className: 'px-3 py-2 text-right text-red-600 font-medium whitespace-nowrap align-top' },
                { data: 'format_nilai_buku', name: 'nilai_buku_sesudah', className: 'px-3 py-2 text-right font-bold whitespace-nowrap align-top text-gray-700' }
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
