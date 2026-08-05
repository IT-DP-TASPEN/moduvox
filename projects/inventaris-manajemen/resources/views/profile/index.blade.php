@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Pengguna')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-bold text-gray-800">Profil Saya</h2>
        <p class="text-sm text-gray-500 mt-1">Kelola informasi akun dan keamanan Anda</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    {{-- User Info Card --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="card-premium">
            <div class="flex flex-col items-center text-center p-4">
                <div class="w-24 h-24 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-3xl font-bold mb-4 shadow-inner ring-4 ring-white">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                </div>
                <h3 class="text-lg font-bold text-gray-900">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500 mb-4">{{ $user->email }}</p>
                
                <span class="px-3 py-1 text-xs font-bold rounded-full bg-primary-50 text-primary-700 border border-primary-100 mb-6">
                    {{ $user->roles->first()->name ?? 'User' }}
                </span>
                
                <div class="w-full text-left space-y-4 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Kantor Cabang</p>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-building text-gray-400"></i>
                            <p class="text-sm font-semibold text-gray-800">{{ $user->kantor->nama ?? 'Semua Kantor / Pusat' }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Terdaftar Sejak</p>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-calendar-check text-gray-400"></i>
                            <p class="text-sm font-medium text-gray-800">{{ $user->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Update Password Card --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="card-premium">
            <div class="mb-5 border-b border-gray-100 pb-4">
                <h3 class="text-lg font-bold text-gray-900 tracking-tight"><i class="fa-solid fa-lock text-gray-400 mr-2"></i> Ubah Password</h3>
                <p class="text-sm text-gray-500 mt-1">Pastikan akun Anda menggunakan password yang panjang dan acak agar tetap aman.</p>
            </div>
            
            <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label for="current_password" class="label-premium">Password Saat Ini</label>
                    <input type="password" name="current_password" id="current_password" class="input-premium @error('current_password') border-red-500 focus:ring-red-500 @enderror" required>
                    @error('current_password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password" class="label-premium">Password Baru</label>
                    <input type="password" name="password" id="password" class="input-premium @error('password') border-red-500 focus:ring-red-500 @enderror" required>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password_confirmation" class="label-premium">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="input-premium" required>
                </div>
                
                <div class="flex justify-end pt-4">
                    <button type="submit" class="btn-primary flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
