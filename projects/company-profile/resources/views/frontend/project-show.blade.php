@extends('layouts.app')

@php
    $pageTitle = $project->title . ' | PT Moduvox Tech ID';
    $pageDescription = $project->description
        ? \Illuminate\Support\Str::limit(strip_tags($project->description), 160)
        : 'Detail proyek PT Moduvox Tech ID pada bidang konstruksi, infrastruktur, dan engineering.';
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)

@section('content')
{{-- =========================================================
     PROJECT HEADER — Industrial Detail
     ========================================================= --}}
<section class="pt-32 pb-16 bg-[#0F172A] relative overflow-hidden">
    <div class="absolute inset-0 industrial-grid opacity-10"></div>
    <div class="max-w-7xl mx-auto px-8 relative z-10">
        <a href="{{ route('projects') }}" class="inline-flex items-center gap-3 text-[10px] font-black text-primary uppercase tracking-[0.3em] mb-12 group">
            <span class="material-symbols-outlined text-sm transition-industrial group-hover:-translate-x-2">arrow_back</span>
            Kembali ke Indeks Proyek
        </a>
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-end">
            <div class="lg:col-span-8 space-y-6">
                <div class="inline-flex items-center gap-3 px-4 py-1.5 bg-primary/10 border-l-4 border-primary">
                    <span class="text-primary font-headline font-extrabold text-[10px] uppercase tracking-[0.3em]">
                        {{ $project->category }}
                    </span>
                </div>
                <h1 class="font-headline font-extrabold text-4xl md:text-7xl text-white leading-[0.95] tracking-tighter uppercase">
                    {{ $project->title }}
                </h1>
            </div>
            <div class="lg:col-span-4 flex lg:justify-end">
                <div class="flex flex-col gap-2 border-l border-white/20 pl-8">
                    <span class="text-[9px] font-bold text-white/30 uppercase tracking-[0.4em]">Project ID</span>
                    <span class="text-xs font-black text-white uppercase tracking-widest">DAP-PRJ-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
     TECHNICAL SPECIFICATIONS
     ========================================================= --}}
<section class="py-20 bg-background border-b border-outline-variant">
    <div class="max-w-7xl mx-auto px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
            {{-- Left Side: Details & Scope --}}
            <div class="lg:col-span-8 space-y-16">
                {{-- Data Grid --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-px bg-outline-variant border border-outline-variant shadow-xl">
                    <div class="bg-white p-8">
                        <div class="text-[9px] font-bold text-on-surface-variant uppercase tracking-widest mb-2 opacity-50">Klien</div>
                        <div class="text-sm font-black text-on-background uppercase tracking-tight">{{ $project->client_name ?: 'Industrial Partner' }}</div>
                    </div>
                    <div class="bg-white p-8">
                        <div class="text-[9px] font-bold text-on-surface-variant uppercase tracking-widest mb-2 opacity-50">Lokasi</div>
                        <div class="text-sm font-black text-on-background uppercase tracking-tight">{{ $project->location ?: 'Indonesia' }}</div>
                    </div>
                    <div class="bg-white p-8">
                        <div class="text-[9px] font-bold text-on-surface-variant uppercase tracking-widest mb-2 opacity-50">Tahun</div>
                        <div class="text-sm font-black text-on-background uppercase tracking-tight">{{ $project->year ?: '-' }}</div>
                    </div>
                    <div class="bg-white p-8">
                        <div class="text-[9px] font-bold text-on-surface-variant uppercase tracking-widest mb-2 opacity-50">Status Operasi</div>
                        <div class="text-[10px] font-black text-green-700 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-2 h-2 rounded-none bg-green-700"></span>
                            Terverifikasi
                        </div>
                    </div>
                </div>

                <div class="space-y-10">
                    <div class="space-y-6">
                        <h3 class="font-headline font-extrabold text-2xl text-on-background uppercase tracking-tighter border-l-4 border-primary pl-6">Deskripsi Proyek</h3>
                        <div class="text-on-background/70 text-lg leading-relaxed whitespace-pre-line">
                            {{ $project->description }}
                        </div>
                    </div>

                    @if($project->project_scope)
                    <div class="space-y-8 pt-8 border-t border-outline-variant/30">
                        <h3 class="font-headline font-extrabold text-2xl text-on-background uppercase tracking-tighter border-l-4 border-primary pl-6">Cakupan Pekerjaan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $scopes = array_values(array_filter(array_map('trim', preg_split("/\\r?\\n/", (string) $project->project_scope))));
                            @endphp
                            @foreach($scopes as $scope)
                            <div class="flex items-center gap-4 p-5 bg-white border border-outline-variant group hover:border-primary transition-industrial">
                                <span class="material-symbols-outlined text-primary text-xl">settings_input_component</span>
                                <span class="text-[11px] font-extrabold text-on-background uppercase tracking-wider">{{ $scope }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Gallery — Technical Documentation --}}
                @if($project->images->count() > 0)
                <div class="space-y-8 pt-8 border-t border-outline-variant/30">
                    <h3 class="font-headline font-extrabold text-2xl text-on-background uppercase tracking-tighter border-l-4 border-primary pl-6">Dokumentasi Lapangan</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                        @foreach($project->images as $img)
                        <div class="group relative aspect-square bg-surface border border-outline-variant overflow-hidden cursor-zoom-in"
                             onclick="openLightbox('{{ asset('storage/' . $img->image_path) }}')">
                            <img src="{{ asset('storage/' . $img->image_path) }}" 
                                 class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-industrial duration-700" 
                                 alt="Technical documentation">
                            <div class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-industrial flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-3xl">zoom_in</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Right Side: Technical Banner & CTA --}}
            <div class="lg:col-span-4 space-y-10">
                <div class="border border-outline-variant bg-white p-2">
                    <img src="{{ asset('storage/' . $project->image) }}" class="w-full h-auto grayscale-[0.3]" alt="{{ $project->title }}">
                </div>
                
                <div class="bg-[#0F172A] p-10 relative overflow-hidden border border-white/5 shadow-2xl">
                    <div class="absolute top-0 right-0 p-4 border-b border-l border-white/10">
                        <span class="text-[9px] font-bold text-white/30 uppercase tracking-widest">DAP-ENG-04</span>
                    </div>
                    <div class="space-y-6 relative z-10">
                        <h4 class="font-headline font-extrabold text-xl text-white uppercase tracking-tight">Kemitraan Teknis</h4>
                        <p class="text-white/50 text-sm leading-relaxed">Hubungi kami untuk mendapatkan solusi engineering dan konstruksi serupa untuk proyek strategis Anda.</p>
                        <a href="{{ route('contact') }}?project={{ $project->title }}" 
                           class="flex items-center justify-center gap-4 bg-primary text-white w-full py-5 font-headline font-extrabold text-xs uppercase tracking-widest hover:bg-primary-dark transition-industrial shadow-xl shadow-primary/20">
                            Ajukan Penawaran
                            <span class="material-symbols-outlined text-sm">arrow_right_alt</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Related Projects --}}
