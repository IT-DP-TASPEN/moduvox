@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan | PT Moduvox Tech ID')
@section('meta_description', 'Halaman yang Anda cari tidak ditemukan. Silakan kembali ke halaman utama PT Moduvox Tech ID.')
@section('meta_robots', 'noindex,nofollow')

@section('content')
<section class="pt-28 pb-20 bg-surface-container-low">
    <div class="max-w-7xl mx-auto px-8">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/70 border border-outline-variant/30 px-4 py-2 text-xs font-bold tracking-widest uppercase text-on-surface-variant">
                404
                <span class="material-symbols-outlined text-base">error</span>
            </div>
            <h1 class="mt-6 font-headline font-extrabold text-4xl md:text-6xl text-on-surface tracking-tighter uppercase">
                Halaman tidak ditemukan
            </h1>
            <p class="mt-5 text-on-surface-variant text-lg leading-relaxed">
                Link yang Anda buka mungkin sudah dipindah atau tidak tersedia. Gunakan tombol di bawah untuk kembali.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-3 bg-primary text-on-primary px-8 py-4 rounded-lg font-headline font-extrabold text-sm uppercase tracking-widest shadow-lg hover:bg-on-primary-fixed-variant transition-colors">
                    Kembali ke Beranda
                    <span class="material-symbols-outlined">arrow_right_alt</span>
                </a>
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-3 bg-white border border-outline-variant/30 text-on-surface px-8 py-4 rounded-lg font-headline font-extrabold text-sm uppercase tracking-widest hover:shadow-md transition">
                    Hubungi Kami
                    <span class="material-symbols-outlined">support_agent</span>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection


