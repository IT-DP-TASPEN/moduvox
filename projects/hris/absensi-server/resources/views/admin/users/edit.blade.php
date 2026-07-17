@extends('admin.layout')

@section('header', 'Edit Penempatan Karyawan')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex items-center gap-4">
            <div class="w-16 h-16 bg-brand-blue rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-blue-100">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-800">{{ $user->name }}</h3>
                <p class="text-slate-500 text-sm">{{ $user->employee_id }} • {{ $user->title }}</p>
            </div>
        </div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="p-8 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-3">
                <label class="text-sm font-bold text-slate-700 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Pilih Lokasi Kantor Penempatan
                </label>
                <select name="office_id" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent transition-all appearance-none bg-no-repeat bg-[right_1rem_center] bg-[length:1em_1em]" 
                    style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2364748b%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22/%3E%3C/svg%3E');">
                    <option value="">-- Belum Diatur --</option>
                    @foreach($offices as $office)
                        <option value="{{ $office->id }}" {{ $user->office_id == $office->id ? 'selected' : '' }}>
                            {{ $office->code }} - {{ $office->name }} (Radius: {{ $office->radius }}m)
                        </option>
                    @endforeach
                </select>
                <p class="text-slate-400 text-[11px]">Karyawan hanya bisa melakukan absensi jika berada di dalam radius kantor yang dipilih.</p>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" class="bg-brand-blue text-white px-8 py-3 rounded-2xl font-bold hover:opacity-90 transition-all shadow-lg shadow-blue-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.users.index') }}" class="bg-slate-100 text-slate-600 px-8 py-3 rounded-2xl font-bold hover:bg-slate-200 transition-all">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
