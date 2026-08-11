@extends('layouts.app')

@section('title', 'PT Moduvox Tech ID | Kontraktor & Jasa Konstruksi Infrastruktur Nasional')
@section('meta_description', 'PT Moduvox Tech ID - Perusahaan kontraktor dan general contractor terpercaya di Jakarta. Ahli dalam pembangunan infrastruktur, konstruksi rumah sakit, dan engineering services dengan standar kualitas tinggi.')

@section('content')

{{-- =========================================================
     HERO SECTION — Industrial Focus
     ========================================================= --}}
<section class="relative min-h-[90vh] w-full flex items-center justify-start overflow-hidden bg-[#0F172A]" id="home">
    {{-- Hero Background --}}
    <div class="absolute inset-0 w-full h-full">
        @php
            $heroVideo = setting('home', 'home_hero_video');
            $heroPoster = setting('home', 'home_hero_video_poster');
            $defaultPoster = asset('dap.png');
            $posterUrl = $heroPoster ? asset('storage/' . $heroPoster) : $defaultPoster;
            $heroVideoType = $heroVideo && \Illuminate\Support\Str::endsWith($heroVideo, '.webm') ? 'video/webm' : 'video/mp4';
        @endphp
        <video autoplay loop muted playsinline preload="metadata" poster="{{ $posterUrl }}" 
               class="w-full h-full object-cover opacity-40 grayscale-[0.5]" aria-hidden="true">
            @if($heroVideo)
                <source src="{{ asset('storage/' . $heroVideo) }}" type="{{ $heroVideoType }}" />
            @else
                <source src="{{ asset('video/hero.mp4') }}" type="video/mp4" />
            @endif
        </video>
        {{-- Industrial Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-r from-surface via-surface/80 to-transparent"></div>
        <div class="absolute inset-0 industrial-grid opacity-10"></div>
    </div>

    <div class="relative z-10 w-full max-w-7xl mx-auto px-8 py-20">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-3 px-4 py-2 bg-primary/10 border-l-4 border-primary mb-8">
                <span class="text-primary font-headline font-extrabold text-[10px] uppercase tracking-[0.3em]">Operational Excellence</span>
            </div>
            
            <h1 class="font-headline font-extrabold text-5xl md:text-7xl lg:text-8xl text-white leading-[0.9] tracking-tighter uppercase mb-8">
                {{ setting('home', 'home_hero_title', 'Engineering & Construction') }}
            </h1>
            
            <p class="text-white/60 text-lg md:text-xl leading-relaxed font-medium mb-12 max-w-2xl border-l border-white/20 pl-6">
                {{ setting('home', 'home_hero_subtitle', 'Penyedia jasa konstruksi infrastruktur, mekanikal, dan elektrikal dengan spesialisasi pada akurasi teknis dan ketepatan eksekusi.') }}
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-4">
                <a href="{{ route('projects') }}"
                   class="w-full sm:w-auto px-10 py-5 bg-primary text-white font-headline font-extrabold text-xs uppercase tracking-widest hover:bg-primary-dark transition-industrial shadow-xl shadow-primary/20">
                    Lihat Portofolio Proyek
                </a>
                <a href="{{ route('contact') }}"
                   class="w-full sm:w-auto px-10 py-5 border border-white/20 text-white font-headline font-extrabold text-xs uppercase tracking-widest hover:bg-white/5 transition-industrial">
                    Konsultasi Teknis
                </a>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
     OPERATIONAL STATS — Grounded
     ========================================================= --}}
<div class="relative z-20 -mt-16 max-w-7xl mx-auto px-8">
    <div class="grid grid-cols-2 md:grid-cols-4 bg-white border border-outline-variant shadow-2xl">
        <div class="p-8 border-r border-outline-variant">
            <div class="text-3xl font-black text-on-background font-headline">{{ setting('home', 'home_stats_years', '15+') }}</div>
            <div class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mt-2">Tahun Pengalaman</div>
        </div>
        <div class="p-8 md:border-r border-outline-variant">
            <div class="text-3xl font-black text-on-background font-headline">{{ setting('home', 'home_stats_projects', '200+') }}</div>
            <div class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mt-2">Proyek Selesai</div>
        </div>
        <div class="p-8 border-r border-outline-variant">
            <div class="text-3xl font-black text-on-background font-headline">{{ setting('home', 'home_stats_clients', '50+') }}</div>
            <div class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mt-2">Mitra Strategis</div>
        </div>
        <div class="p-8">
            <div class="text-3xl font-black text-on-background font-headline">{{ setting('home', 'home_stats_regions', '12+') }}</div>
            <div class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mt-2">Wilayah Operasi</div>
        </div>
    </div>
</div>

{{-- =========================================================
     ABOUT SECTION — Professional & Industrial
     ========================================================= --}}
<section class="py-32 px-8 bg-background" id="about">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-start">
            <div class="lg:col-span-7 space-y-10">
                <div class="space-y-4">
                    <span class="text-primary font-headline font-extrabold text-[11px] uppercase tracking-[0.4em]">Profil Perusahaan</span>
                    <h2 class="text-4xl md:text-6xl font-headline font-extrabold text-on-background leading-[0.95] tracking-tighter uppercase">
                        {{ setting('home', 'home_about_title', 'Komitmen Pada Presisi & Integritas Konstruksi.') }}
                    </h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-4">
                        <div class="w-10 h-1 bg-primary"></div>
                        <p class="text-on-background/70 text-base leading-relaxed">
                            {{ setting('home', 'home_about_desc', 'PT Moduvox Tech ID hadir sebagai solusi konstruksi terintegrasi yang mengedepankan standar engineering tinggi. Kami percaya bahwa setiap detail dalam pembangunan adalah fondasi bagi kepercayaan klien.') }}
                        </p>
                    </div>
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <span class="material-symbols-outlined text-primary">verified</span>
                            <div>
                                <h4 class="font-headline font-extrabold text-sm text-on-background uppercase tracking-tight">Visi Terukur</h4>
                                <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">{{ setting('home', 'home_vision', 'Menjadi standar utama dalam industri EPC nasional yang diakui atas kualitas dan keselamatan.') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <span class="material-symbols-outlined text-primary">engineering</span>
                            <div>
                                <h4 class="font-headline font-extrabold text-sm text-on-background uppercase tracking-tight">Misi Eksekusi</h4>
                                <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">{{ setting('home', 'home_mission', 'Menyelesaikan setiap tantangan teknis dengan solusi yang efisien, aman, dan tepat waktu.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="lg:col-span-5">
                <div class="relative group">
                    <div class="absolute -inset-4 border border-outline-variant translate-x-4 translate-y-4 -z-10 group-hover:translate-x-2 group-hover:translate-y-2 transition-industrial"></div>
                    <div class="aspect-[4/5] bg-surface overflow-hidden border border-outline-variant">
                        {{-- Replace with real workshop/office image --}}
                        @php $aboutImg = setting('about', 'about_hero_image'); @endphp
                        <img src="{{ $aboutImg ? asset('storage/' . $aboutImg) : asset('dap.png') }}" 
                             class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-industrial duration-700" 
                             alt="Workshop DAP">
                        
                        <div class="absolute bottom-0 left-0 right-0 p-8 bg-gradient-to-t from-surface to-transparent">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-px bg-primary"></div>
                                <div class="text-[10px] font-bold text-white uppercase tracking-[0.2em] leading-tight">
                                    Established Facility<br>Depok, Indonesia
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
>

{{-- =========================================================
     SERVICES SECTION — Industrial Capabilities
     ========================================================= --}}
