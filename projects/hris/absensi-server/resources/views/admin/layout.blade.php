<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Absensi - Moduvox</title>
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Select2 & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        :root {
            --brand-blue: #004a99;
            --brand-orange: #f7941d;
            --indigo-primary: #6366f1;
        }
        
        .bg-brand-blue { background-color: var(--brand-blue); }
        .text-brand-blue { color: var(--brand-blue); }
        .bg-brand-orange { background-color: var(--brand-orange); }
        .text-brand-orange { color: var(--brand-orange); }
        
        /* Unified Component Styles */
        .admin-card { 
            background-color: white;
            border-radius: 1.5rem;
            border: 1px solid #f1f5f9;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .admin-table-thead { 
            background-color: #f8fafc;
            color: #64748b;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .admin-table-row { 
            transition: all 0.2s;
            border-bottom: 1px solid #f8fafc;
        }
        .admin-table-row:hover {
            background-color: #f8fafc;
        }
        .admin-input { 
            background-color: rgba(248, 250, 252, 0.8);
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            font-size: 0.875rem;
            padding: 0.6rem 1.25rem;
            font-weight: 500;
            transition: all 0.2s;
            outline: none;
        }
        .admin-input:focus {
            background-color: white;
            border-color: var(--brand-blue);
            box-shadow: 0 0 0 4px rgba(0, 74, 153, 0.05);
        }
        
        /* Unified Badges */
        .badge { 
            padding: 0.35rem 0.75rem;
            font-size: 10px;
            font-weight: 900;
            border-radius: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        .badge-success { background-color: #ecfdf5; color: #047857; }
        .badge-warning { background-color: #fffbeb; color: #b45309; }
        .badge-danger { background-color: #fff1f2; color: #be123c; }
        .badge-info { background-color: #eff6ff; color: #1d4ed8; }
        .badge-slate { background-color: #f1f5f9; color: #64748b; }

        /* Unified Buttons */
        .btn-primary { 
            background-color: var(--brand-blue);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 1.25rem;
            font-size: 0.875rem;
            font-weight: 700;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 74, 153, 0.15);
            border: none;
            cursor: pointer;
        }
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .btn-secondary { 
            background-color: white;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            border-radius: 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Custom SweetAlert2 Styling */
        .swal2-popup {
            border-radius: 2rem !important;
            padding: 2rem !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .swal2-styled.swal2-confirm {
            background-color: var(--indigo-primary) !important;
            border-radius: 1rem !important;
            padding: 0.75rem 2rem !important;
            font-weight: 700 !important;
        }
        .swal2-styled.swal2-cancel {
            border-radius: 1rem !important;
            padding: 0.75rem 2rem !important;
            font-weight: 700 !important;
        }
        /* Select2 Modern Styling */
        .select2-container--default .select2-selection--single {
            background-color: #f8fafc;
            border: none;
            border-radius: 1.25rem;
            height: 3.5rem;
            display: flex;
            align-items: center;
            padding: 0 1rem;
            font-weight: 600;
            color: #334155;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #334155;
            line-height: 3.5rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 3.5rem;
            right: 1rem;
        }
        .select2-dropdown {
            border: 1px solid #f1f5f9;
            border-radius: 1.25rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 0.5rem;
        }
        .select2-search__field {
            border-radius: 0.75rem !important;
            padding: 0.5rem 1rem !important;
            border: 1px solid #e2e8f0 !important;
        }
        .select2-results__option--highlighted[aria-selected] {
            background-color: var(--brand-blue) !important;
            border-radius: 0.75rem;
        }
        .select2-results__option {
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            margin-bottom: 2px;
            font-size: 0.875rem;
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-72 bg-white border-r border-slate-200 flex-shrink-0 flex flex-col">
            <div class="p-8">
                <img src="https://ui-avatars.com/api/?name=Moduvox&background=1e3a8a&color=fff&rounded=true&bold=true&size=128" alt="Logo Moduvox" class="h-12 w-auto object-contain">
            </div>

            <nav class="mt-2 px-6 space-y-2 flex-1">
                <!-- DASHBOARD -->
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                    </svg>
                    Dashboard
                </a>

                <!-- MASTER DATA -->
                <div class="pt-4">
                    <p class="px-4 text-[10px] font-black text-slate-300 uppercase tracking-wider">Manajemen SDM (Master)</p>
                </div>
                <a href="{{ route('admin.employees.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.employees.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Data Karyawan
                </a>
                <a href="{{ route('admin.offices.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.offices.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Data Kantor
                </a>

                <!-- OPERASIONAL -->
                <div class="pt-4">
                    <p class="px-4 text-[10px] font-black text-slate-300 uppercase tracking-wider">Operasional & Absensi</p>
                </div>
                <a href="{{ route('admin.attendances.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.attendances.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Riwayat Kehadiran
                </a>
                <a href="{{ Route::has('admin.leave_requests.index') ? route('admin.leave_requests.index') : '#' }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.leave_requests.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Pengajuan Cuti
                </a>
                <a href="{{ Route::has('admin.permit_requests.index') ? route('admin.permit_requests.index') : '#' }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.permit_requests.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Pengajuan Izin
                </a>
                <a href="{{ Route::has('admin.overtime_requests.index') ? route('admin.overtime_requests.index') : '#' }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.overtime_requests.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Pengajuan Lembur
                </a>
                <a href="{{ Route::has('admin.outside_duty_requests.index') ? route('admin.outside_duty_requests.index') : '#' }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.outside_duty_requests.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.483V4.517a2 2 0 011.553-1.943L9 2l6 2.583L20.447 2c.76 0 1.553.674 1.553 1.517v10.966a2 2 0 01-1.553 1.943L15 19l-6 1z" />
                    </svg>
                    Tugas Luar
                </a>

                <!-- PAYROLL -->
                <div class="pt-4">
                    <p class="px-4 text-[10px] font-black text-slate-300 uppercase tracking-wider">Payroll & Keuangan</p>
                </div>
                <a href="{{ route('admin.salaries.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.salaries.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Gaji (Payroll)
                </a>
                <a href="{{ route('admin.kpi.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.kpi.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Input KPI Staff
                </a>
                <a href="{{ route('admin.master-data.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.master-data.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Golongan & SKG
                </a>
                <a href="{{ route('admin.positions.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.positions.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Struktur & Benefit
                </a>
                <a href="{{ route('admin.payroll-settings.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.payroll-settings.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Pengaturan Payroll
                </a>
                <a href="{{ route('admin.global-allowances.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.global-allowances.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Master Tunjangan & Potongan
                </a>

                <!-- SISTEM -->
                <div class="pt-4">
                    <p class="px-4 text-[10px] font-black text-slate-300 uppercase tracking-wider">Konfigurasi Sistem</p>
                </div>
                <a href="{{ route('admin.banners.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.banners.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Banner Beranda
                </a>

                <a href="{{ route('admin.app-settings.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-2xl transition-all {{ request()->routeIs('admin.app-settings.*') ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-brand-blue' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Pengaturan Mobile App
                </a>
            </nav>

            <div class="p-6 border-t border-slate-100">
                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl">
                    <div class="w-10 h-10 bg-brand-orange rounded-full flex items-center justify-center text-white font-bold">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-800 truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-[10px] text-slate-500">Moduvox</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-10 overflow-y-auto">
            <header class="mb-10 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">@yield('header', 'Dashboard')</h2>
                    <p class="text-slate-400 text-sm mt-1">Human Resource Manajemen System Moduvox</p>
                </div>
                <div class="flex gap-4">
                    @yield('actions')
                    @if(request()->routeIs('admin.employees.index'))
                    <button onclick="window.location.href='#bulk-increment'" class="bg-indigo-600 text-white px-5 py-2.5 rounded-2xl text-sm font-semibold hover:opacity-90 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Kenaikan Massal
                    </button>
                    @endif
                    @if(request()->routeIs('admin.employees.index'))
                    <a href="{{ route('admin.employees.create') }}" class="bg-brand-blue text-white px-5 py-2.5 rounded-2xl text-sm font-semibold hover:opacity-90 transition-all shadow-lg shadow-blue-100 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah Karyawan Baru
                    </a>
                    @endif
                </div>
            </header>

            @if(session('success'))
            <div class="mb-8 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
            @endif

            @yield('content')
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2500,
                customClass: {
                    popup: 'rounded-3xl shadow-2xl border-none',
                    title: 'text-2xl font-bold text-slate-800',
                }
            });
            @endif

            @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: "{{ session('error') }}",
                confirmButtonColor: '#ef4444',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl border-none',
                    confirmButton: 'px-8 py-3 rounded-2xl font-bold uppercase tracking-wider text-sm'
                }
            });
            @endif

            @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Ada Kesalahan Input',
                html: `<div class="text-left mt-4 bg-red-50 p-6 rounded-2xl border border-red-100">
                    <ul class="space-y-2">
                        @foreach($errors->all() as $error)
                            <li class="text-sm text-red-700 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>`,
                confirmButtonColor: '#6366f1',
                confirmButtonText: 'Perbaiki Sekarang',
                customClass: {
                    popup: 'rounded-[2.5rem] shadow-2xl border-none p-10',
                    title: 'text-2xl font-bold text-slate-800',
                    confirmButton: 'px-10 py-4 rounded-2xl font-bold uppercase tracking-wider text-sm mt-4'
                }
            });
            @endif
        });

        function confirmAction(e, message) {
            e.preventDefault();
            const form = e.currentTarget.closest('form');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: message || 'Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        // --- Rupiah Formatter ---
        document.addEventListener('DOMContentLoaded', function() {
            const rupiahInputs = document.querySelectorAll('.rupiah-input');
            
            function formatRupiah(angka, prefix) {
                var number_string = angka.replace(/[^,\d]/g, '').toString(),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
            }

            rupiahInputs.forEach(function(input) {
                // Format initial value if exists
                if(input.value) {
                    // check if it's not a small decimal like 0.05
                    if(!input.value.includes('.') || parseFloat(input.value) >= 100) {
                       input.value = formatRupiah(input.value);
                    }
                }

                input.addEventListener('keyup', function(e) {
                    this.value = formatRupiah(this.value);
                });
            });

            // Strip dots before any form submit
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    const inputs = this.querySelectorAll('.rupiah-input');
                    inputs.forEach(input => {
                        input.value = input.value.replace(/\./g, '');
                    });
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
