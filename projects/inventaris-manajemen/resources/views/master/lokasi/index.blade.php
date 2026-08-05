@extends('layouts.app')

@section('title', 'Master Lokasi')
@section('page-title', 'Master Lokasi')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Lokasi</h3>
        <button type="button" onclick="openModal()" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Tambah Baru
        </button>
    </div>

    <div class="overflow-x-auto">
        <table id="table-lokasi" class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-y border-gray-200">
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Kode</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Nama Lokasi</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                <!-- Loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>

@include('master.lokasi._form')

@endsection

@push('scripts')
<script>
    let table;
    const saveUrl = "{{ route('master.lokasi.store') }}";
    
    $(document).ready(function() {
        table = $('#table-lokasi').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('master.lokasi.data') }}",
            columns: [
                { data: 'kode', name: 'kode' },
                { data: 'nama', name: 'nama' },
                { 
                    data: 'id', 
                    name: 'aksi', 
                    orderable: false, 
                    searchable: false,
                    className: 'text-right',
                    render: function(data) {
                        return `
                            <div class="flex justify-end gap-2">
                                <button onclick="editData(${data})" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button onclick="deleteData(${data})" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
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

        $('#form-lokasi').on('submit', function(e) {
            e.preventDefault();
            
            const id = $('#lokasi_id').val();
            const url = id ? `/master/lokasi/${id}` : saveUrl;
            const method = id ? 'PUT' : 'POST';
            
            showLoading();
            
            $.ajax({
                url: url,
                type: method,
                data: $(this).serialize(),
                success: function(res) {
                    hideLoading();
                    closeModal();
                    table.ajax.reload();
                    toastr.success(res.message);
                },
                error: function(xhr) {
                    hideLoading();
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        for (let field in errors) {
                            $(`#error-${field}`).text(errors[field][0]).removeClass('hidden');
                        }
                    } else {
                        toastr.error('Terjadi kesalahan sistem');
                    }
                }
            });
        });
        
        $('#form-lokasi input').on('input', function() {
            $(this).siblings('.error-message').addClass('hidden');
        });
    });

    function openModal() {
        $('#form-lokasi')[0].reset();
        $('#lokasi_id').val('');
        $('#modal-title').text('Tambah Lokasi');
        $('.error-message').addClass('hidden');
        $('#modal-lokasi').removeClass('hidden');
        setTimeout(() => {
            $('#modal-lokasi-panel').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
        }, 10);
    }

    function closeModal() {
        $('#modal-lokasi-panel').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => {
            $('#modal-lokasi').addClass('hidden');
        }, 200);
    }

    function editData(id) {
        showLoading();
        $.get(`/master/lokasi/${id}`, function(res) {
            hideLoading();
            if (res.success) {
                const data = res.data;
                $('#lokasi_id').val(data.id);
                $('#kode').val(data.kode);
                $('#nama').val(data.nama);
                
                $('#modal-title').text('Edit Lokasi');
                $('.error-message').addClass('hidden');
                
                $('#modal-lokasi').removeClass('hidden');
                setTimeout(() => {
                    $('#modal-lokasi-panel').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
                }, 10);
            }
        }).fail(function() {
            hideLoading();
            toastr.error('Gagal mengambil data');
        });
    }

    function deleteData(id) {
        confirmDelete(`/master/lokasi/${id}`, 'table-lokasi');
    }
</script>
@endpush
