@extends('layouts.app')

@section('title', 'Tentang Kami | PT Moduvox Tech ID')
@section('meta_description', 'Profil PT Moduvox Tech ID: perusahaan kontraktor industrial, infrastruktur, dan engineering terpercaya dengan fasilitas kantor & workshop mandiri.')

@section('content')
{{-- =========================================================
     ABOUT HERO — Industrial Identity
     ========================================================= --}}
<section class="relative min-h-[50vh] flex items-center justify-start bg-[#0F172A] overflow-hidden pt-20">
    <div class="absolute inset-0 industrial-grid opacity-10"></div>
    @php
        $heroBg = setting('about', 'about_hero_image') ? asset('storage/' . setting('about', 'about_hero_image')) : null;
    @endphp
    @if($heroBg)
        <img src="{{ $heroBg }}" class="absolute inset-0 w-full h-full object-cover grayscale opacity-20" alt="Operational Background">
    @endif
    
    <div class="relative z-10 w-full max-w-7xl mx-auto px-8 py-20">
        <div class="max-w-3xl space-y-6">
            <div class="inline-flex items-center gap-3 px-4 py-1.5 bg-primary/10 border-l-4 border-primary">
                <span class="text-primary font-headline font-extrabold text-[10px] uppercase tracking-[0.3em]">Identitas Perusahaan</span>
            </div>
            <h1 class="text-4xl md:text-7xl font-headline font-extrabold text-white leading-[0.9] tracking-tighter uppercase">
                {{ setting('about', 'about_hero_title', 'Integritas & Presisi Engineering') }}
            </h1>
            <p class="text-white/50 text-lg md:text-xl font-medium max-w-xl border-l border-white/20 pl-6">
                {{ setting('about', 'about_hero_desc', 'Berdedikasi dalam penyediaan jasa konstruksi infrastruktur dan engineering dengan standar mutu nasional.') }}
            </p>
        </div>
    </div>
</section>

{{-- =========================================================
     CORPORATE STORY — Grounded & Professional
     ========================================================= --}}
<section class="py-24 bg-background">
    <div class="max-w-7xl mx-auto px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-20 items-start">
            <div class="lg:col-span-7 space-y-10">
                <div class="space-y-6">
                    <h2 class="font-headline font-extrabold text-3xl md:text-4xl text-on-background uppercase tracking-tighter border-l-4 border-primary pl-6">
                        {{ setting('about', 'about_story_title', 'Rekam Jejak Operasional') }}
                    </h2>
                    <div class="text-on-background/70 text-lg leading-relaxed whitespace-pre-line prose-industrial">
                        {!! nl2br(setting('about', 'about_story_desc', 'PT Moduvox Tech ID adalah entitas engineering yang berfokus pada eksekusi proyek infrastruktur strategis...')) !!}
                    </div>
                </div>
                
                @if(setting('about', 'about_journey'))
                <div class="p-10 bg-white border border-outline-variant relative overflow-hidden group hover:border-primary transition-industrial">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-industrial">
                        <span class="material-symbols-outlined text-6xl">timeline</span>
                    </div>
                    <h3 class="font-headline font-extrabold text-sm text-on-background uppercase tracking-widest mb-4">Milestone Perusahaan</h3>
                    <p class="text-on-surface-variant text-sm leading-relaxed italic">
                        {{ setting('about', 'about_journey') }}
                    </p>
                </div>
                @endif
            </div>

            <div class="lg:col-span-5 space-y-8 sticky top-32">
                {{-- Vision & Mission — Industrial Style --}}
                <div class="bg-[#0F172A] p-10 border border-white/5 shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 border-b border-l border-white/10">
                        <span class="text-[9px] font-bold text-white/30 uppercase tracking-widest">DAP-VMS</span>
                    </div>
                    
                    <div class="space-y-12 relative z-10">
                        <div class="space-y-4">
                            <span class="text-primary font-headline font-extrabold text-[10px] uppercase tracking-[0.4em]">Visi Kami</span>
                            <p class="text-xl font-headline font-extrabold text-white leading-tight uppercase tracking-tight">
                                "{{ setting('about', 'about_vision', 'Menjadi mitra strategis konstruksi yang handal dan dipercaya di seluruh wilayah Indonesia.') }}"
                            </p>
                        </div>
                        
                        <div class="space-y-6 pt-10 border-t border-white/10">
                            <span class="text-primary font-headline font-extrabold text-[10px] uppercase tracking-[0.4em]">Misi Operasional</span>
                            <ul class="space-y-5">
                                @php
                                    $missionText = setting('about', 'about_mission', "Implementasi standar K3 secara konsisten\nMenjamin kualitas hasil kerja sesuai spesifikasi\nOptimasi efisiensi waktu dan biaya proyek");
                                    $missionLines = array_values(array_filter(array_map('trim', preg_split("/\\r?\\n/", (string) $missionText))));
                                @endphp
                                @foreach($missionLines as $line)
                                <li class="flex gap-4 items-start group">
                                    <span class="material-symbols-outlined text-primary text-xl group-hover:scale-110 transition-industrial">verified</span>
                                    <span class="text-[11px] font-extrabold text-white/70 uppercase tracking-widest leading-relaxed">{{ $line }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
     FACILITIES — Operational Base
     ========================================================= --}}
