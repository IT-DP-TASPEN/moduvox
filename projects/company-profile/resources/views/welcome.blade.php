@extends('layouts.app')

@section('title', 'Welcome | PT Moduvox Tech ID')
@section('meta_description', 'Website PT Moduvox Tech ID — solusi konstruksi, infrastruktur, dan engineering terpercaya.')

@section('content')
<section class="pt-28 pb-20 bg-surface-container-low">
    <div class="max-w-7xl mx-auto px-8">
        <div class="max-w-3xl">
            <div class="flex items-center gap-4">
                <img src="{{ asset('dap.png') }}" alt="PT Moduvox Tech ID" class="h-12 w-auto" loading="eager" decoding="async">
                <div class="text-xs uppercase tracking-widest font-bold text-on-surface-variant">Landing</div>
            </div>
            <h1 class="mt-6 font-headline font-extrabold text-4xl md:text-6xl text-on-surface tracking-tighter uppercase">
                PT Moduvox Tech ID
            </h1>
            <p class="mt-5 text-on-surface-variant text-lg leading-relaxed">
                Halaman ini adalah halaman sambutan default. Silakan lanjutkan ke halaman utama website atau masuk ke admin panel untuk mengelola konten.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-3 bg-primary text-on-primary px-8 py-4 rounded-lg font-headline font-extrabold text-sm uppercase tracking-widest shadow-lg hover:bg-on-primary-fixed-variant transition-colors">
                    Buka Website
                    <span class="material-symbols-outlined">arrow_right_alt</span>
                </a>
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-3 bg-white border border-outline-variant/30 text-on-surface px-8 py-4 rounded-lg font-headline font-extrabold text-sm uppercase tracking-widest hover:shadow-md transition">
                        Login Admin
                        <span class="material-symbols-outlined">admin_panel_settings</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection


