@extends('layouts.app')
@section('title', 'Proyek Kami | PT Moduvox Tech ID')
@section('meta_description', 'Lihat portofolio proyek PT Moduvox Tech ID: konstruksi, infrastruktur, maintenance, dan engineering. Jelajahi kategori proyek dan konsultasikan kebutuhan Anda.')

@section('head')
    <style>
        .filter-btn.active {
            background-color: #B91C1C;
            color: white;
            border-color: #B91C1C;
        }
    </style>
@endsection

@section('content')
{{-- =========================================================
     PROJECTS HERO — Industrial Header
     ========================================================= --}}
<section class="relative min-h-[40vh] flex items-center justify-start bg-[#0F172A] overflow-hidden pt-20">
    <div class="absolute inset-0 industrial-grid opacity-10"></div>
    <div class="relative z-10 w-full max-w-7xl mx-auto px-8 py-20">
        <div class="max-w-3xl space-y-6">
            <span class="text-primary font-headline font-extrabold text-[11px] uppercase tracking-[0.4em]">Basis Data Portofolio</span>
            <h1 class="text-4xl md:text-7xl font-headline font-extrabold text-white leading-[0.9] tracking-tighter uppercase">
                {{ setting('project', 'project_hero_title', 'Rekam Jejak Proyek') }}
            </h1>
            <p class="text-white/50 text-lg md:text-xl font-medium max-w-xl border-l border-white/20 pl-6">
                {{ setting('project', 'project_hero_desc', 'Dokumentasi teknis penyelesaian berbagai proyek infrastruktur nasional dengan standar presisi tinggi.') }}
            </p>
        </div>
    </div>
</section>

{{-- =========================================================
     FILTER & SEARCH — High Contrast
     ========================================================= --}}
<section class="bg-background border-y border-outline-variant py-8 sticky top-20 z-40">
    <div class="max-w-7xl mx-auto px-8 flex flex-col lg:flex-row justify-between items-center gap-8">
        <div class="flex flex-wrap items-center gap-2" id="filter-btns">
            <button onclick="filterProjects('all')" data-filter="all" 
                    class="filter-btn active px-6 py-3 border border-outline-variant font-headline font-extrabold text-[10px] uppercase tracking-widest transition-industrial hover:border-primary">
                Semua Bidang
            </button>
            @foreach($categories as $cat)
            <button onclick="filterProjects('{{ Str::slug($cat) }}')" data-filter="{{ Str::slug($cat) }}" 
                    class="filter-btn px-6 py-3 border border-outline-variant font-headline font-extrabold text-[10px] uppercase tracking-widest transition-industrial hover:border-primary">
                {{ $cat }}
            </button>
            @endforeach
        </div>
        
        <div class="relative w-full lg:w-96">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
            <input id="search-input" oninput="searchProjects(this.value)" 
                   class="w-full pl-12 pr-4 py-4 bg-white border border-outline-variant rounded-none focus:border-primary text-on-background font-headline font-bold text-[11px] uppercase tracking-widest outline-none transition-industrial" 
                   placeholder="Cari Judul Proyek / Klien..."/>
        </div>
    </div>
</section>

{{-- =========================================================
     PROJECT GRID — Structured Log
     ========================================================= --}}
