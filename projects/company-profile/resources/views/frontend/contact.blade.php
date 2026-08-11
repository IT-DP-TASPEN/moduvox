@extends('layouts.app')

@section('title', 'Hubungi Kami | PT Moduvox Tech ID')
@section('meta_description', 'Hubungi PT Moduvox Tech ID untuk konsultasi proyek konstruksi, infrastruktur, dan engineering. Tim kami siap memberikan solusi teknis terbaik.')

@section('content')
{{-- =========================================================
     CONTACT HERO — Industrial Hub
     ========================================================= --}}
<section class="relative min-h-[40vh] flex items-center justify-start bg-surface overflow-hidden pt-20">
    <div class="absolute inset-0 industrial-grid opacity-10"></div>
    <div class="relative z-10 w-full max-w-7xl mx-auto px-8 py-20">
        <div class="max-w-3xl space-y-6">
            <div class="inline-flex items-center gap-3 px-4 py-1.5 bg-primary/10 border-l-4 border-primary">
                <span class="text-primary font-headline font-extrabold text-[10px] uppercase tracking-[0.3em]">Saluran Komunikasi</span>
            </div>
            <h1 class="text-4xl md:text-7xl font-headline font-extrabold text-white leading-[0.9] tracking-tighter uppercase">
                Hubungi Tim <br><span class="text-primary">Engineering</span>
            </h1>
            <p class="text-white/50 text-lg md:text-xl font-medium max-w-xl border-l border-white/20 pl-6">
                Kami siap merespons kebutuhan teknis dan operasional proyek strategis Anda dengan standar profesional tinggi.
            </p>
        </div>
    </div>
</section>

{{-- =========================================================
     CONTACT CHANNELS — High Contrast
     ========================================================= --}}
<section class="py-12 bg-background relative z-20">
    <div class="max-w-7xl mx-auto px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-px bg-outline-variant border border-outline-variant -mt-24 shadow-2xl">
            {{-- Email --}}
            <div class="bg-white p-10 space-y-6 group hover:bg-surface transition-industrial">
                <span class="material-symbols-outlined text-primary text-4xl group-hover:text-white transition-industrial">mail</span>
                <div>
                    <h4 class="text-[10px] font-black text-on-surface-variant uppercase tracking-widest mb-1 group-hover:text-white/40">Email Korespondensi</h4>
                    <p class="text-sm font-black text-on-background uppercase tracking-tight group-hover:text-white truncate">{{ setting('contact', 'contact_email', 'info@moduvox.id') }}</p>
                </div>
            </div>
            {{-- Phone --}}
            <div class="bg-white p-10 space-y-6 group hover:bg-surface transition-industrial">
                <span class="material-symbols-outlined text-primary text-4xl group-hover:text-white transition-industrial">call</span>
                <div>
                    <h4 class="text-[10px] font-black text-on-surface-variant uppercase tracking-widest mb-1 group-hover:text-white/40">Layanan Telepon</h4>
                    <p class="text-sm font-black text-on-background uppercase tracking-tight group-hover:text-white">{{ setting('contact', 'contact_phone', '(021) 555-0123') }}</p>
                </div>
            </div>
            {{-- WhatsApp --}}
            <div class="bg-white p-10 space-y-6 group hover:bg-surface transition-industrial">
                <span class="material-symbols-outlined text-green-600 text-4xl group-hover:text-white transition-industrial">chat_bubble</span>
                <div>
                    <h4 class="text-[10px] font-black text-on-surface-variant uppercase tracking-widest mb-1 group-hover:text-white/40">Respon Cepat</h4>
                    <p class="text-sm font-black text-green-600 uppercase tracking-tight group-hover:text-white italic">Tanya via WhatsApp &rarr;</p>
                </div>
            </div>
            {{-- Office --}}
            <div class="bg-white p-10 space-y-6 group hover:bg-surface transition-industrial">
                <span class="material-symbols-outlined text-primary text-4xl group-hover:text-white transition-industrial">location_on</span>
                <div>
                    <h4 class="text-[10px] font-black text-on-surface-variant uppercase tracking-widest mb-1 group-hover:text-white/40">Lokasi Operasional</h4>
                    <p class="text-[11px] font-black text-on-background uppercase tracking-tight group-hover:text-white leading-tight">Depok, Indonesia</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
     CONSULTATION FORM — Industrial Style
     ========================================================= --}}
