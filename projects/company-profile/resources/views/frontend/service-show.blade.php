@extends('layouts.app')

@php
    $pageTitle = $service->name . ' | PT Moduvox Tech ID';
    $pageDescription = $service->description 
        ? \Illuminate\Support\Str::limit(strip_tags($service->description), 160)
        : 'Detail layanan ' . $service->name . ' PT Moduvox Tech ID.';
    $banner = $service->image ? asset('storage/' . $service->image) : null;
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)

@section('content')
{{-- =========================================================
     SERVICE HEADER — Industrial Detail
     ========================================================= --}}
<section class="pt-32 pb-20 bg-[#0F172A] relative overflow-hidden">
    <div class="absolute inset-0 industrial-grid opacity-10"></div>
    <div class="max-w-7xl mx-auto px-8 relative z-10">
        {{-- Breadcrumb — Technical Style --}}
        <nav class="flex items-center gap-3 text-[10px] font-black text-white/30 uppercase tracking-[0.3em] mb-12">
            <a href="{{ route('home') }}" class="hover:text-primary transition-industrial">Home</a>
            <span>/</span>
            <a href="{{ route('services') }}" class="hover:text-primary transition-industrial">Layanan</a>
            <span>/</span>
            <span class="text-white">{{ $service->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            <div class="lg:col-span-8 space-y-8">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 bg-primary flex items-center justify-center border border-white/10 shadow-2xl">
                        <span class="material-symbols-outlined text-white text-3xl">{{ $service->icon }}</span>
                    </div>
                    <div class="space-y-1">
                        <span class="text-primary font-headline font-extrabold text-[10px] uppercase tracking-[0.4em]">Spesialisasi Engineering</span>
                        <h1 class="font-headline font-extrabold text-4xl md:text-6xl text-white leading-[0.95] tracking-tighter uppercase">
                            {{ $service->name }}
                        </h1>
                    </div>
                </div>
                <p class="text-white/50 text-lg md:text-xl leading-relaxed max-w-2xl border-l-4 border-primary pl-6">
                    {{ $service->description }}
                </p>
            </div>
            <div class="lg:col-span-4 flex lg:justify-end">
                <div class="bg-white/5 border border-white/10 p-6 backdrop-blur-sm">
                    <div class="flex flex-col gap-2">
                        <span class="text-[9px] font-bold text-white/30 uppercase tracking-[0.4em]">Service Code</span>
                        <span class="text-xs font-black text-white uppercase tracking-widest">DAP-SRV-{{ str_pad($service->id, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
     SERVICE CONTENT — Technical Brief
     ========================================================= --}}
<section class="py-24 bg-background">
    <div class="max-w-7xl mx-auto px-8">
        <div class="flex flex-col lg:flex-row gap-20">
            
            {{-- Main Content Side --}}
            <div class="flex-1 space-y-16">
                @if($banner)
                <div class="border border-outline-variant bg-white p-2">
                    <img src="{{ $banner }}" class="w-full h-auto grayscale-[0.2]" alt="{{ $service->name }}">
                </div>
                @endif

                <div class="space-y-10">
                    <div class="space-y-8">
                        <h2 class="font-headline font-extrabold text-2xl text-on-background uppercase tracking-tighter border-l-4 border-primary pl-6">Cakupan Teknis</h2>
                        @if($service->content)
                            <div class="text-on-background/70 text-lg leading-relaxed whitespace-pre-line prose-industrial">
                                {!! nl2br(e($service->content)) !!}
                            </div>
                        @else
                            <div class="p-10 bg-white border border-outline-variant text-center space-y-4">
                                <span class="material-symbols-outlined text-5xl text-primary opacity-10">construction</span>
                                <p class="text-xs font-extrabold text-on-surface-variant uppercase tracking-widest">Detail teknis sedang dalam proses verifikasi.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Performance Standards Grid --}}
                    <div class="pt-16 border-t border-outline-variant/30 space-y-10">
                        <h3 class="font-headline font-extrabold text-2xl text-on-background uppercase tracking-tighter border-l-4 border-primary pl-6">Standar Operasional</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-px bg-outline-variant border border-outline-variant">
                            <div class="bg-white p-8 space-y-4">
                                <span class="material-symbols-outlined text-primary text-3xl">health_and_safety</span>
                                <h4 class="font-headline font-extrabold text-sm text-on-background uppercase tracking-tight">K3 Zero Accident</h4>
                                <p class="text-[11px] text-on-surface-variant leading-relaxed">Penerapan sistem manajemen keselamatan kerja ketat di setiap titik operasional.</p>
                            </div>
                            <div class="bg-white p-8 space-y-4">
                                <span class="material-symbols-outlined text-primary text-3xl">verified_user</span>
                                <h4 class="font-headline font-extrabold text-sm text-on-background uppercase tracking-tight">Quality Assurance</h4>
                                <p class="text-[11px] text-on-surface-variant leading-relaxed">Pengawasan mutu material dan metode kerja secara berlapis untuk hasil presisi.</p>
                            </div>
                            <div class="bg-white p-8 space-y-4">
                                <span class="material-symbols-outlined text-primary text-3xl">precision_manufacturing</span>
                                <h4 class="font-headline font-extrabold text-sm text-on-background uppercase tracking-tight">Technical Mastery</h4>
                                <p class="text-[11px] text-on-surface-variant leading-relaxed">Dukungan tenaga ahli tersertifikasi dan peralatan kerja standar industri.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar — Operational Contact --}}
            <div class="w-full lg:w-96 space-y-10">
                <div class="bg-[#0F172A] p-10 relative overflow-hidden border border-white/5 shadow-2xl">
                    <div class="absolute top-0 right-0 p-4 border-b border-l border-white/10">
                        <span class="text-[9px] font-bold text-white/30 uppercase tracking-widest">DAP-INQ-01</span>
                    </div>
                    <div class="space-y-8 relative z-10">
                        <h4 class="font-headline font-extrabold text-xl text-white uppercase tracking-tight">Inquiry Layanan</h4>
                        <p class="text-white/50 text-sm leading-relaxed">Dapatkan evaluasi teknis dan estimasi penawaran untuk kebutuhan <strong class="text-white">{{ $service->name }}</strong>.</p>
                        
                        <div class="space-y-4 pt-6 border-t border-white/10">
                            <div class="flex items-center gap-4 group">
                                <span class="material-symbols-outlined text-primary text-lg">mail</span>
                                <span class="text-[11px] font-bold text-white uppercase tracking-widest truncate">{{ setting('contact', 'contact_email', 'info@moduvox.id') }}</span>
                            </div>
                            <div class="flex items-center gap-4 group">
                                <span class="material-symbols-outlined text-primary text-lg">call</span>
                                <span class="text-[11px] font-bold text-white uppercase tracking-widest">{{ setting('contact', 'contact_phone', '+62 (21) 555-0123') }}</span>
                            </div>
                        </div>

                        <a href="{{ route('contact') }}?service={{ urlencode($service->name) }}" 
                           class="flex items-center justify-center gap-4 bg-primary text-white w-full py-5 font-headline font-extrabold text-xs uppercase tracking-widest hover:bg-primary-dark transition-industrial">
                            Kirim Permintaan
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                    </div>
                </div>

                {{-- Catalog Navigation --}}
                @if($related->count() > 0)
                <div class="space-y-6">
                    <h4 class="font-headline font-extrabold text-sm text-on-background uppercase tracking-[0.2em] border-b border-outline-variant pb-4">Layanan Lain</h4>
                    <div class="space-y-3">
                        @foreach($related as $rel)
                        <a href="{{ route('services.show', $rel->slug) }}" class="flex items-center justify-between p-5 bg-white border border-outline-variant hover:border-primary transition-industrial group">
                            <div class="flex items-center gap-4">
                                <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-industrial">{{ $rel->icon }}</span>
                                <span class="text-[11px] font-extrabold text-on-background uppercase tracking-wider group-hover:text-primary transition-industrial">{{ $rel->name }}</span>
                            </div>
                            <span class="material-symbols-outlined text-sm text-primary opacity-0 group-hover:opacity-100 transition-industrial">arrow_forward</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>
</section>
@endsection