<section class="py-32 px-8 bg-surface border-y border-white/5" id="services">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-end mb-20 gap-8">
        <div class="max-w-2xl">
            <span class="text-primary font-headline font-extrabold text-[11px] uppercase tracking-[0.4em]">Solusi & Kapabilitas</span>
            <h2 class="text-4xl md:text-5xl font-headline font-extrabold text-white mt-4 uppercase tracking-tighter">Layanan Engineering</h2>
        </div>
        <p class="text-on-surface-variant max-w-sm text-sm leading-relaxed border-l border-primary pl-6">
            Menyediakan keahlian teknis menyeluruh mulai dari perencanaan hingga pemeliharaan infrastruktur industri.
        </p>
    </div>

    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 border-t border-l border-white/10">
        @forelse($services as $service)
        <div class="group bg-surface p-10 border-r border-b border-white/10 hover:bg-white/5 transition-industrial relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-industrial">
                <span class="text-4xl font-headline font-black text-white">0{{ $loop->iteration }}</span>
            </div>
            
            <div class="w-14 h-14 bg-white/5 flex items-center justify-center mb-10 border border-white/10 group-hover:border-primary transition-industrial">
                <span class="material-symbols-outlined text-primary text-3xl transition-industrial">{{ $service->icon }}</span>
            </div>
            
            <h3 class="text-xl font-headline font-extrabold text-white mb-4 uppercase tracking-tight group-hover:text-primary transition-industrial">{{ $service->name }}</h3>
            <p class="text-on-surface-variant text-sm leading-relaxed">{{ $service->description }}</p>
            
            <div class="mt-8 pt-6 border-t border-white/5 opacity-0 group-hover:opacity-100 transition-industrial">
                <a href="{{ $service->slug ? route('services.show', $service->slug) : route('services') }}" class="text-[10px] font-bold text-primary uppercase tracking-widest flex items-center gap-2">
                    Detail Teknis <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-4 text-center py-20 border-r border-b border-white/10">
            <span class="material-symbols-outlined text-6xl block mb-4 opacity-10 text-white">engineering</span>
            <p class="text-on-surface-variant font-bold uppercase tracking-widest text-xs">Informasi Kapabilitas Sedang Diperbarui</p>
        </div>
        @endforelse
    </div>
</section>

{{-- =========================================================
     TRUST & COMPLIANCE — Safety First
     ========================================================= --}}
<section class="py-32 px-8 bg-background border-b border-outline-variant" id="trust">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
            <div class="lg:col-span-5 space-y-8">
                <div class="space-y-4">
                    <span class="text-primary font-headline font-extrabold text-[11px] uppercase tracking-[0.4em]">Standar Operasional</span>
                    <h2 class="text-4xl md:text-5xl font-headline font-extrabold text-on-background leading-[0.95] tracking-tighter uppercase">
                        Keamanan & Mutu Tanpa Kompromi.
                    </h2>
                </div>
                <p class="text-on-background/70 text-lg leading-relaxed">
                    Setiap proyek dikelola dengan sistem kontrol yang ketat, memastikan kepatuhan terhadap standar K3 dan spesifikasi teknis yang berlaku.
                </p>
                <div class="pt-4">
                    <a href="{{ route('about') }}" class="inline-flex items-center gap-4 group">
                        <div class="w-12 h-12 bg-on-background flex items-center justify-center text-background group-hover:bg-primary transition-industrial">
                            <span class="material-symbols-outlined">description</span>
                        </div>
                        <span class="font-headline font-extrabold text-xs uppercase tracking-widest text-on-background group-hover:text-primary transition-industrial">Lihat Sertifikasi & Profil</span>
                    </a>
                </div>
            </div>
            
            <div class="lg:col-span-7 grid grid-cols-1 md:grid-cols-2 gap-px bg-outline-variant border border-outline-variant">
                <div class="bg-white p-10 space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined">health_and_safety</span>
                        </div>
                        <h3 class="font-headline font-extrabold text-lg text-on-background uppercase tracking-tight">Budaya K3</h3>
                    </div>
                    <p class="text-sm text-on-surface-variant leading-relaxed">Implementasi sistem manajemen keselamatan kerja yang ketat untuk mencapai target *zero accident* di setiap area proyek.</p>
                </div>
                <div class="bg-white p-10 space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined">verified_user</span>
                        </div>
                        <h3 class="font-headline font-extrabold text-lg text-on-background uppercase tracking-tight">QA/QC Kontrol</h3>
                    </div>
                    <p class="text-sm text-on-surface-variant leading-relaxed">Pengawasan berlapis pada setiap fase konstruksi untuk menjamin hasil kerja sesuai dengan standar kualitas yang telah ditetapkan.</p>
                </div>
                <div class="bg-white p-10 space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined">architecture</span>
                        </div>
                        <h3 class="font-headline font-extrabold text-lg text-on-background uppercase tracking-tight">Metode Tepat</h3>
                    </div>
                    <p class="text-sm text-on-surface-variant leading-relaxed">Penggunaan metode kerja yang telah divalidasi secara teknis untuk memastikan efisiensi biaya dan ketepatan waktu eksekusi.</p>
                </div>
                <div class="bg-white p-10 space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined">precision_manufacturing</span>
                        </div>
                        <h3 class="font-headline font-extrabold text-lg text-on-background uppercase tracking-tight">Fasilitas Workshop</h3>
                    </div>
                    <p class="text-sm text-on-surface-variant leading-relaxed">Dukungan fasilitas fabrikasi internal untuk kontrol penuh pada kualitas material dan komponen sebelum instalasi di lapangan.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
     PROJECTS SECTION — Portfolio Log
     ========================================================= --}}