<section class="py-24 bg-background">
    <div class="max-w-7xl mx-auto px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-20 items-start">
            {{-- Text Info --}}
            <div class="lg:col-span-5 space-y-12">
                <div class="space-y-6">
                    <span class="text-primary font-headline font-extrabold text-[11px] uppercase tracking-[0.4em]">Form-DAP-01</span>
                    <h2 class="text-4xl md:text-6xl font-headline font-extrabold text-on-background leading-[0.95] tracking-tighter uppercase">Mulai Kolaborasi.</h2>
                    <p class="text-on-surface-variant text-lg leading-relaxed max-w-md">Ajukan spesifikasi proyek Anda untuk mendapatkan solusi engineering dan estimasi biaya yang presisi.</p>
                </div>
                
                <div class="space-y-8 pt-8 border-t border-outline-variant/30">
                    <div class="flex gap-6 group">
                        <div class="w-12 h-12 bg-background border border-outline-variant flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-industrial">
                            <span class="material-symbols-outlined">description</span>
                        </div>
                        <div>
                            <h4 class="text-[11px] font-black text-on-background uppercase tracking-widest mb-1">Evaluasi Teknis</h4>
                            <p class="text-on-surface-variant text-xs leading-relaxed">Tim ahli kami akan meninjau setiap detail permintaan Anda secara menyeluruh.</p>
                        </div>
                    </div>
                    <div class="flex gap-6 group">
                        <div class="w-12 h-12 bg-background border border-outline-variant flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-industrial">
                            <span class="material-symbols-outlined">verified</span>
                        </div>
                        <div>
                            <h4 class="text-[11px] font-black text-on-background uppercase tracking-widest mb-1">Standar Kualitas</h4>
                            <p class="text-on-surface-variant text-xs leading-relaxed">Seluruh proses layanan kami mengacu pada standar mutu ISO dan regulasi K3.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Technical Form --}}
            <div class="lg:col-span-7 bg-surface p-10 md:p-16 border border-white/5 relative shadow-2xl">
                <div class="absolute top-0 right-0 p-4 border-b border-l border-white/10">
                    <span class="text-[9px] font-bold text-white/30 uppercase tracking-[0.2em]">INQUIRY SYSTEM</span>
                </div>

                @if(session('success'))
                    <div class="mb-10 p-5 bg-primary/10 border-l-4 border-primary text-primary text-xs font-black uppercase tracking-widest flex items-center gap-4">
                        <span class="material-symbols-outlined">check_circle</span>
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('contact.store') }}" method="POST" class="space-y-10">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-[0.3em] text-white/40">Penanggung Jawab</label>
                            <input type="text" name="name" required value="{{ old('name') }}"
                                   class="w-full bg-transparent border-b border-white/20 focus:border-primary text-white py-3 outline-none transition-industrial @error('name') border-primary @enderror"
                                   placeholder="NAMA LENGKAP">
                            @error('name')<p class="text-primary text-[9px] font-bold mt-1 uppercase">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-[0.3em] text-white/40">Email Perusahaan</label>
                            <input type="email" name="email" required value="{{ old('email') }}"
                                   class="w-full bg-transparent border-b border-white/20 focus:border-primary text-white py-3 outline-none transition-industrial @error('email') border-primary @enderror"
                                   placeholder="CORP@MAIL.COM">
                            @error('email')<p class="text-primary text-[9px] font-bold mt-1 uppercase">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.3em] text-white/40">Kategori Pekerjaan</label>
                        <select name="subject" class="w-full bg-transparent border-b border-white/20 focus:border-primary text-white/60 py-3 outline-none transition-industrial appearance-none uppercase">
                            @foreach($services as $service)
                                <option value="{{ $service->name }}" class="bg-surface uppercase">{{ strtoupper($service->name) }}</option>
                            @endforeach
                            <option value="Lainnya" class="bg-surface uppercase">GENERAL / ENGINEERING SUPPORT</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.3em] text-white/40">Deskripsi Kebutuhan Teknis</label>
                        <textarea name="message" rows="5" required
                                  class="w-full bg-transparent border-b border-white/20 focus:border-primary text-white py-3 outline-none transition-industrial @error('message') border-primary @enderror"
                                  placeholder="JELASKAN CAKUPAN PEKERJAAN ANDA...">{{ old('message') }}</textarea>
                        @error('message')<p class="text-primary text-[9px] font-bold mt-1 uppercase">{{ $message }}</p>@enderror
                    </div>
                    
                    <button type="submit" class="w-full py-6 bg-primary text-white font-headline font-extrabold text-xs uppercase tracking-[0.4em] hover:bg-primary-dark transition-industrial shadow-2xl shadow-primary/20 group">
                        Kirim Permintaan Konsultasi <span class="material-symbols-outlined ml-4 text-sm group-hover:translate-x-2 transition-industrial">send</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
     FACILITY & LOGISTICS
     ========================================================= --}}
