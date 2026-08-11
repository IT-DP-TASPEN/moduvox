@extends('layouts.app')

@section('title', $career->title . ' | Karir PT Moduvox Tech ID')
@section('meta_description', 'Detail lowongan kerja ' . $career->title . ' di PT Moduvox Tech ID. Bergabunglah dengan tim konstruksi dan engineering kami.')

@section('content')
{{-- =========================================================
     CAREER HEADER — Job Specification
     ========================================================= --}}
<section class="pt-32 pb-16 bg-surface relative overflow-hidden">
    <div class="absolute inset-0 industrial-grid opacity-10"></div>
    <div class="max-w-7xl mx-auto px-8 relative z-10">
        <a href="{{ route('career') }}" class="inline-flex items-center gap-3 text-[10px] font-black text-primary uppercase tracking-[0.3em] mb-12 group">
            <span class="material-symbols-outlined text-sm transition-industrial group-hover:-translate-x-2">arrow_back</span>
            Kembali ke Daftar Lowongan
        </a>
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-12">
            <div class="space-y-6">
                <div class="flex flex-wrap items-center gap-4">
                    <span class="px-4 py-1.5 bg-primary/10 border-l-4 border-primary text-primary font-headline font-extrabold text-[10px] uppercase tracking-[0.3em]">
                        {{ $career->type }}
                    </span>
                    <span class="text-[10px] font-black text-white/40 uppercase tracking-[0.2em] flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">location_on</span>
                        {{ $career->location ?: 'Indonesia' }}
                    </span>
                </div>
                <h1 class="font-headline font-extrabold text-4xl md:text-7xl text-white leading-[0.95] tracking-tighter uppercase">
                    {{ $career->title }}
                </h1>
            </div>
            <div class="flex-shrink-0">
                <a href="#apply" class="group relative inline-flex px-12 py-6 bg-primary text-white font-headline font-extrabold text-xs uppercase tracking-[0.3em] transition-industrial overflow-hidden shadow-2xl shadow-primary/20">
                    <span class="relative z-10 flex items-center gap-4">
                        Lamar Sekarang <span class="material-symbols-outlined">person_add</span>
                    </span>
                    <div class="absolute inset-0 bg-primary-dark translate-y-full group-hover:translate-y-0 transition-industrial duration-500"></div>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
     JOB DETAILS — Technical Requirements
     ========================================================= --}}
<section class="py-24 bg-background">
    <div class="max-w-7xl mx-auto px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-20 items-start">
            
            {{-- Main Content Side --}}
            <div class="lg:col-span-8 space-y-16">
                <div class="space-y-8">
                    <h3 class="font-headline font-extrabold text-2xl text-on-background uppercase tracking-tighter border-l-4 border-primary pl-6">Deskripsi Pekerjaan</h3>
                    <div class="text-on-background/70 text-lg leading-relaxed whitespace-pre-line prose-industrial">
                        {!! nl2br(e($career->description)) !!}
                    </div>
                </div>

                @if($career->requirements)
                <div class="space-y-8 pt-8 border-t border-outline-variant/30">
                    <h3 class="font-headline font-extrabold text-2xl text-on-background uppercase tracking-tighter border-l-4 border-primary pl-6">Kualifikasi Teknis</h3>
                    <div class="grid grid-cols-1 gap-4">
                        @php
                            $reqs = array_values(array_filter(array_map('trim', preg_split("/\\r?\\n/", (string) $career->requirements))));
                        @endphp
                        @foreach($reqs as $req)
                        <div class="flex items-center gap-5 p-6 bg-white border border-outline-variant hover:border-primary transition-industrial">
                            <span class="material-symbols-outlined text-primary text-xl">check_circle</span>
                            <span class="text-[11px] font-extrabold text-on-background uppercase tracking-wider leading-relaxed">{{ $req }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Compensation & Benefits — Sharp Grid --}}
                <div class="pt-16 border-t border-outline-variant/30 space-y-10">
                    <h3 class="font-headline font-extrabold text-2xl text-on-background uppercase tracking-tighter border-l-4 border-primary pl-6">Benefit Karyawan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-px bg-outline-variant border border-outline-variant">
                        <div class="bg-white p-8 flex items-center gap-6 group">
                            <span class="material-symbols-outlined text-primary text-3xl group-hover:scale-110 transition-industrial">payments</span>
                            <span class="text-[11px] font-black text-on-background uppercase tracking-widest">Remunerasi Kompetitif</span>
                        </div>
                        <div class="bg-white p-8 flex items-center gap-6 group">
                            <span class="material-symbols-outlined text-primary text-3xl group-hover:scale-110 transition-industrial">health_and_safety</span>
                            <span class="text-[11px] font-black text-on-background uppercase tracking-widest">Proteksi Kesehatan & K3</span>
                        </div>
                        <div class="bg-white p-8 flex items-center gap-6 group">
                            <span class="material-symbols-outlined text-primary text-3xl group-hover:scale-110 transition-industrial">school</span>
                            <span class="text-[11px] font-black text-on-background uppercase tracking-widest">Pelatihan Profesional</span>
                        </div>
                        <div class="bg-white p-8 flex items-center gap-6 group">
                            <span class="material-symbols-outlined text-primary text-3xl group-hover:scale-110 transition-industrial">diversity_3</span>
                            <span class="text-[11px] font-black text-on-background uppercase tracking-widest">Kultur Kerja Kolaboratif</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar — Submission Info --}}
            <div id="apply" class="lg:col-span-4 space-y-10 sticky top-32">
                <div class="bg-surface p-10 relative overflow-hidden border border-white/5 shadow-2xl">
                    <div class="absolute top-0 right-0 p-4 border-b border-l border-white/10">
                        <span class="text-[9px] font-bold text-white/30 uppercase tracking-widest">RECRUITMENT-INQ</span>
                    </div>
                    <div class="space-y-8 relative z-10">
                        <h4 class="font-headline font-extrabold text-xl text-white uppercase tracking-tight">Prosedur Lamaran</h4>
                        <p class="text-white/50 text-sm leading-relaxed">Kirimkan berkas lamaran (CV & Portofolio) ke alamat email HRD kami dengan format subjek yang ditentukan.</p>
                        
                        <div class="space-y-6 pt-6 border-t border-white/10">
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-white/30 uppercase tracking-[0.2em]">Email Korespondensi</label>
                                <p class="text-xs font-black text-primary uppercase tracking-widest">{{ setting('contact', 'contact_email', 'hrd@moduvox.id') }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-white/30 uppercase tracking-[0.2em]">Format Subjek Email</label>
                                <p class="text-xs font-black text-white uppercase tracking-widest leading-relaxed">LAMAR_{{ str_replace(' ', '', strtoupper($career->title)) }}_[NAMA LENGKAP]</p>
                            </div>
                        </div>

                        <a href="mailto:{{ setting('contact', 'contact_email') }}?subject=LAMAR_{{ str_replace(' ', '', strtoupper($career->title)) }}" 
                           class="flex items-center justify-center gap-4 bg-primary text-white w-full py-5 font-headline font-extrabold text-xs uppercase tracking-widest hover:bg-primary-dark transition-industrial">
                            Kirim Berkas Sekarang
                            <span class="material-symbols-outlined text-sm">send</span>
                        </a>
                        
                        <div class="p-6 bg-white/5 border border-white/10 flex items-start gap-4">
                            <span class="material-symbols-outlined text-primary text-2xl">warning</span>
                            <p class="text-[10px] text-white/40 font-bold uppercase tracking-widest leading-relaxed">Proses rekrutmen tidak dipungut biaya apapun.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection

