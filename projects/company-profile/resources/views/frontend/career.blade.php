@extends('layouts.app')

@section('title', 'Karir & Human Capital | PT Moduvox Tech ID')
@section('meta_description', 'Bergabunglah dengan PT Moduvox Tech ID. Temukan peluang karir di bidang konstruksi, engineering, dan infrastruktur dalam lingkungan kerja yang profesional dan bertumbuh.')

@section('content')
{{-- =========================================================
     CAREER HERO — Industrial Employer
     ========================================================= --}}
<section class="relative min-h-[50vh] flex items-center justify-start bg-[#0F172A] overflow-hidden pt-20">
    <div class="absolute inset-0 industrial-grid opacity-10"></div>
    @php
        $heroImg = setting('career', 'career_hero_image');
        $banner = $heroImg ? asset('storage/' . $heroImg) : null;
    @endphp
    @if($banner)
        <img src="{{ $banner }}" class="absolute inset-0 w-full h-full object-cover grayscale opacity-20" alt="Operational Environment">
    @endif

    <div class="relative z-10 w-full max-w-7xl mx-auto px-8 py-20">
        <div class="max-w-3xl space-y-6">
            <div class="inline-flex items-center gap-3 px-4 py-1.5 bg-primary/10 border-l-4 border-primary">
                <span class="text-primary font-headline font-extrabold text-[10px] uppercase tracking-[0.3em]">Pengembangan Karir</span>
            </div>
            <h1 class="text-4xl md:text-7xl font-headline font-extrabold text-white leading-[0.9] tracking-tighter uppercase">
                {{ setting('career', 'career_hero_title', 'Bangun Karir Engineering Anda') }}
            </h1>
            <p class="text-white/50 text-lg md:text-xl font-medium max-w-xl border-l border-white/20 pl-6">
                {{ setting('career', 'career_hero_desc', 'Bergabunglah dengan tim profesional yang berdedikasi tinggi dalam mewujudkan infrastruktur nasional berkualitas.') }}
            </p>
        </div>
    </div>
</section>

{{-- =========================================================
     EMPLOYER VALUES — Discipline & Growth
     ========================================================= --}}
<section class="py-24 bg-background">
    <div class="max-w-7xl mx-auto px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-px bg-outline-variant border border-outline-variant shadow-2xl">
            <div class="bg-white p-10 space-y-6">
                <span class="material-symbols-outlined text-primary text-4xl">trending_up</span>
                <h3 class="font-headline font-extrabold text-xl text-on-background uppercase tracking-tight leading-none">Pengembangan Teknis</h3>
                <p class="text-[11px] text-on-surface-variant leading-relaxed">Kami menyediakan lingkungan yang mendukung peningkatan kompetensi teknis melalui proyek skala besar.</p>
            </div>
            <div class="bg-white p-10 space-y-6">
                <span class="material-symbols-outlined text-primary text-4xl">health_and_safety</span>
                <h3 class="font-headline font-extrabold text-xl text-on-background uppercase tracking-tight leading-none">Prioritas K3</h3>
                <p class="text-[11px] text-on-surface-variant leading-relaxed">Keselamatan personel adalah tanggung jawab mutlak kami dalam setiap fase operasional proyek.</p>
            </div>
            <div class="bg-white p-10 space-y-6">
                <span class="material-symbols-outlined text-primary text-4xl">handshake</span>
                <h3 class="font-headline font-extrabold text-xl text-on-background uppercase tracking-tight leading-none">Integritas Kerja</h3>
                <p class="text-[11px] text-on-surface-variant leading-relaxed">Budaya kerja profesional yang menjunjung tinggi kejujuran, disiplin, dan tanggung jawab teknis.</p>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
     VACANCY LOG — Structured List
     ========================================================= --}}
