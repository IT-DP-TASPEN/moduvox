@extends('admin.layout')

@section('header')
<div class="flex items-center gap-4">
    <a href="{{ route('admin.employees.show', $user) }}" class="p-2 hover:bg-slate-100 rounded-full transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Edit Karyawan</h1>
        <p class="text-sm text-slate-500">Perbarui informasi lengkap untuk {{ $user->name }}</p>
    </div>
</div>
@endsection

@section('content')
<form action="{{ route('admin.employees.update', $user) }}" method="POST" enctype="multipart/form-data" class="space-y-8 pb-20">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- LEFT: SECTIONS NAV (Sticky) -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 sticky top-8">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6 px-2">Navigasi Form</h4>
                <nav class="space-y-1">
                    <a href="#section-core" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-blue-600 bg-blue-50 transition-all">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span> Data Utama
                    </a>
                    <a href="#section-personal" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                        <span class="w-2 h-2 rounded-full bg-slate-200"></span> Personal
                    </a>
                    <a href="#section-employment" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                        <span class="w-2 h-2 rounded-full bg-slate-200"></span> Kepegawaian
                    </a>
                    <a href="#section-files" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                        <span class="w-2 h-2 rounded-full bg-slate-200"></span> Berkas & Foto
                    </a>
                </nav>
                
                <hr class="my-6 border-slate-50">
                
                <button type="submit" class="w-full py-4 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Perubahan
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm5 3h1a1 1 0 011 1v1H7v-1a1 1 0 011-1h1" />
                        </svg>
                    </span>
                    Data Utama Kepegawaian
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">NIP / Employee ID</label>
                        <input type="text" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Email Perusahaan</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">No. HP / WhatsApp</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Kantor Utama</label>
                        <select name="office_id" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            <option value="">Pilih Kantor</option>
                            @foreach($offices as $off)
                            <option value="{{ $off->id }}" {{ $user->office_id == $off->id ? 'selected' : '' }}>{{ $off->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Divisi</label>
                        <select name="division_name" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            <option value="">Pilih Divisi</option>
                            @foreach($divisions as $div)
                            <option value="{{ $div->name }}" {{ $user->division_name == $div->name ? 'selected' : '' }}>{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 mt-4">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-3 block">Kantor Tambahan (Multi-Lokasi Absensi)</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 bg-slate-50 p-6 rounded-3xl border border-dashed border-slate-200">
                            @php $assignedOffices = $user->additionalOffices->pluck('id')->toArray(); @endphp
                            @foreach($offices as $off)
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="additional_offices[]" value="{{ $off->id }}" 
                                    {{ in_array($off->id, $assignedOffices) ? 'checked' : '' }}
                                    class="w-5 h-5 rounded-lg border-slate-300 text-blue-600 focus:ring-blue-500 transition-all">
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
                    Data Personal & Alamat
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Tempat Lahir</label>
                        <input type="text" name="profile[birth_place]" value="{{ old('profile.birth_place', $user->profile->birth_place ?? '') }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Tanggal Lahir</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date', $user->birth_date ? $user->birth_date->format('Y-m-d') : '') }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Jenis Kelamin</label>
                        <div class="flex gap-4">
                            <label class="flex-1 flex items-center justify-center gap-2 p-4 rounded-2xl bg-slate-50 cursor-pointer hover:bg-slate-100 transition-all">
                                <input type="radio" name="gender" value="L" {{ $user->gender == 'L' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                                <span class="font-bold text-slate-700">Laki-laki</span>
                            </label>
                            <label class="flex-1 flex items-center justify-center gap-2 p-4 rounded-2xl bg-slate-50 cursor-pointer hover:bg-slate-100 transition-all">
                                <input type="radio" name="gender" value="P" {{ $user->gender == 'P' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                                <span class="font-bold text-slate-700">Perempuan</span>
                            </label>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Agama</label>
                        <select name="profile[religion]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu'] as $rel)
                            <option value="{{ $rel }}" {{ ($user->profile->religion ?? '') == $rel ? 'selected' : '' }}>{{ $rel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">NIK (KTP)</label>
                        <input type="text" name="profile[nik]" value="{{ old('profile.nik', $user->profile->nik ?? '') }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">No. Passport (Opsional)</label>
                        <input type="text" name="profile[passport_no]" value="{{ old('profile.passport_no', $user->profile->passport_no ?? '') }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Status Pernikahan</label>
                        <select name="profile[marital_status]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            @foreach(['Lajang', 'Menikah', 'Cerai'] as $ms)
                            <option value="{{ $ms }}" {{ ($user->profile->marital_status ?? '') == $ms ? 'selected' : '' }}>{{ $ms }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Golongan Darah</label>
                        <select name="profile[blood_type]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            @foreach(['-', 'A', 'B', 'AB', 'O'] as $bt)
                            <option value="{{ $bt }}" {{ ($user->profile->blood_type ?? '') == $bt ? 'selected' : '' }}>{{ $bt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="md:col-span-2 my-2 border-slate-50">

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Pendidikan Terakhir</label>
                        <input type="text" name="profile[education_level]" value="{{ old('profile.education_level', $user->profile->education_level ?? '') }}" placeholder="SMA / S1" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Institusi Pendidikan</label>
                        <input type="text" name="profile[education_institution]" value="{{ old('profile.education_institution', $user->profile->education_institution ?? '') }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Tahun Lulus</label>
                        <input type="text" name="profile[graduation_year]" value="{{ old('profile.graduation_year', $user->profile->graduation_year ?? '') }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">IPK / Nilai</label>
                        <input type="text" name="profile[gpa]" value="{{ old('profile.gpa', $user->profile->gpa ?? '') }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>

                    <hr class="md:col-span-2 my-2 border-slate-50">

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Alamat Domisili Lengkap</label>
                        <textarea name="profile[domicile_address]" rows="3" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">{{ old('profile.domicile_address', $user->profile->domicile_address ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: EMPLOYMENT DETAILS -->
            <div id="section-employment" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-10">
                <h3 class="text-xl font-bold text-slate-800 mb-8 flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                    Detail Kepegawaian & HR
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Tanggal Bergabung</label>
                        <input type="date" name="employment[join_date]" value="{{ old('employment.join_date', $user->employment->join_date ?? ($user->join_date ? $user->join_date->format('Y-m-d') : '')) }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Status Karyawan</label>
                        <select name="employment_status" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            @foreach(['OJT', 'Kontrak', 'Tetap'] as $st)
                            <option value="{{ $st }}" {{ $user->employment_status == $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Jatah Cuti Tahunan (Hari)</label>
                        <input type="number" name="employment[leave_quota]" value="{{ old('employment.leave_quota', $user->employment->leave_quota ?? 12) }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Sisa Cuti Saat Ini (Hari)</label>
                        <input type="number" name="employment[remaining_leave]" value="{{ old('employment.remaining_leave', $user->employment->remaining_leave ?? 12) }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Jabatan Struktural</label>
                        <select name="employment[position_id]" class="select2 w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            <option value="">Pilih Jabatan (Data Master)</option>
                            @php $currPosId = old('employment.position_id', $user->employment->position_id ?? ''); @endphp
                            @foreach($positionOptions as $pos)
                                <option value="{{ $pos->id }}" {{ $currPosId == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Override Tunjangan Jabatan (Opsional)</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                            @php $overrideVal = old('employment.allowance_override', $user->employment->allowance_override ?? 0); @endphp
                            <input type="text" class="rupiah-input w-full pl-14 pr-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-bold text-slate-700" value="{{ $overrideVal }}" placeholder="Kosongkan jika ikut Master">
                            <input type="hidden" name="employment[allowance_override]" value="{{ $overrideVal }}">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1 ml-1">Isi jika karyawan ini memiliki nominal tunjangan khusus yang berbeda dari Master.</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Departemen / Unit</label>
                        <input type="text" name="employment[department]" value="{{ old('employment.department', $user->employment->department ?? '') }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">SKG (Untuk Gaji Pokok Tetap)</label>
                        <select name="employment[skg]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            <option value="">Pilih SKG</option>
                            @php $currSkg = old('employment.skg', $user->employment->skg ?? 0); @endphp
                            @foreach($skgOptions as $skg)
                                <option value="{{ $skg }}" {{ $currSkg == $skg ? 'selected' : '' }}>{{ $skg }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Golongan / Tingkat</label>
                        <select name="employment[grade]" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                            <option value="">Pilih Golongan/Tingkat</option>
                            @php $currGrade = old('employment.grade', $user->employment->grade ?? ''); @endphp
                            @foreach($gradeOptions as $grade)
                                <option value="{{ $grade }}" {{ $currGrade == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="md:col-span-2 my-2 border-slate-50">

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">No. Rekening Gaji</label>
                        <input type="text" name="employment[company_account_number]" value="{{ old('employment.company_account_number', $user->employment->company_account_number ?? '') }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">No. Rekening DPLK</label>
                        <input type="text" name="employment[dplk_bni_account_number]" value="{{ old('employment.dplk_bni_account_number', $user->employment->dplk_bni_account_number ?? '') }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">No. BPJS Ketenagakerjaan</label>
                        <input type="text" name="employment[bpjs_ketenagakerjaan_no]" value="{{ old('employment.bpjs_ketenagakerjaan_no', $user->employment->bpjs_ketenagakerjaan_no ?? '') }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">No. BPJS Kesehatan</label>
                        <input type="text" name="employment[bpjs_kesehatan_no]" value="{{ old('employment.bpjs_kesehatan_no', $user->employment->bpjs_kesehatan_no ?? '') }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">NPWP</label>
                        <input type="text" name="employment[npwp]" value="{{ old('employment.npwp', $user->employment->npwp ?? '') }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Status PTKP</label>
                        <input type="text" name="profile[ptkp_status]" value="{{ old('profile.ptkp_status', $user->profile->ptkp_status ?? '') }}" placeholder="Contoh: K/0 atau TK/0" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Tahun PTKP</label>
                        <input type="text" name="profile[ptkp_year]" value="{{ old('profile.ptkp_year', $user->profile->ptkp_year ?? date('Y')) }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">
                    </div>

                    <hr class="md:col-span-2 my-2 border-slate-50">

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Informasi Kontak Darurat (Nama & No. HP)</label>
                        <textarea name="profile[emergency_contact_info]" rows="2" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-blue-500 transition-all font-semibold text-slate-700">{{ old('profile.emergency_contact_info', $user->profile->emergency_contact_info ?? '') }}</textarea>
                    </div>

                    <hr class="md:col-span-2 my-2 border-slate-50">

                </div>
            </div>

            <!-- SECTION 4: FILE UPLOADS -->
            <div id="section-files" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-10">
                <h3 class="text-xl font-bold text-slate-800 mb-8 flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </span>
                    Upload Berkas & Foto
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach(['PHOTO' => 'Foto Profil', 'KTP' => 'Scan KTP', 'KK' => 'Kartu Keluarga', 'IJAZAH' => 'Ijazah Terakhir'] as $key => $label)
                    <div class="p-6 rounded-[2rem] bg-slate-50 border-2 border-dashed border-slate-200 hover:border-blue-300 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-slate-400 group-hover:text-blue-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-bold text-slate-500 uppercase mb-1">{{ $label }}</p>
                                <input type="file" name="user_files[{{ $key }}]" class="text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                @php $exist = $user->files->where('file_type', $key)->first(); @endphp
                                @if($exist)
                                <p class="text-[10px] text-emerald-600 font-bold mt-2 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    File sudah ada: {{ $exist->name }}
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
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
