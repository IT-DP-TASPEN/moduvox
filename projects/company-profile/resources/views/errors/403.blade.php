@extends('layouts.app')

@section('title', 'Akses Ditolak | PT Moduvox Tech ID')
@section('meta_description', 'Anda tidak memiliki akses ke halaman ini.')
@section('meta_robots', 'noindex,nofollow')

@section('content')
<section class="pt-28 pb-20 bg-surface-container-low">
    <div class="max-w-7xl mx-auto px-8">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/70 border border-outline-variant/30 px-4 py-2 text-xs font-bold tracking-widest uppercase text-on-surface-variant">
                403
                <span class="material-symbols-outlined text-base">lock</span>
            </div>
            <h1 class="mt-6 font-headline font-extrabold text-4xl md:text-6xl text-on-surface tracking-tighter uppercase">
                Akses ditolak
            </h1>
            <p class="mt-5 text-on-surface-variant text-lg leading-relaxed">
                Halaman admin hanya dapat diakses oleh akun administrator. Jika Anda membutuhkan akses, silakan hubungi admin.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-3 bg-primary text-on-primary px-8 py-4 rounded-lg font-headline font-extrabold text-sm uppercase tracking-widest shadow-lg hover:bg-on-primary-fixed-variant transition-colors">
                    Kembali ke Beranda
                    <span class="material-symbols-outlined">arrow_right_alt</span>
                </a>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-3 bg-white border border-outline-variant/30 text-on-surface px-8 py-4 rounded-lg font-headline font-extrabold text-sm uppercase tracking-widest hover:shadow-md transition">
                    Login Admin
                    <span class="material-symbols-outlined">admin_panel_settings</span>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection


