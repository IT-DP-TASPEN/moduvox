@extends('layouts.app')

@section('title', 'Layanan Engineering | PT Moduvox Tech ID')
@section('meta_description', 'Layanan PT Moduvox Tech ID: konstruksi infrastruktur, gedung, engineering, general contractor, maintenance, pengadaan, dan QA/QC. Solusi konstruksi profesional dan terpercaya.')

@section('content')
{{-- =========================================================
     SERVICES HERO — Industrial Header
     ========================================================= --}}
<section class="relative min-h-[50vh] flex items-center justify-start bg-[#0F172A] overflow-hidden pt-20">
    <div class="absolute inset-0 industrial-grid opacity-10"></div>
    <div class="relative z-10 w-full max-w-7xl mx-auto px-8 py-20">
        <div class="max-w-3xl space-y-6">
            <div class="inline-flex items-center gap-3 px-4 py-1.5 bg-primary/10 border-l-4 border-primary">
                <span class="text-primary font-headline font-extrabold text-[10px] uppercase tracking-[0.3em]">Kapabilitas Operasional</span>
            </div>
            <h1 class="text-4xl md:text-7xl font-headline font-extrabold text-white leading-[0.9] tracking-tighter uppercase">
                {{ setting('services', 'services_hero_title', 'Konstruksi & Engineering') }}
            </h1>
            <p class="text-white/50 text-lg md:text-xl font-medium max-w-xl border-l border-white/20 pl-6">
                {{ setting('services', 'services_hero_desc', 'Solusi konstruksi terintegrasi yang mengedepankan presisi teknis, standar keselamatan K3, dan efisiensi operasional.') }}
            </p>
        </div>
    </div>
</section>

{{-- =========================================================
     SERVICES GRID — Structured Capabilities
     ========================================================= --}}
<section class="py-24 bg-background">
    <div class="max-w-7xl mx-auto px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-px bg-outline-variant border border-outline-variant shadow-2xl">
            @forelse($services as $service)
                <a href="{{ $service->slug ? route('services.show', $service->slug) : route('services') }}" class="group bg-white p-10 hover:bg-[#0F172A] transition-industrial relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-industrial">
                        <span class="text-4xl font-headline font-black text-on-background group-hover:text-white">{{ $loop->iteration }}</span>
                    </div>
                    
                    <div class="w-14 h-14 bg-background flex items-center justify-center mb-10 border border-outline-variant group-hover:bg-primary group-hover:border-primary transition-industrial">
                        <span class="material-symbols-outlined text-primary text-3xl group-hover:text-white transition-industrial">{{ $service->icon }}</span>
                    </div>
                    
                    <h2 class="text-xl font-headline font-extrabold text-on-background uppercase leading-tight tracking-tighter group-hover:text-white transition-industrial mb-6">
                        {{ $service->name }}
                    </h2>
                    
                    <p class="text-on-surface-variant text-sm leading-relaxed group-hover:text-white/60 transition-industrial mb-10">
                        {{ $service->description }}
                    </p>

                    <div class="flex items-center justify-between pt-6 border-t border-outline-variant group-hover:border-white/10 transition-industrial">
                        <span class="text-[10px] font-black uppercase tracking-widest text-primary group-hover:text-white">Detail Kapabilitas</span>
                        <span class="material-symbols-outlined text-primary text-xl group-hover:text-white transition-industrial">arrow_forward</span>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-32 text-center bg-white border border-outline-variant">
                    <span class="material-symbols-outlined text-7xl text-primary opacity-10 mb-6">precision_manufacturing</span>
                    <h3 class="text-xs font-headline font-extrabold uppercase tracking-widest text-on-surface-variant">Basis Data Layanan Sedang Diperbarui</h3>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- =========================================================
     INDUSTRIAL CTA
     ========================================================= --}}
<section class="py-32 bg-[#0F172A] relative overflow-hidden">
    <div class="absolute inset-0 industrial-grid opacity-5"></div>
    <div class="max-w-7xl mx-auto px-8 relative z-10">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-16">
            <div class="max-w-2xl space-y-6 text-center lg:text-left">
                <span class="text-primary font-headline font-extrabold text-[11px] uppercase tracking-[0.4em]">Mulai Konsultasi</span>
                <h2 class="text-4xl md:text-6xl font-headline font-extrabold text-white leading-[0.9] tracking-tighter uppercase mb-6">
                    {{ setting('services', 'services_cta_title', 'Butuh Solusi Konstruksi Strategis?') }}
                </h2>
                <p class="text-white/50 text-lg leading-relaxed max-w-xl mx-auto lg:mx-0">
                    {{ setting('services', 'services_cta_desc', 'Diskusikan rencana kerja Anda bersama tenaga ahli kami untuk mendapatkan evaluasi teknis yang komprehensif.') }}
                </p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-6">
                <a href="{{ route('contact') }}" class="group relative px-12 py-6 bg-primary text-white font-headline font-extrabold text-xs uppercase tracking-[0.3em] transition-industrial overflow-hidden text-center w-full sm:w-auto shadow-2xl shadow-primary/20">
                    <span class="relative z-10 flex items-center justify-center gap-4">
                        Hubungi Tim Kami <span class="material-symbols-outlined">send</span>
                    </span>
                    <div class="absolute inset-0 bg-primary-dark translate-y-full group-hover:translate-y-0 transition-industrial duration-500"></div>
                </a>
                <a href="{{ route('projects') }}" class="px-12 py-6 border border-white/20 text-white font-headline font-extrabold text-xs uppercase tracking-[0.3em] hover:bg-white/5 transition-industrial text-center w-full sm:w-auto">
                    Lihat Portofolio
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

