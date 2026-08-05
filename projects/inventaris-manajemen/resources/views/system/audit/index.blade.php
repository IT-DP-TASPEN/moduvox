@extends('layouts.app')

@section('title', 'Audit Trail')
@section('page-title', 'Sistem: Audit Trail')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800">Catatan Aktivitas Sistem</h3>
        <p class="text-sm text-gray-500">Memantau riwayat perubahan (tambah, ubah, hapus) data pada aplikasi.</p>
    </div>

    <div class="overflow-x-auto">
        <table id="table-audit" class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-gray-50 border-y border-gray-200">
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Waktu</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">User</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Tabel / Modul</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Data ID</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Perubahan (JSON)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                <!-- Loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#table-audit').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('system.audit.data') }}",
            order: [[0, 'desc']],
            columns: [
                { data: 'waktu', name: 'created_at' },
                { data: 'user_name', name: 'user_id' },
                { data: 'action_badge', name: 'action', className: 'text-center' },
                { data: 'model_type', name: 'table_name', render: function(data) {
                    return `<span class="font-mono text-xs text-gray-600">${data}</span>`;
                }},
                { data: 'model_id', name: 'record_id', className: 'text-center font-mono text-xs' },
                { data: 'changes', name: 'changes', orderable: false, render: function(data) {
                    if(!data) return '-';
                    return `<div class="max-w-xs overflow-hidden text-ellipsis whitespace-nowrap text-xs text-gray-500" title='${data}'>${data}</div>`;
                }},
            ],
            dom: '<"flex flex-col sm:flex-row justify-between items-center mb-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-4"ip>',
            drawCallback: function() {
                $('.dataTables_length select').addClass('border-gray-300 rounded-lg text-sm py-1.5 mx-2');
                $('.dataTables_filter input').addClass('border-gray-300 rounded-lg text-sm py-1.5 px-3 ml-2');
                $('.paginate_button').addClass('px-3 py-1 border border-gray-200 bg-white text-sm hover:bg-gray-50');
                $('.paginate_button.current').addClass('bg-amber-50 text-amber-600 border-amber-200 font-medium');
            }
        });
    });
</script>
@endpush