<section class="py-24 bg-white border-y border-outline-variant">
    <div class="max-w-7xl mx-auto px-8">
        <div class="max-w-2xl mb-16 space-y-4">
            <span class="text-primary font-headline font-extrabold text-[11px] uppercase tracking-[0.4em]">Infrastruktur Pendukung</span>
            <h2 class="text-4xl font-headline font-extrabold text-on-background leading-none tracking-tighter uppercase">Fasilitas & Workshop</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @php 
                $facImages = json_decode(setting('contact', 'contact_facility_images', '[]'), true);
            @endphp
            @forelse($facImages as $img)
                <div class="aspect-video bg-background border border-outline-variant overflow-hidden group relative">
                    <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-industrial duration-1000" alt="Operational Facility">
                    <div class="absolute inset-0 bg-surface/60 opacity-0 group-hover:opacity-100 transition-industrial flex items-end p-6">
                        <span class="text-white text-[9px] font-black uppercase tracking-widest border-l-2 border-primary pl-4">Aset Internal PT Moduvox Tech ID</span>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-32 text-center border-2 border-dashed border-outline-variant flex flex-col items-center gap-6">
                    <span class="material-symbols-outlined text-6xl text-primary opacity-10">photo_library</span>
                    <p class="text-[10px] font-black text-on-surface-variant uppercase tracking-widest">Dokumentasi Fasilitas Sedang Diperbarui</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- =========================================================
     MAP — Operational Location
     ========================================================= --}}
@php $mapEmbed = setting('contact', 'contact_map'); @endphp
@if($mapEmbed)
<section class="py-24 bg-background overflow-hidden">
    <div class="max-w-7xl mx-auto px-8">
        <div class="relative border-8 border-surface shadow-2xl h-[600px] w-full overflow-hidden grayscale contrast-125 brightness-50 hover:grayscale-0 hover:contrast-100 hover:brightness-100 transition-industrial duration-1000">
            {!! str_replace(['width="600"', 'height="450"', 'style="border:0;"'], ['width="100%"', 'height="100%"', 'style="border:0; display:block;"'], $mapEmbed) !!}
            <div class="absolute bottom-10 left-10 z-10 hidden md:block">
                <div class="bg-surface text-white p-10 border-l-4 border-primary shadow-2xl">
                    <h3 class="font-headline font-extrabold text-xl uppercase tracking-tighter mb-2">Pusat Operasi</h3>
                    <p class="text-white/40 text-[10px] font-bold uppercase tracking-widest leading-relaxed">PT Moduvox Tech ID <br> KOTA DEPOK, JAWA BARAT</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@endsection