<section class="py-24 bg-background">
    <div class="max-w-7xl mx-auto px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10" id="projects-grid">
            @forelse($projects as $project)
                <div class="project-card group bg-white border border-outline-variant overflow-hidden transition-industrial"
                     data-category="{{ Str::slug($project->category) }}" data-title="{{ strtolower($project->title) }} {{ strtolower($project->client_name) }}">
                    
                    {{-- Visual documentation --}}
                    <div class="aspect-video relative overflow-hidden bg-surface border-b border-outline-variant">
                        @if($project->image)
                            <img src="{{ asset('storage/' . $project->image) }}" alt="{{ $project->title }}" 
                                 class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-industrial duration-700">
                        @else
                            <div class="w-full h-full flex items-center justify-center opacity-10">
                                <span class="material-symbols-outlined text-6xl">engineering</span>
                            </div>
                        @endif
                        
                        <div class="absolute top-0 left-0 p-4">
                            <span class="bg-primary text-white text-[9px] font-extrabold px-3 py-1 uppercase tracking-widest">
                                {{ $project->category }}
                            </span>
                        </div>
                    </div>

                    <div class="p-8 space-y-6">
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-px bg-primary/30"></div>
                                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-[0.2em]">{{ $project->client_name ?: 'General Contractor' }}</span>
                            </div>
                            <h3 class="text-xl font-headline font-extrabold text-on-background uppercase leading-tight tracking-tighter group-hover:text-primary transition-industrial">
                                {{ $project->title }}
                            </h3>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-outline-variant/30">
                            <div class="flex flex-col gap-1">
                                <span class="text-[9px] font-bold text-on-surface-variant uppercase tracking-widest opacity-50">Lokasi</span>
                                <span class="text-[10px] font-extrabold text-on-background uppercase tracking-wider truncate">{{ $project->location ?: 'Indonesia' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-[9px] font-bold text-on-surface-variant uppercase tracking-widest opacity-50">Tahun</span>
                                <span class="text-[10px] font-extrabold text-on-background uppercase tracking-wider">{{ $project->year ?: '2024' }}</span>
                            </div>
                        </div>

                        <a href="{{ $project->slug ? route('projects.show', $project->slug) : route('projects') }}" 
                           class="flex items-center justify-between w-full pt-4 border-t border-outline-variant/30 group/link">
                            <span class="text-[10px] font-black text-primary uppercase tracking-[0.3em]">Detail Rekam Jejak</span>
                            <span class="material-symbols-outlined text-sm text-primary group-hover/link:translate-x-2 transition-industrial">arrow_forward</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-12 text-center py-32 border-2 border-dashed border-outline-variant">
                    <span class="material-symbols-outlined text-6xl block mb-6 opacity-10">inventory_2</span>
                    <p class="text-on-surface-variant font-headline font-extrabold uppercase tracking-widest text-xs">Basis Data Proyek Sedang Diperbarui.</p>
                </div>
            @endforelse
        </div>

        @if($projects->hasPages())
        <div class="mt-20">
            {{ $projects->links() }}
        </div>
        @endif
    </div>
</section>

{{-- =========================================================
     CTA — Technical Consultation
     ========================================================= --}}
<section class="py-32 bg-surface relative overflow-hidden">
    <div class="absolute inset-0 industrial-grid opacity-5"></div>
    <div class="max-w-7xl mx-auto px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="space-y-8">
                <span class="text-primary font-headline font-extrabold text-[11px] uppercase tracking-[0.4em]">Hubungan Kemitraan</span>
                <h2 class="text-4xl md:text-6xl font-headline font-extrabold text-white leading-[0.95] tracking-tighter uppercase">
                    {{ setting('project', 'project_cta_title', 'Solusi Teknis Untuk Proyek Strategis.') }}
                </h2>
                <p class="text-white/50 text-lg leading-relaxed max-w-xl">
                    {{ setting('project', 'project_cta_desc', 'Diskusikan spesifikasi teknis dan tantangan operasional Anda bersama tim ahli kami untuk hasil yang optimal.') }}
                </p>
            </div>
            <div class="flex lg:justify-end">
                <a href="{{ route('contact') }}" class="group relative px-12 py-6 bg-primary text-white font-headline font-extrabold text-xs uppercase tracking-[0.3em] transition-industrial overflow-hidden">
                    <span class="relative z-10 flex items-center gap-4">
                        Konsultasi Sekarang <span class="material-symbols-outlined">arrow_right_alt</span>
                    </span>
                    <div class="absolute inset-0 bg-primary-dark translate-y-full group-hover:translate-y-0 transition-industrial duration-500"></div>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
function filterProjects(filter) {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        if (btn.dataset.filter === filter) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    document.querySelectorAll('.project-card').forEach(card => {
        const show = filter === 'all' || card.dataset.category === filter;
        card.style.display = show ? 'block' : 'none';
    });
}
function searchProjects(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.project-card').forEach(card => {
        card.style.display = card.dataset.title.includes(q) ? 'block' : 'none';
    });
}
</script>
@endsection