@if($related->count())
<section class="py-32 bg-background">
    <div class="max-w-7xl mx-auto px-8">
        <div class="flex items-end justify-between gap-8 mb-16">
            <div class="space-y-4">
                <span class="text-primary font-headline font-extrabold text-[11px] uppercase tracking-[0.4em]">Basis Data Terkait</span>
                <h2 class="text-4xl font-headline font-extrabold text-on-background leading-none tracking-tighter uppercase">Proyek Serupa</h2>
            </div>
            <a href="{{ route('projects') }}" class="font-headline font-extrabold text-[11px] uppercase tracking-[0.2em] text-on-background border-b-2 border-primary pb-2 hover:text-primary transition-industrial">
                Lihat Semua &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            @foreach($related as $p)
            <a href="{{ route('projects.show', $p->slug) }}" class="group block bg-white border border-outline-variant overflow-hidden transition-industrial">
                <div class="aspect-video overflow-hidden border-b border-outline-variant">
                    <img src="{{ asset('storage/' . $p->image) }}" 
                         class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-industrial duration-700" 
                         alt="{{ $p->title }}">
                </div>
                <div class="p-8 space-y-4">
                    <span class="text-[9px] font-bold text-primary uppercase tracking-widest">{{ $p->category }}</span>
                    <h4 class="font-headline font-extrabold text-xl text-on-background group-hover:text-primary transition-industrial uppercase leading-tight tracking-tight">{{ $p->title }}</h4>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Lightbox — Engineering View --}}
<div id="lightbox" class="fixed inset-0 z-[100] bg-[#0F172A]/95 hidden items-center justify-center p-8 backdrop-blur-sm transition-industrial" onclick="closeLightbox()">
    <button class="absolute top-8 right-8 text-white/50 hover:text-white transition-industrial">
        <span class="material-symbols-outlined text-5xl">close</span>
    </button>
    <img id="lightbox-img" src="" class="max-w-full max-h-full border-4 border-white/10 shadow-2xl scale-95 transition-industrial" onclick="event.stopPropagation()">
</div>
@endsection

@section('scripts')
<script>
function openLightbox(src) {
    const lb = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    img.src = src;
    lb.classList.remove('hidden');
    lb.classList.add('flex');
    setTimeout(() => img.classList.replace('scale-95', 'scale-100'), 10);
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lb = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    img.classList.replace('scale-100', 'scale-95');
    setTimeout(() => {
        lb.classList.add('hidden');
        lb.classList.remove('flex');
    }, 200);
    document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeLightbox();
});
</script>
@endsection

