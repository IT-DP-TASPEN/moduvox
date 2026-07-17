@extends('admin.layout')

@section('header')
<div class="flex items-center gap-4">
    <a href="{{ route('admin.employees.index') }}" class="p-2 hover:bg-slate-100 rounded-full transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Portal Karyawan</h1>
        <p class="text-sm text-slate-500">Manajemen profil dan riwayat lengkap SDM</p>
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    
    <!-- LEFT: IDENTITY CARD -->
    <div class="lg:col-span-3 space-y-6">
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="h-24 bg-gradient-to-r from-blue-600 to-indigo-700"></div>
            <div class="px-8 pb-8 -mt-12 text-center">
                <div class="relative inline-block">
                    <div class="w-24 h-24 rounded-3xl bg-white p-1 shadow-xl mx-auto overflow-hidden">
                        <img src="{{ $user->photo_profile ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=0D8ABC&color=fff' }}" class="w-full h-full object-cover rounded-2xl">
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-emerald-500 border-4 border-white rounded-full"></div>
                </div>
                
                <h2 class="text-xl font-bold text-slate-800 mt-4">{{ $user->name }}</h2>
                <p class="text-xs font-bold text-blue-600 uppercase tracking-widest">{{ $user->employee_id }}</p>
                <p class="text-sm text-slate-500 font-medium mt-1">{{ $user->title }}</p>
                
                <div class="mt-6 grid grid-cols-2 gap-2">
                    <div class="p-3 bg-slate-50 rounded-2xl">
                        <p class="text-[10px] text-slate-400 uppercase font-bold">Status</p>
                        <p class="text-xs font-bold text-slate-700">{{ $user->employment_status }}</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-2xl">
                        <p class="text-[10px] text-slate-400 uppercase font-bold">Gender</p>
                        <p class="text-xs font-bold text-slate-700">{{ $user->gender == 'L' ? 'Pria' : 'Wanita' }}</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3">
                    <a href="{{ route('admin.employees.edit', $user) }}" class="flex items-center justify-center gap-2 w-full py-3 rounded-2xl bg-slate-800 text-white font-bold hover:bg-slate-700 transition-all text-sm shadow-lg shadow-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit Profil
                    </a>
                    
                    @if(!$user->trashed())
                    <button onclick="document.getElementById('deactivateModal').classList.remove('hidden')" class="flex items-center justify-center gap-2 w-full py-3 rounded-2xl bg-red-50 text-red-600 font-bold hover:bg-red-100 transition-all text-sm border border-red-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        Nonaktifkan Karyawan
                    </button>
                    @else
                    <div class="py-3 px-4 rounded-2xl bg-slate-100 border border-slate-200 text-slate-500 text-xs font-bold text-center">
                        Akun Nonaktif / Resign
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- QUICK STATS -->
        <div class="bg-indigo-900 rounded-[2rem] p-6 text-white shadow-xl shadow-indigo-100">
            <h4 class="text-xs font-bold text-indigo-300 uppercase tracking-widest mb-4">Ringkasan Cuti</h4>
            <div class="space-y-4">
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-2xl font-bold">{{ $user->employment->remaining_leave ?? 0 }}</p>
                        <p class="text-[10px] text-indigo-300 uppercase font-bold">Sisa Kuota</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold">{{ $user->employment->leave_quota ?? 0 }}</p>
                        <p class="text-[10px] text-indigo-300 uppercase font-bold">Total Jatah</p>
                    </div>
                </div>
                <div class="w-full bg-indigo-800 h-2 rounded-full overflow-hidden">
                    @php 
                        $quota = $user->employment->leave_quota ?? 12;
                        $rem = $user->employment->remaining_leave ?? 0;
                        $perc = $quota > 0 ? ($rem / $quota) * 100 : 0;
                    @endphp
                    <div class="bg-indigo-400 h-full rounded-full" style="width: {{ $perc }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT: SECTIONS -->
    <div class="lg:col-span-9 space-y-8">
        
        <!-- SECTION 1: PERSONAL & EMPLOYMENT INFO -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <!-- PERSONAL -->
                    <section>
                        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            Data Personal
                        </h3>
                        <div class="grid grid-cols-1 gap-4">
                            <x-detail-item label="Nama Lengkap" value="{{ $user->name }}" />
                            <x-detail-item label="NIK (KTP)" value="{{ $user->profile->nik ?? '-' }}" />
                            <x-detail-item label="No. Passport" value="{{ $user->profile->passport_no ?? '-' }}" />
                            <x-detail-item label="Tempat, Tgl Lahir" value="{{ $user->profile->birth_place ?? '-' }}, {{ $user->birth_date ? $user->birth_date->format('d-m-Y') : '-' }}" />
                            <div class="grid grid-cols-2 gap-4">
                                <x-detail-item label="Agama" value="{{ $user->profile->religion ?? '-' }}" />
                                <x-detail-item label="Gol. Darah" value="{{ $user->profile->blood_type ?? '-' }}" />
                            </div>
                            <x-detail-item label="Status Pernikahan" value="{{ $user->profile->marital_status ?? '-' }}" />
                            <x-detail-item label="Email" value="{{ $user->email }}" />
                            <x-detail-item label="Telepon" value="{{ $user->phone }}" />
                            
                            <div class="mt-4 pt-4 border-t border-slate-50">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Alamat KTP</p>
                                <p class="text-sm text-slate-700 leading-relaxed">{{ $user->profile->ktp_address ?? '-' }}</p>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ $user->profile->ktp_village ?? '' }} {{ $user->profile->ktp_city ?? '' }} {{ $user->profile->ktp_postal_code ?? '' }}
                                </p>
                            </div>

                            <div class="mt-2 pt-2">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Alamat Domisili</p>
                                <p class="text-sm text-slate-700 leading-relaxed">{{ $user->profile->domicile_address ?? '-' }}</p>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ $user->profile->domicile_village ?? '' }} {{ $user->profile->domicile_city ?? '' }} {{ $user->profile->domicile_postal_code ?? '' }}
                                </p>
                            </div>

                            <div class="mt-4 pt-4 border-t border-slate-50">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Kontak Darurat</p>
                                <p class="text-sm text-slate-700 font-medium">{{ $user->profile->emergency_contact_info ?? '-' }}</p>
                            </div>

                            <div class="mt-4 pt-4 border-t border-slate-50">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Pendidikan Terakhir</p>
                                <x-detail-item label="Tingkat" value="{{ $user->profile->education_level ?? '-' }}" />
                                <x-detail-item label="Institusi / Universitas" value="{{ $user->profile->education_institution ?? '-' }}" />
                                <div class="grid grid-cols-2 gap-4">
                                    <x-detail-item label="Tahun Lulus" value="{{ $user->profile->graduation_year ?? '-' }}" />
                                    <x-detail-item label="IPK / Nilai" value="{{ $user->profile->gpa ?? '-' }}" />
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- EMPLOYMENT -->
                    <section>
                        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            Informasi Kerja
                        </h3>
                        <div class="grid grid-cols-1 gap-4">
                            <x-detail-item label="Unit / Penempatan" value="{{ $user->office->name ?? '-' }}" />
                            <x-detail-item label="Departemen" value="{{ $user->employment->department ?? '-' }}" />
                            <x-detail-item label="Jabatan" value="{{ $user->employment->position ?? $user->title }}" />
                            <x-detail-item label="Tgl Bergabung" value="{{ $user->join_date ? $user->join_date->format('d M Y') : '-' }}" />
                            <x-detail-item label="Penyetuju (Approver)" value="{{ $user->approver ?? 'Belum Diatur' }}" />
                            <x-detail-item label="Status Kontrak" value="{{ $user->employment->employment_status ?? $user->employment_status }}" />
                            <div class="mt-4 pt-4 border-t border-slate-50">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Gaji & Payroll</p>
                                <x-detail-item label="Golongan / SKG" value="{{ $user->employment->grade ?? '-' }} / {{ $user->employment->skg ?? '-' }}" />
                                <x-detail-item label="NPWP" value="{{ $user->employment->npwp ?? '-' }}" />
                                <x-detail-item label="Status PTKP" value="{{ $user->profile->ptkp_status ?? '-' }} ({{ $user->profile->ptkp_year ?? '' }})" />
                                <x-detail-item label="Rekening Perusahaan" value="{{ $user->employment->company_account_number ?? '-' }}" />
                                <x-detail-item label="DPLK / BNI" value="{{ $user->employment->dplk_bni_account_number ?? '-' }}" />
                                <div class="grid grid-cols-1 gap-1 mt-3">
                                    <x-detail-item label="BPJS Ketenagakerjaan" value="{{ $user->employment->bpjs_ketenagakerjaan_no ?? '-' }}" />
                                    <x-detail-item label="BPJS Kesehatan" value="{{ $user->employment->bpjs_kesehatan_no ?? '-' }}" />
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <!-- SECTION 2: ATTENDANCE & LOGS (TABBED) -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden" x-data="{ tab: 'attendance' }">
            <div class="px-8 pt-8 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01" />
                        </svg>
                    </span>
                    Monitoring Aktivitas
                </h3>
                <div class="flex bg-slate-100 p-1 rounded-2xl">
                    <button @click="tab = 'attendance'" :class="tab === 'attendance' ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all">Kehadiran</button>
                    <button @click="tab = 'leave'" :class="tab === 'leave' ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all">Cuti & Izin</button>
                    <button @click="tab = 'overtime'" :class="tab === 'overtime' ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all">Lembur & Tugas</button>
                    <button @click="tab = 'history'" :class="tab === 'history' ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all">Riwayat Karir</button>
                </div>
            </div>

            <div class="p-8">
                <!-- TAB ATTENDANCE -->
                <div x-show="tab === 'attendance'" class="space-y-4">
                    <table class="w-full text-left text-sm">
                        <thead class="text-slate-400 text-[10px] uppercase font-bold">
                            <tr>
                                <th class="pb-4">Tanggal</th>
                                <th class="pb-4">Jam Masuk</th>
                                <th class="pb-4">Jam Keluar</th>
                                <th class="pb-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @php
                                $groupedAttendances = $user->attendances()->latest()->get()->groupBy(fn($att) => $att->created_at->format('Y-m-d'))->take(10);
                            @endphp
                            @forelse($groupedAttendances as $date => $group)
                                @php
                                    $in = $group->where('type', 'masuk')->first();
                                    $out = $group->where('type', 'keluar')->first();
                                @endphp
                                <tr>
                                    <td class="py-4 font-semibold text-slate-700">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</td>
                                    <td class="py-4 text-emerald-600 font-bold">{{ $in ? $in->created_at->format('H:i') : '--:--' }}</td>
                                    <td class="py-4 text-red-500 font-bold">{{ $out ? $out->created_at->format('H:i') : '--:--' }}</td>
                                    <td class="py-4">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase {{ $in ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                                            {{ $in ? 'HADIR' : 'ALPHA' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-slate-400 italic text-sm">Belum ada data kehadiran terdeteksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- TAB LEAVE & PERMIT -->
                <div x-show="tab === 'leave'" x-cloak class="space-y-4">
                    <table class="w-full text-left text-sm">
                        <thead class="text-slate-400 text-[10px] uppercase font-bold">
                            <tr>
                                <th class="pb-4">Tipe</th>
                                <th class="pb-4">Periode</th>
                                <th class="pb-4">Alasan</th>
                                <th class="pb-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @php 
                                $leaves = collect($user->leaveRequests)->merge($user->permitRequests)->sortByDesc('created_at')->take(10);
                            @endphp
                            @forelse($leaves as $req)
                                <tr>
                                    <td class="py-4 font-semibold text-slate-700">{{ $req->type }}</td>
                                    <td class="py-4 text-slate-600">
                                        @if(isset($req->start_date))
                                            {{ \Carbon\Carbon::parse($req->start_date)->format('d/m/y') }} - {{ \Carbon\Carbon::parse($req->end_date)->format('d/m/y') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($req->date)->format('d/m/y') }}
                                        @endif
                                    </td>
                                    <td class="py-4 text-xs text-slate-500 italic max-w-xs truncate">{{ $req->notes ?? $req->reason ?? '-' }}</td>
                                    <td class="py-4">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase 
                                            {{ $req->status == 'approved' ? 'bg-emerald-50 text-emerald-600' : ($req->status == 'rejected' ? 'bg-red-50 text-red-600' : 'bg-orange-50 text-orange-600') }}">
                                            {{ $req->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-slate-400 italic text-sm">Belum ada riwayat cuti atau izin.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- TAB OVERTIME & DUTY -->
                <div x-show="tab === 'overtime'" x-cloak class="space-y-4">
                    <table class="w-full text-left text-sm">
                        <thead class="text-slate-400 text-[10px] uppercase font-bold">
                            <tr>
                                <th class="pb-4">Tipe</th>
                                <th class="pb-4">Tanggal</th>
                                <th class="pb-4">Keterangan</th>
                                <th class="pb-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @php 
                                $others = collect($user->overtimeRequests)->merge($user->outsideDutyRequests)->sortByDesc('created_at')->take(10);
                            @endphp
                            @forelse($others as $req)
                                <tr>
                                    <td class="py-4 font-semibold text-slate-700">{{ isset($req->destination) ? 'Tugas Luar' : 'Lembur' }}</td>
                                    <td class="py-4 text-slate-600">{{ \Carbon\Carbon::parse($req->date)->format('d M Y') }}</td>
                                    <td class="py-4 text-xs text-slate-500 italic max-w-xs truncate">{{ $req->reason ?? $req->destination ?? '-' }}</td>
                                    <td class="py-4">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase 
                                            {{ $req->status == 'approved' ? 'bg-emerald-50 text-emerald-600' : ($req->status == 'rejected' ? 'bg-red-50 text-red-600' : 'bg-orange-50 text-orange-600') }}">
                                            {{ $req->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-slate-400 italic text-sm">Belum ada riwayat lembur atau tugas luar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- TAB CAREER HISTORY -->
                <div x-show="tab === 'history'" x-cloak class="space-y-4">
                    <table class="w-full text-left text-sm">
                        <thead class="text-slate-400 text-[10px] uppercase font-bold">
                            <tr>
                                <th class="pb-4">Tanggal</th>
                                <th class="pb-4">Jenis</th>
                                <th class="pb-4">Perubahan</th>
                                <th class="pb-4">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($user->histories->sortByDesc('effective_date') as $hist)
                                <tr>
                                    <td class="py-4 font-semibold text-slate-700">{{ \Carbon\Carbon::parse($hist->effective_date)->format('d M Y') }}</td>
                                    <td class="py-4">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase 
                                            {{ $hist->type == 'Promotion' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600' }}">
                                            {{ $hist->type }}
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <div class="text-xs">
                                            <span class="text-slate-400">{{ $hist->old_value }}</span>
                                            <span class="mx-2 text-slate-300">➔</span>
                                            <span class="font-bold text-slate-700">{{ $hist->new_value }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 text-xs text-slate-500 italic">{{ $hist->notes }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-slate-400 italic text-sm">Belum ada riwayat perubahan karir tercatat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECTION 3: DOCUMENTS -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-8">
                <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                    </span>
                    Berkas & Dokumen Karyawan
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    @forelse($user->files as $file)
                    @php
                        $isImage = in_array(strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                    @endphp
                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="group relative bg-slate-50 rounded-3xl border border-slate-100 hover:border-blue-200 hover:bg-white hover:shadow-xl hover:shadow-blue-900/5 transition-all overflow-hidden p-4">
                        <div class="aspect-square rounded-2xl bg-white mb-4 overflow-hidden border border-slate-50 flex items-center justify-center relative">
                            @if($isImage)
                                <img src="{{ asset('storage/' . $file->file_path) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <div class="absolute inset-0 bg-black/5 group-hover:bg-transparent transition-colors"></div>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-500 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            @endif
                        </div>
                        
                        <div class="relative z-10">
                            <p class="text-[11px] font-bold text-slate-700 truncate group-hover:text-blue-600 transition-colors">{{ $file->name }}</p>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-[9px] text-slate-400 uppercase font-black tracking-widest">{{ $file->file_type }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-slate-300 group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </div>
                        </div>
                    </a>
                    @empty
                    <div class="col-span-full py-12 text-center bg-slate-50 rounded-[2rem] border border-dashed border-slate-200">
                        <p class="text-slate-400 text-sm">Belum ada dokumen yang diunggah.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- SECTION 4: OTHERS (MUTASI & SP) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-8">
                <h4 class="font-bold text-slate-800 flex items-center gap-3 mb-6">
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-black">MT</span>
                    Riwayat Mutasi
                </h4>
                <div class="space-y-4">
                    @forelse($user->mutations as $mut)
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold text-blue-600 uppercase">{{ $mut->type }}</span>
                            <span class="text-[10px] text-slate-400">{{ $mut->date }}</span>
                        </div>
                        <p class="text-sm font-bold text-slate-700 mt-2">{{ $mut->old_position }} <span class="text-slate-400 mx-2">➔</span> {{ $mut->new_position }}</p>
                    </div>
                    @empty
                    <p class="text-center text-slate-400 text-sm py-4">Tidak ada riwayat mutasi.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-8">
                <h4 class="font-bold text-slate-800 flex items-center gap-3 mb-6">
                    <span class="w-8 h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xs font-black">SP</span>
                    Peringatan & Disiplin
                </h4>
                <div class="space-y-4">
                    @forelse($user->warnings as $sp)
                    <div class="p-5 rounded-2xl bg-red-50 border border-red-100">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold text-red-600 uppercase">{{ $sp->level }}</span>
                            <span class="text-[10px] text-red-400">{{ $sp->date }}</span>
                        </div>
                        <p class="text-sm font-bold text-slate-700 mt-2">{{ $sp->reason }}</p>
                    </div>
                    @empty
                    <p class="text-center text-slate-400 text-sm py-4">Karyawan tidak memiliki riwayat SP.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nonaktifkan Karyawan -->
<div id="deactivateModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl relative">
        <h3 class="text-lg font-bold text-slate-800 mb-2">Nonaktifkan Karyawan</h3>
        <p class="text-xs text-slate-500 mb-6">Karyawan tidak akan bisa login lagi ke portal, namun data historis absensi dan penggajian akan tetap tersimpan.</p>
        
        <form method="POST" action="{{ route('admin.employees.destroy', $user->id) }}">
            @csrf
            @method('DELETE')
            
            <div class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Alasan</label>
                    <select name="reason" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold text-slate-700">
                        <option value="">Pilih Alasan</option>
                        <option value="resign">Resign / Mengundurkan Diri</option>
                        <option value="pensiun">Pensiun</option>
                        <option value="habis_kontrak">Habis Masa Kontrak</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Catatan Tambahan (Opsional)</label>
                    <textarea name="note" rows="3" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-medium text-slate-700" placeholder="Keterangan tambahan..."></textarea>
                </div>
            </div>
            
            <div class="flex gap-3 mt-8">
                <button type="button" onclick="document.getElementById('deactivateModal').classList.add('hidden')" class="flex-1 py-3 bg-slate-100 text-slate-600 rounded-2xl text-sm font-bold hover:bg-slate-200 transition-all">Batal</button>
                <button type="submit" class="flex-1 py-3 bg-red-500 text-white rounded-2xl text-sm font-bold hover:bg-red-600 transition-all">Konfirmasi</button>
            </div>
        </form>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
