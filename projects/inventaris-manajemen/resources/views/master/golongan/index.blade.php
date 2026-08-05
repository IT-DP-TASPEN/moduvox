@extends('layouts.app')

@section('title', 'Master Golongan Aset')
@section('page-title', 'Master Golongan Aset')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Golongan Aset</h3>
        <button type="button" onclick="openModal()" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Tambah Baru
        </button>
    </div>

    <div class="overflow-x-auto">
        <table id="table-golongan" class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-y border-gray-200">
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Kode</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Nama Golongan</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Masa Manfaat (Bulan)</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-center">Akun Debet</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-center">Akun Kredit</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                <!-- Loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>

@include('master.golongan._form')

@endsection

@push('scripts')
<script>
    let table;
    const saveUrl = "{{ route('master.golongan.store') }}";
    
    $(document).ready(function() {
        table = $('#table-golongan').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('master.golongan.data') }}",
            columns: [
                { data: 'kode', name: 'kode' },
                { data: 'nama', name: 'nama' },
                { 
                    data: 'umur_standar', 
                    name: 'umur_standar',
                    className: 'text-right',
                    render: function(data) {
                        return data + ' Bulan';
                    }
                },
                { data: 'akun_debet', name: 'akun_debet', className: 'text-center', defaultContent: '-' },
                { data: 'akun_kredit', name: 'akun_kredit', className: 'text-center', defaultContent: '-' },
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
                // Tailwind styling overrides for datatable elements
                $('.dataTables_length select').addClass('border-gray-300 rounded-lg text-sm py-1.5 focus:ring-amber-500 focus:border-amber-500 mx-2');
                $('.dataTables_filter input').addClass('border-gray-300 rounded-lg text-sm py-1.5 px-3 focus:ring-amber-500 focus:border-amber-500 ml-2');
                $('.paginate_button').addClass('px-3 py-1 border border-gray-200 bg-white text-sm hover:bg-gray-50');
                $('.paginate_button.current').addClass('bg-amber-50 text-amber-600 border-amber-200 font-medium');
            }
        });

        // Form Submit
        $('#form-golongan').on('submit', function(e) {
            e.preventDefault();
            
            const id = $('#golongan_id').val();
            const url = id ? `/master/golongan/${id}` : saveUrl;
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
        
        // Reset errors on input
        $('#form-golongan input').on('input', function() {
            $(this).siblings('.error-message').addClass('hidden');
        });
    });

    function openModal() {
        $('#form-golongan')[0].reset();
        $('#golongan_id').val('');
        $('#modal-title').text('Tambah Golongan Aset');
        $('.error-message').addClass('hidden');
        $('#modal-golongan').removeClass('hidden');
        setTimeout(() => {
            $('#modal-golongan-panel').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
        }, 10);
    }

    function closeModal() {
        $('#modal-golongan-panel').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => {
            $('#modal-golongan').addClass('hidden');
        }, 200);
    }

    function editData(id) {
        showLoading();
        $.get(`/master/golongan/${id}`, function(res) {
            hideLoading();
            if (res.success) {
                const data = res.data;
                $('#golongan_id').val(data.id);
                $('#kode').val(data.kode);
                $('#nama').val(data.nama);
                $('#umur_standar').val(data.umur_standar);
                $('#akun_debet').val(data.akun_debet);
                $('#akun_kredit').val(data.akun_kredit);
                
                $('#modal-title').text('Edit Golongan Aset');
                $('.error-message').addClass('hidden');
                
                $('#modal-golongan').removeClass('hidden');
                setTimeout(() => {
                    $('#modal-golongan-panel').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
                }, 10);
            }
        }).fail(function() {
            hideLoading();
            toastr.error('Gagal mengambil data');
        });
    }

    function deleteData(id) {
        confirmDelete(`/master/golongan/${id}`, 'table-golongan');
    }
</script>
@endpush