<section class="py-24 bg-white border-y border-outline-variant">
    <div class="max-w-7xl mx-auto px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16">
            <div class="max-w-2xl space-y-4">
                <span class="text-primary font-headline font-extrabold text-[11px] uppercase tracking-[0.4em]">Peluang Aktif</span>
                <h2 class="text-4xl font-headline font-extrabold text-on-background leading-none tracking-tighter uppercase">Lowongan Pekerjaan</h2>
            </div>
            <div class="inline-flex items-center gap-4 border border-outline-variant px-6 py-3 bg-background">
                <span class="w-2 h-2 rounded-none bg-green-600 animate-pulse"></span>
                <span class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">{{ $careers->count() }} Posisi Tersedia</span>
            </div>
        </div>

        <div class="space-y-6">
            @forelse($careers as $career)
            <a href="{{ route('career.show', $career->slug) }}" class="group block bg-white border border-outline-variant p-10 hover:border-primary transition-industrial relative overflow-hidden shadow-sm hover:shadow-2xl">
                <div class="absolute top-0 right-0 p-4 border-b border-l border-outline-variant group-hover:border-primary/20 transition-industrial">
                    <span class="text-[9px] font-bold text-on-surface-variant uppercase tracking-widest">JOB-DAP-{{ str_pad($career->id, 3, '0', STR_PAD_LEFT) }}</span>
                </div>
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-10">
                    <div class="flex-1 space-y-6">
                        <div class="flex flex-wrap items-center gap-4">
                            <span class="px-4 py-1.5 bg-background text-on-surface-variant text-[9px] font-black uppercase tracking-widest border border-outline-variant">
                                {{ $career->type }}
                            </span>
                            <span class="text-[9px] font-black text-primary uppercase tracking-widest flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">location_on</span>
                                {{ $career->location ?: 'Indonesia' }}
                            </span>
                        </div>
                        <h3 class="font-headline font-extrabold text-2xl md:text-3xl text-on-background uppercase tracking-tighter group-hover:text-primary transition-industrial">
                            {{ $career->title }}
                        </h3>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 border border-outline-variant flex items-center justify-center group-hover:bg-primary group-hover:border-primary transition-industrial">
                            <span class="material-symbols-outlined text-2xl group-hover:text-white">arrow_forward</span>
                        </div>
                    </div>
                </div>
            </a>
            @empty
            <div class="py-32 text-center border-2 border-dashed border-outline-variant space-y-6 bg-background">
                <span class="material-symbols-outlined text-6xl text-primary opacity-10">person_search</span>
                <p class="text-[10px] font-black text-on-surface-variant uppercase tracking-[0.4em]">Basis Data Karir Sedang Diperbarui</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

{{-- =========================================================
     TALENT DATABASE CTA
     ========================================================= --}}
<section class="py-32 bg-[#0F172A] relative overflow-hidden">
    <div class="absolute inset-0 industrial-grid opacity-5"></div>
    <div class="max-w-4xl mx-auto px-8 text-center relative z-10 space-y-10">
        <h2 class="text-3xl md:text-5xl font-headline font-extrabold text-white uppercase tracking-tighter leading-none italic">Belum Menemukan Posisi Yang Relevan?</h2>
        <p class="text-white/50 text-lg leading-relaxed max-w-2xl mx-auto">Kami tetap menerima aplikasi umum untuk database talenta kami. Kirimkan berkas lamaran Anda untuk diproses di masa mendatang.</p>
        <div class="flex flex-col md:flex-row items-center justify-center gap-8 pt-6">
            <a href="mailto:{{ setting('contact', 'contact_email') }}" class="group relative px-12 py-6 bg-primary text-white font-headline font-extrabold text-xs uppercase tracking-[0.3em] transition-industrial overflow-hidden shadow-2xl shadow-primary/20 w-full md:w-auto">
                <span class="relative z-10 flex items-center justify-center gap-4">
                    Kirim Lamaran Umum <span class="material-symbols-outlined">mail</span>
                </span>
                <div class="absolute inset-0 bg-primary-dark translate-y-full group-hover:translate-y-0 transition-industrial duration-500"></div>
            </a>
            <div class="text-white/40 font-bold uppercase tracking-widest text-[10px]">Atau Hubungi HRD: <span class="text-white ml-2">{{ setting('contact', 'contact_phone') }}</span></div>
        </div>
    </div>
</section>
@endsection

