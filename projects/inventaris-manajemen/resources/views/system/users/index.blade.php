@extends('layouts.app')

@section('title', 'User & Role')
@section('page-title', 'Sistem: User & Role')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-800">Manajemen Pengguna</h3>
        <button type="button" onclick="openModal()" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Tambah User
        </button>
    </div>

    <div class="overflow-x-auto">
        <table id="table-users" class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-gray-50 border-y border-gray-200">
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Nama Lengkap</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Kantor Cabang</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Role Akses</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                <!-- Loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>

{{-- Modal --}}
<div id="modal-user" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div id="modal-user-panel" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full scale-95 opacity-0 duration-200">
            <form id="form-user">
                <input type="hidden" id="user_id" name="id">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-xl leading-6 font-semibold text-gray-900" id="modal-title">Tambah User</h3>
                        <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" required>
                            <p id="error-name" class="error-message text-red-500 text-xs mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" required autocomplete="off">
                            <p id="error-email" class="error-message text-red-500 text-xs mt-1 hidden"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-gray-400 text-xs font-normal" id="password-hint"></span></label>
                            <input type="password" id="password" name="password" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" autocomplete="new-password">
                            <p id="error-password" class="error-message text-red-500 text-xs mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kantor Cabang</label>
                            <select id="kantor_id" name="kantor_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                                <option value="">Kantor Pusat (Bisa lihat semua cabang)</option>
                                @foreach($kantors as $k)
                                    <option value="{{ $k->id }}">{{ $k->kode }} - {{ $k->nama }}</option>
                                @endforeach
                            </select>
                            <p id="error-kantor_id" class="error-message text-red-500 text-xs mt-1 hidden"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role Akses <span class="text-red-500">*</span></label>
                            <select id="role" name="role" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" required>
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                            <p id="error-role" class="error-message text-red-500 text-xs mt-1 hidden"></p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition">
                        Simpan
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
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
    const saveUrl = "{{ route('system.users.store') }}";
    
    $(document).ready(function() {
        table = $('#table-users').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('system.users.data') }}",
            columns: [
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'nama_kantor', name: 'kantor_id' },
                { data: 'role_names', name: 'roles.name', render: data => `<span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">${data}</span>` },
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
                $('.dataTables_length select').addClass('border-gray-300 rounded-lg text-sm py-1.5 mx-2');
                $('.dataTables_filter input').addClass('border-gray-300 rounded-lg text-sm py-1.5 px-3 ml-2');
                $('.paginate_button').addClass('px-3 py-1 border border-gray-200 bg-white text-sm hover:bg-gray-50');
                $('.paginate_button.current').addClass('bg-amber-50 text-amber-600 border-amber-200 font-medium');
            }
        });

        $('#form-user').on('submit', function(e) {
            e.preventDefault();
            
            const id = $('#user_id').val();
            const url = id ? `/system/users/${id}` : saveUrl;
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
                        toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan sistem');
                    }
                }
            });
        });
        
        $('#form-user input, #form-user select').on('input change', function() {
            $(this).siblings('.error-message').addClass('hidden');
        });
    });

    function openModal() {
        $('#form-user')[0].reset();
        $('#user_id').val('');
        $('#password').prop('required', true);
        $('#password-hint').text('');
        $('#modal-title').text('Tambah User');
        $('.error-message').addClass('hidden');
        $('#modal-user').removeClass('hidden');
        setTimeout(() => { $('#modal-user-panel').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100'); }, 10);
    }

    function closeModal() {
        $('#modal-user-panel').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => { $('#modal-user').addClass('hidden'); }, 200);
    }

    function editData(id) {
        showLoading();
        $.get(`/system/users/${id}`, function(res) {
            hideLoading();
            if (res.success) {
                const data = res.data;
                $('#user_id').val(data.id);
                $('#name').val(data.name);
                $('#email').val(data.email);
                $('#kantor_id').val(data.kantor_id);
                $('#role').val(data.role);
                $('#password').prop('required', false);
                $('#password-hint').text('(Kosongkan jika tidak ingin mengubah password)');
                
                $('#modal-title').text('Edit User');
                $('.error-message').addClass('hidden');
                
                $('#modal-user').removeClass('hidden');
                setTimeout(() => { $('#modal-user-panel').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100'); }, 10);
            }
        }).fail(function() {
            hideLoading();
            toastr.error('Gagal mengambil data');
        });
    }

    function deleteData(id) {
        confirmDelete(`/system/users/${id}`, 'table-users');
    }
</script>
@endpush
