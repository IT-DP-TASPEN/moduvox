@extends('admin.layout')

@section('header')
<div class="flex items-center gap-4">
    <a href="{{ route('admin.employees.index') }}" class="p-2 hover:bg-slate-100 rounded-full transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Tambah Karyawan Baru</h1>
        <p class="text-sm text-slate-500">Daftarkan anggota tim baru ke dalam sistem</p>
    </div>
</div>
@endsection

@section('content')
<form action="{{ route('admin.employees.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8 pb-20">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- LEFT: SECTIONS NAV (Sticky) -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 sticky top-8">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6 px-2">Navigasi Form</h4>
                <nav class="space-y-1">
                    <a href="#section-core" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-blue-600 bg-blue-50 transition-all">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span> Akun & Data Utama
                    </a>
                    <a href="#section-personal" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                        <span class="w-2 h-2 rounded-full bg-slate-200"></span> Personal
                    </a>
                    <a href="#section-employment" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                        <span class="w-2 h-2 rounded-full bg-slate-200"></span> Kepegawaian
                    </a>
                </nav>
                
                <hr class="my-6 border-slate-50">
                
                <button type="submit" class="w-full py-4 rounded-2xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Daftarkan Karyawan
                </button>
            </div>
        </div>

        <!-- RIGHT: FORM FIELDS -->
        <div class="lg:col-span-9 space-y-8">
            
            <!-- SECTION 1: CORE DATA -->
            <div id="section-core" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-10">
                <h3 class="text-xl font-bold text-slate-800 mb-8 flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </span>
                    Informasi Akun & Akses
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">NIP / Employee ID</label>
                        <input type="text" name="employee_id" value="{{ old('employee_id') }}" placeholder="Contoh: 20260001" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Email Perusahaan</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="budi@perusahaan.com" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Password Awal</label>
                        <input type="password" name="password" placeholder="Min. 6 Karakter" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Kantor Utama</label>
                        <select name="office_id" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            <option value="">Pilih Kantor</option>
                            @foreach($offices as $off)
                            <option value="{{ $off->id }}">{{ $off->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Divisi</label>
                        <select name="division_name" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            <option value="">Pilih Divisi</option>
                            @foreach($divisions as $div)
                            <option value="{{ $div->name }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 mt-4">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-3 block">Kantor Tambahan (Multi-Lokasi Absensi)</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 bg-slate-50 p-6 rounded-3xl border border-dashed border-slate-200">
                            @foreach($offices as $off)
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="additional_offices[]" value="{{ $off->id }}" class="w-5 h-5 rounded-lg border-slate-300 text-blue-600 focus:ring-blue-500 transition-all">
                                <span class="text-sm font-semibold text-slate-600 group-hover:text-slate-800 transition-colors">{{ $off->name }}</span>
                            </label>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-slate-400 mt-2 ml-1">Centang kantor lain jika karyawan diperbolehkan absen di luar kantor utama.</p>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: PERSONAL DATA -->
            <div id="section-personal" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-10">
                <h3 class="text-xl font-bold text-slate-800 mb-8 flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    Data Personal & Kontak
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">No. HP / WhatsApp</label>
                        <input type="text" name="phone" placeholder="0812..." class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Jenis Kelamin</label>
                        <div class="flex gap-4">
                            <label class="flex-1 flex items-center justify-center gap-2 p-4 rounded-2xl bg-slate-50 cursor-pointer hover:bg-slate-100 transition-all">
                                <input type="radio" name="gender" value="L" checked class="text-blue-600 focus:ring-blue-500">
                                <span class="font-bold text-slate-700">Laki-laki</span>
                            </label>
                            <label class="flex-1 flex items-center justify-center gap-2 p-4 rounded-2xl bg-slate-50 cursor-pointer hover:bg-slate-100 transition-all">
                                <input type="radio" name="gender" value="P" class="text-blue-600 focus:ring-blue-500">
                                <span class="font-bold text-slate-700">Perempuan</span>
                            </label>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Tempat Lahir</label>
                        <input type="text" name="profile[birth_place]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Tanggal Lahir</label>
                        <input type="date" name="birth_date" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">NIK (Sesuai KTP)</label>
                        <input type="text" name="profile[nik]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">No. Passport (Opsional)</label>
                        <input type="text" name="profile[passport_no]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Agama</label>
                        <select name="profile[religion]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            <option value="Islam">Islam</option>
                            <option value="Kristen">Kristen</option>
                            <option value="Katolik">Katolik</option>
                            <option value="Hindu">Hindu</option>
                            <option value="Budha">Budha</option>
                            <option value="Konghucu">Konghucu</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Status Pernikahan</label>
                        <select name="profile[marital_status]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            <option value="Lajang">Lajang</option>
                            <option value="Menikah">Menikah</option>
                            <option value="Cerai">Cerai</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Golongan Darah</label>
                        <select name="profile[blood_type]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            <option value="-">-</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="AB">AB</option>
                            <option value="O">O</option>
                        </select>
                    </div>
                </div>

                <hr class="my-10 border-slate-100">
                <h4 class="text-sm font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Data Pendidikan Terakhir
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Jenjang Pendidikan</label>
                        <input type="text" name="profile[education_level]" placeholder="SMA / D3 / S1" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Institusi Pendidikan</label>
                        <input type="text" name="profile[education_institution]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Tahun Lulus</label>
                        <input type="text" name="profile[graduation_year]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">IPK / Nilai</label>
                        <input type="text" name="profile[gpa]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                </div>

                <hr class="my-10 border-slate-100">
                <h4 class="text-sm font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Alamat Sesuai KTP
                </h4>
                <div class="space-y-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Alamat Lengkap</label>
                        <textarea name="profile[ktp_address]" rows="2" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Kota</label>
                            <input type="text" name="profile[ktp_city]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Kelurahan</label>
                            <input type="text" name="profile[ktp_village]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Kode Pos</label>
                            <input type="text" name="profile[ktp_postal_code]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                        </div>
                    </div>
                </div>

                <hr class="my-10 border-slate-100">
                <h4 class="text-sm font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Alamat Domisili (Saat Ini)
                </h4>
                <div class="space-y-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Alamat Lengkap</label>
                        <textarea name="profile[domicile_address]" rows="2" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Kota</label>
                            <input type="text" name="profile[domicile_city]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Kelurahan</label>
                            <input type="text" name="profile[domicile_village]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Kode Pos</label>
                            <input type="text" name="profile[domicile_postal_code]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: EMPLOYMENT -->
            <div id="section-employment" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-10">
                <h3 class="text-xl font-bold text-slate-800 mb-8 flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                    Detail Kepegawaian
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Status Karyawan</label>
                        <select name="employment_status" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            <option value="OJT">OJT</option>
                            <option value="Kontrak">Kontrak</option>
                            <option value="Tetap">Tetap</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Jatah Cuti Tahunan (Hari)</label>
                        <input type="number" name="employment[leave_quota]" value="{{ old('employment.leave_quota', 12) }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Tanggal Bergabung</label>
                        <input type="date" name="employment[join_date]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Tanggal Selesai Kontrak</label>
                        <input type="date" name="employment[contract_end_date]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Jabatan</label>
                        <select name="employment[position_id]" class="select2 w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            <option value="">Pilih Jabatan (Data Master)</option>
                            @foreach($positionOptions as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Override Tunjangan Jabatan (Opsional)</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                            <input type="text" class="rupiah-input w-full pl-14 pr-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-bold text-slate-700" placeholder="Kosongkan jika ikut Master">
                            <input type="hidden" name="employment[allowance_override]" value="0">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1 ml-1">Isi jika karyawan ini memiliki nominal tunjangan khusus yang berbeda dari Master.</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Departemen / Unit</label>
                        <input type="text" name="employment[department]" placeholder="Contoh: Unit IT" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">SKG (Karyawan Tetap)</label>
                        <select name="employment[skg]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            <option value="">Pilih SKG</option>
                            @foreach($skgOptions as $skg)
                                <option value="{{ $skg }}">{{ $skg }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Golongan / Tingkat</label>
                        <select name="employment[grade]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            <option value="">Pilih Golongan/Tingkat</option>
                            @foreach($gradeOptions as $grade)
                                <option value="{{ $grade }}">{{ $grade }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="md:col-span-2 my-2 border-slate-50">

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">No. Rekening Gaji</label>
                        <input type="text" name="employment[company_account_number]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">No. Rekening DPLK</label>
                        <input type="text" name="employment[dplk_bni_account_number]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">No. BPJS Ketenagakerjaan</label>
                        <input type="text" name="employment[bpjs_ketenagakerjaan_no]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">No. BPJS Kesehatan</label>
                        <input type="text" name="employment[bpjs_kesehatan_no]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">NPWP</label>
                        <input type="text" name="employment[npwp]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Status PTKP</label>
                        <input type="text" name="profile[ptkp_status]" placeholder="Contoh: K/0 atau TK/0" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Tahun PTKP</label>
                        <input type="text" name="profile[ptkp_year]" value="{{ date('Y') }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>

                    <hr class="md:col-span-2 my-2 border-slate-50">

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Informasi Kontak Darurat (Nama & No. HP)</label>
                        <textarea name="profile[emergency_contact_info]" rows="2" placeholder="Contoh: Ibu Siti (Istri) - 0812..." class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700"></textarea>
                    </div>

                    <hr class="md:col-span-2 my-2 border-slate-50">

                </div>
            </div>
        </div>
    </div>
</form>

<style>
    html { scroll-behavior: smooth; }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Cari Jabatan...',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