<section class="py-32 px-8 bg-surface" id="projects">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-end mb-16 gap-8">
        <div class="max-w-2xl">
            <span class="text-primary font-headline font-extrabold text-[11px] uppercase tracking-[0.4em]">Dokumentasi Proyek</span>
            <h2 class="text-4xl md:text-5xl font-headline font-extrabold text-white mt-4 uppercase tracking-tighter">Rekam Jejak Engineering</h2>
        </div>
        <a href="{{ route('projects') }}"
           class="font-headline font-extrabold text-[11px] uppercase tracking-[0.2em] text-white border-b-2 border-primary pb-2 hover:text-primary transition-industrial">
            Lihat Semua Proyek &rarr;
        </a>
    </div>

    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
        @forelse($projects as $project)
        <a href="{{ $project->slug ? route('projects.show', $project->slug) : route('projects') }}" class="group block relative bg-surface-container border border-white/5 overflow-hidden transition-industrial">
            {{-- Technical Image Container --}}
            <div class="aspect-video overflow-hidden border-b border-white/5 relative">
                @if($project->image)
                    <img src="{{ asset('storage/' . $project->image) }}"
                         alt="{{ $project->title }}"
                         loading="lazy" decoding="async"
                         class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-industrial duration-700">
                @else
                    <div class="w-full h-full bg-surface-container flex items-center justify-center">
                        <span class="material-symbols-outlined text-white/10 text-6xl">construction</span>
                    </div>
                @endif
                
                {{-- Project ID / Category Badge --}}
                <div class="absolute top-0 right-0 p-4">
                    <span class="bg-primary text-white text-[9px] font-bold px-3 py-1 uppercase tracking-widest">
                        {{ $project->category }}
                    </span>
                </div>
            </div>

            <div class="p-8 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-px bg-primary"></div>
                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">{{ $project->client_name ?: 'Industrial Client' }}</span>
                </div>
                
                <h3 class="text-xl font-headline font-extrabold text-white uppercase tracking-tight leading-tight group-hover:text-primary transition-industrial">
                    {{ $project->title }}
                </h3>
                
                <div class="flex items-center gap-6 pt-4 border-t border-white/5">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm text-primary">location_on</span>
                        <span class="text-[10px] font-bold text-white/50 uppercase tracking-wide">{{ $project->location ?: 'Indonesia' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm text-primary">calendar_today</span>
                        <span class="text-[10px] font-bold text-white/50 uppercase tracking-wide">{{ $project->year ?: '2024' }}</span>
                    </div>
                </div>
            </div>
            
            {{-- Technical Overlay on Hover --}}
            <div class="absolute inset-0 border-2 border-primary opacity-0 group-hover:opacity-100 transition-industrial pointer-events-none"></div>
        </a>
        @empty
        <div class="col-span-3 text-center py-24 border border-white/5 bg-white/5">
            <span class="material-symbols-outlined text-5xl mb-4 block opacity-10 text-white">inventory</span>
            <p class="text-on-surface-variant font-bold uppercase tracking-widest text-[10px]">Basis Data Proyek Belum Tersedia</p>
        </div>
        @endforelse
    </div>
{{-- =========================================================
     CLIENTS & PARTNERS — Professional Grayscale
     ========================================================= --}}
<section class="py-24 bg-background border-y border-outline-variant overflow-hidden">
    <div class="max-w-7xl mx-auto px-8 mb-16">
        <div class="flex items-center gap-6">
            <div class="h-px bg-outline-variant flex-grow"></div>
            <p class="text-on-surface-variant font-headline font-extrabold uppercase tracking-[0.4em] text-[10px] whitespace-nowrap">Trusted by Industrial Leaders</p>
            <div class="h-px bg-outline-variant flex-grow"></div>
        </div>
    </div>
    
    <div class="relative overflow-hidden">
        <div class="flex space-x-24 animate-[scroll_40s_linear_infinite] whitespace-nowrap w-max px-12">
            @php 
                $clientList = $clients->count() ? $clients : collect([['name'=>'PERTAMINA'],['name'=>'WIKA'],['name'=>'ADHI KARYA'],['name'=>'PLN'],['name'=>'PUPR'],['name'=>'TELKOM'],['name'=>'FREEPORT'],['name'=>'ANTAM']]); 
            @endphp
            @foreach([$clientList, $clientList] as $list)
                @foreach($list as $client)
                    @if(is_array($client))
                        <span class="text-3xl font-black font-headline text-on-surface-variant/20 tracking-tighter uppercase inline-block">{{ $client['name'] }}</span>
                    @else
                        @if($client->logo)
                            <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->name }}" class="h-12 w-auto object-contain grayscale opacity-30 hover:opacity-100 hover:grayscale-0 transition-industrial inline-block">
                        @else
                            <span class="text-3xl font-black font-headline text-on-surface-variant/20 tracking-tighter uppercase inline-block">{{ $client->name }}</span>
                        @endif
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>
</section>

