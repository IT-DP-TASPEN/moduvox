<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inventaris') - PT Moduvox Tech ID</title>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS (Vite) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.11/css/dataTables.tailwindcss.min.css">

    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        @include('layouts.partials.sidebar')

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col">
            {{-- Topbar --}}
            @include('layouts.partials.topbar')

            {{-- Breadcrumb --}}
            <div class="px-6 sm:px-8 py-4 bg-white border-b border-gray-200">
                @yield('breadcrumb')
            </div>

            {{-- Page Content --}}
            <main class="flex-1 p-6 sm:p-8 overflow-x-hidden">
                <div class="max-w-7xl mx-auto w-full">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    {{-- Loading Overlay --}}
    <div id="loading-overlay" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[9999] flex items-center justify-center hidden transition-opacity duration-300">
        <div class="bg-white rounded-xl p-8 flex flex-col items-center gap-4 shadow-2xl border border-gray-100 min-w-[200px]">
            <div class="w-12 h-12 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin"></div>
            <span class="text-sm font-medium text-gray-600">Memproses...</span>
        </div>
    </div>

    {{-- Modal Container --}}
    <div id="modal-container"></div>

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    {{-- DataTables --}}
    <script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Toastr --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    {{-- DataTables Global Language (Indonesian) --}}
    <script>
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                "emptyTable": "Tidak ada data yang tersedia",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "lengthMenu": "Tampilkan _MENU_ data",
                "loadingRecords": "Memuat...",
                "processing": "Sedang memproses...",
                "search": "Cari:",
                "zeroRecords": "Tidak ditemukan data yang cocok",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                },
                "aria": {
                    "sortAscending": ": aktifkan untuk mengurutkan kolom naik",
                    "sortDescending": ": aktifkan untuk mengurutkan kolom turun"
                }
            }
        });
    </script>

    <script>
        // Global AJAX Setup
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // Toastr defaults
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4000,
        };

        // Loading overlay helpers
        function showLoading() { $('#loading-overlay').removeClass('hidden').addClass('flex'); }
        function hideLoading() { $('#loading-overlay').removeClass('flex').addClass('hidden'); }

        // SweetAlert delete confirmation
        function confirmDelete(url, tableId) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        success: function (res) {
                            hideLoading();
                            toastr.success(res.message || 'Data berhasil dihapus');
                            if (tableId) $('#' + tableId).DataTable().ajax.reload();
                        },
                        error: function (xhr) {
                            hideLoading();
                            toastr.error(xhr.responseJSON?.message || 'Gagal menghapus data');
                        },
                    });
                }
            });
        }
    </script>

    <script>
        @if(Session::has('success'))
            toastr.success("{{ Session::get('success') }}");
        @endif
        @if(Session::has('error'))
            toastr.error("{{ Session::get('error') }}");
        @endif
        @if(Session::has('info'))
            toastr.info("{{ Session::get('info') }}");
        @endif
        @if(Session::has('warning'))
            toastr.warning("{{ Session::get('warning') }}");
        @endif
    </script>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.0/dist/cdn.min.js"></script>

    @stack('scripts')
</body>
</html>