<section class="py-24 bg-white border-y border-outline-variant">
    <div class="max-w-7xl mx-auto px-8">
        <div class="max-w-2xl mb-16 space-y-4">
            <span class="text-primary font-headline font-extrabold text-[11px] uppercase tracking-[0.4em]">Aset & Infrastruktur</span>
            <h2 class="text-4xl font-headline font-extrabold text-on-background leading-none tracking-tighter uppercase">Basis Operasional</h2>
            <p class="text-on-surface-variant text-sm font-medium">Kami memiliki fasilitas mandiri untuk mendukung mobilitas dan kontrol kualitas proyek secara menyeluruh.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-px bg-outline-variant border border-outline-variant">
            {{-- Office --}}
            <div class="bg-white p-8 space-y-6 group">
                <div class="aspect-video overflow-hidden bg-background border border-outline-variant">
                    <img src="{{ setting('about', 'about_facility_office') ? asset('storage/' . setting('about', 'about_facility_office')) : asset('dap.png') }}" 
                         class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-industrial duration-700" alt="Head Office">
                </div>
                <div>
                    <h4 class="font-headline font-extrabold text-lg text-on-background uppercase tracking-tight mb-2">Kantor Pusat</h4>
                    <p class="text-xs text-on-surface-variant leading-relaxed">Pusat komando administratif dan perencanaan teknis terpadu.</p>
                </div>
            </div>
            {{-- Workshop --}}
            <div class="bg-white p-8 space-y-6 group">
                <div class="aspect-video overflow-hidden bg-background border border-outline-variant">
                    <img src="{{ setting('about', 'about_facility_workshop') ? asset('storage/' . setting('about', 'about_facility_workshop')) : asset('dap.png') }}" 
                         class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-industrial duration-700" alt="Workshop">
                </div>
                <div>
                    <h4 class="font-headline font-extrabold text-lg text-on-background uppercase tracking-tight mb-2">Workshop Fabrikasi</h4>
                    <p class="text-xs text-on-surface-variant leading-relaxed">Fasilitas pendukung fabrikasi komponen dan pemeliharaan alat.</p>
                </div>
            </div>
            {{-- Activity --}}
            <div class="bg-white p-8 space-y-6 group">
                <div class="aspect-video overflow-hidden bg-background border border-outline-variant">
                    <img src="{{ setting('about', 'about_facility_activity') ? asset('storage/' . setting('about', 'about_facility_activity')) : asset('dap.png') }}" 
                         class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-industrial duration-700" alt="Field Activity">
                </div>
                <div>
                    <h4 class="font-headline font-extrabold text-lg text-on-background uppercase tracking-tight mb-2">Aktivitas Lapangan</h4>
                    <p class="text-xs text-on-surface-variant leading-relaxed">Dokumentasi koordinasi tim ahli dalam eksekusi proyek.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
     ORGANIZATION — Management Grid
     ========================================================= --}}
@if(setting('about', 'about_org_structure'))
<section class="py-24 bg-background">
    <div class="max-w-7xl mx-auto px-8">
        <div class="text-center mb-16 space-y-4">
            <span class="text-primary font-headline font-extrabold text-[11px] uppercase tracking-[0.4em]">Manajemen</span>
            <h2 class="text-4xl font-headline font-extrabold text-on-background leading-none tracking-tighter uppercase">Struktur Organisasi</h2>
        </div>
        <div class="max-w-5xl mx-auto p-4 bg-white border border-outline-variant shadow-xl">
            <img src="{{ asset('storage/' . setting('about', 'about_org_structure')) }}" class="w-full h-auto grayscale-[0.2]" alt="Organization Structure">
        </div>
    </div>
</section>
@endif

{{-- =========================================================
     CORE VALUES — Industrial Standards
     ========================================================= --}}
<section class="py-24 bg-[#0F172A] relative overflow-hidden">
    <div class="absolute inset-0 industrial-grid opacity-5"></div>
    <div class="max-w-7xl mx-auto px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
            @foreach([
                ['icon' => 'health_and_safety', 'title' => 'Disiplin K3', 'desc' => 'Nir-kecelakaan adalah target utama di setiap area kerja.'],
                ['icon' => 'verified', 'title' => 'Mutu Terjamin', 'desc' => 'Kontrol kualitas ketat sesuai standar spesifikasi teknik.'],
                ['icon' => 'schedule', 'title' => 'Presisi Waktu', 'desc' => 'Komitmen pada timeline penyelesaian proyek secara akurat.'],
                ['icon' => 'handshake', 'title' => 'Integritas', 'desc' => 'Transparansi operasional dan tanggung jawab profesional.'],
            ] as $v)
            <div class="space-y-6 group">
                <div class="w-16 h-16 bg-white/5 border border-white/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-industrial">
                    <span class="material-symbols-outlined text-3xl">{{ $v['icon'] }}</span>
                </div>
                <h4 class="font-headline font-extrabold text-xl text-white uppercase tracking-tight">{{ $v['title'] }}</h4>
                <p class="text-white/50 text-xs leading-relaxed">{{ $v['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- =========================================================
     FINAL CTA — Collaborative Engineering
     ========================================================= --}}
<section class="py-24 bg-background">
    <div class="max-w-7xl mx-auto px-8 text-center space-y-10">
        <h2 class="font-headline font-extrabold text-3xl md:text-5xl text-on-background uppercase tracking-tighter leading-none">Siap Berkolaborasi Secara Teknis?</h2>
        <a href="{{ route('contact') }}" class="group relative inline-flex px-12 py-6 bg-primary text-white font-headline font-extrabold text-xs uppercase tracking-[0.3em] transition-industrial overflow-hidden shadow-2xl shadow-primary/20">
            <span class="relative z-10 flex items-center gap-4">
                Hubungi Tim Engineering <span class="material-symbols-outlined">arrow_forward</span>
            </span>
            <div class="absolute inset-0 bg-primary-dark translate-y-full group-hover:translate-y-0 transition-industrial duration-500"></div>
        </a>
    </div>
</section>
@endsection

