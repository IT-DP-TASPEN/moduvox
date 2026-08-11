@extends('layouts.auth')
@section('title', 'Login')

@section('content')
<div class="min-h-screen grid grid-cols-1 lg:grid-cols-12">
    <div class="hidden lg:block lg:col-span-6 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-inverse-surface via-on-surface-variant to-on-surface"></div>
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="relative h-full p-16 flex flex-col justify-end">
            <div class="flex items-center gap-4 mb-10">
                <img src="{{ asset('dap.png') }}" alt="PT Moduvox Tech ID" class="h-12 w-auto" loading="eager" decoding="async">
                <div>
                    <div class="text-white font-headline font-extrabold text-2xl tracking-tight">PT Moduvox Tech ID</div>
                    <div class="text-white/70 text-sm font-medium">Secure Administrator Access</div>
                </div>
            </div>
            <h1 class="text-white font-headline font-extrabold text-5xl tracking-tight mb-4 leading-tight">
                Membangun<br/>Masa Depan.
            </h1>
            <p class="text-white/80 text-lg max-w-md">
                Akses admin untuk mengelola konten perusahaan, portofolio proyek, dan pesan masuk.
            </p>
        </div>
    </div>

    <div class="lg:col-span-6 flex items-center justify-center p-8 sm:p-16">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center sm:text-left">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('dap.png') }}" alt="PT Moduvox Tech ID" class="h-10 w-auto" loading="eager" decoding="async">
                    <span class="font-headline font-extrabold text-xl text-on-surface tracking-tight">Admin Panel</span>
                </a>
                <h2 class="mt-6 text-3xl font-headline font-extrabold tracking-tight text-on-surface">Selamat Datang</h2>
                <p class="text-on-surface-variant mt-2 text-sm">Masuk untuk melanjutkan pengelolaan website.</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 text-red-700 p-4 rounded-lg flex items-start gap-3 border border-red-100">
                    <span class="material-symbols-outlined text-red-600 mt-0.5">error</span>
                    <ul class="text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-on-surface mb-2">Alamat Email</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50">mail</span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full pl-12 pr-4 py-3 bg-surface-container border-2 border-transparent rounded-xl focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 transition-all text-sm outline-none"
                               placeholder="admin@moduvox.id">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-on-surface mb-2">Kata Sandi</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50">lock</span>
                        <input id="password" type="password" name="password" required
                               class="w-full pl-12 pr-4 py-3 bg-surface-container border-2 border-transparent rounded-xl focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 transition-all text-sm outline-none"
                               placeholder="••••••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label for="remember_me" class="flex items-center cursor-pointer group">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 text-primary bg-surface-container border-gray-300 rounded focus:ring-primary focus:ring-2 cursor-pointer">
                        <span class="ml-2 text-sm text-on-surface-variant group-hover:text-primary transition-colors">Ingat Saya</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-primary hover:text-red-900 transition-colors">Lupa sandi?</a>
                    @endif
                </div>

                <button type="submit" class="w-full inline-flex justify-center items-center gap-2 py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-primary/20 text-sm font-bold text-white bg-primary hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-primary/30 transition-all transform active:scale-[0.98]">
                    Masuk
                    <span class="material-symbols-outlined text-lg">arrow_forward</span>
                </button>
            </form>

            <div class="text-center pt-8 border-t border-surface-container text-xs text-on-surface-variant/60 font-medium">
                © {{ date('Y') }} PT Moduvox Tech ID. All rights reserved.
            </div>
        </div>
    </div>
</div>
@endsection