{{-- =========================================================
     CONTACT SECTION — Direct & Operational
     ========================================================= --}}
<section class="py-32 px-8 bg-surface border-b border-white/5" id="contact">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-24 items-start">
        <div class="lg:col-span-5 space-y-12">
            <div class="space-y-6">
                <span class="text-primary font-headline font-extrabold text-[11px] uppercase tracking-[0.4em]">Hubungan Kemitraan</span>
                <h2 class="text-4xl md:text-6xl font-headline font-extrabold text-white leading-[0.95] tracking-tighter uppercase">Mulai Kolaborasi Teknis.</h2>
                <p class="text-on-surface-variant text-lg leading-relaxed">Ajukan kebutuhan proyek Anda kepada tim engineering kami untuk mendapatkan solusi teknis dan estimasi yang akurat.</p>
            </div>
            
            <div class="grid grid-cols-1 gap-8 pt-8">
                <div class="flex items-start gap-6 group">
                    <div class="w-12 h-12 bg-white/5 border border-white/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-industrial">
                        <span class="material-symbols-outlined">location_on</span>
                    </div>
                    <div>
                        <h4 class="font-headline font-extrabold text-xs text-white uppercase tracking-widest mb-1">Kantor Pusat</h4>
                        <p class="text-on-surface-variant text-sm leading-relaxed max-w-xs">{{ setting('contact', 'contact_address', 'Jakarta, Indonesia') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-6 group">
                    <div class="w-12 h-12 bg-white/5 border border-white/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-industrial">
                        <span class="material-symbols-outlined">mail</span>
                    </div>
                    <div>
                        <h4 class="font-headline font-extrabold text-xs text-white uppercase tracking-widest mb-1">Email Korespondensi</h4>
                        <p class="text-on-surface-variant text-sm leading-relaxed">{{ setting('contact', 'contact_email', 'info@moduvox.id') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-6 group">
                    <div class="w-12 h-12 bg-white/5 border border-white/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-industrial">
                        <span class="material-symbols-outlined">call</span>
                    </div>
                    <div>
                        <h4 class="font-headline font-extrabold text-xs text-white uppercase tracking-widest mb-1">Layanan Telepon</h4>
                        <p class="text-on-surface-variant text-sm leading-relaxed">{{ setting('contact', 'contact_phone', '+62 (21) 555-0123') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Form — Technical Style --}}
        <div class="lg:col-span-7 bg-white/5 border border-white/10 p-10 md:p-16 relative">
            <div class="absolute top-0 right-0 p-4 border-b border-l border-white/10">
                <span class="text-[9px] font-bold text-white/30 uppercase tracking-[0.2em]">FORM-DAP-01</span>
            </div>

            @if(session('success'))
                <div class="mb-10 p-5 bg-primary/10 border-l-4 border-primary text-primary text-sm font-bold flex items-center gap-4">
                    <span class="material-symbols-outlined">verified</span>
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('contact.store') }}" method="POST" class="space-y-8">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <label class="text-[10px] font-extrabold text-white/40 uppercase tracking-[0.2em]" for="name">Nama Penanggung Jawab</label>
                        <input class="w-full bg-transparent border-b border-white/20 focus:border-primary text-white transition-industrial py-3 outline-none @error('name') border-primary @enderror"
                               id="name" name="name" placeholder="Full Name" type="text" value="{{ old('name') }}"/>
                        @error('name')<p class="text-primary text-[10px] mt-1 font-bold">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] font-extrabold text-white/40 uppercase tracking-[0.2em]" for="email">Email Perusahaan</label>
                        <input class="w-full bg-transparent border-b border-white/20 focus:border-primary text-white transition-industrial py-3 outline-none @error('email') border-primary @enderror"
                               id="email" name="email" placeholder="corporate@mail.com" type="email" value="{{ old('email') }}"/>
                        @error('email')<p class="text-primary text-[10px] mt-1 font-bold">{{ $message }}</p>@enderror
                    </div>
                </div>
                
                <div class="space-y-3">
                    <label class="text-[10px] font-extrabold text-white/40 uppercase tracking-[0.2em]" for="subject">Kategori Pekerjaan</label>
                    <select class="w-full bg-transparent border-b border-white/20 focus:border-primary text-white/70 transition-industrial py-3 outline-none appearance-none" id="subject" name="subject">
                        @foreach($services as $service)
                            <option value="{{ $service->name }}" class="bg-surface">{{ $service->name }}</option>
                        @endforeach
                        <option value="Lainnya" class="bg-surface">General / Engineering Support</option>
                    </select>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-extrabold text-white/40 uppercase tracking-[0.2em]" for="message">Ringkasan Kebutuhan Proyek</label>
                    <textarea class="w-full bg-transparent border-b border-white/20 focus:border-primary text-white transition-industrial py-3 outline-none @error('message') border-primary @enderror"
                              id="message" name="message" placeholder="Describe the scope of work..." rows="4">{{ old('message') }}</textarea>
                    @error('message')<p class="text-primary text-[10px] mt-1 font-bold">{{ $message }}</p>@enderror
                </div>

                <button class="w-full py-5 bg-primary text-white font-headline font-extrabold text-xs uppercase tracking-[0.3em] hover:bg-primary-dark transition-industrial shadow-2xl shadow-primary/20" type="submit">
                    Kirim Permintaan Konsultasi
                </button>
            </form>
        </div>
    </div>
</section>

@endsection

