<footer class="bg-[#0F172A] w-full py-24 px-8 mt-32 border-t border-white/5">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-16 max-w-7xl mx-auto">
        {{-- Branding & About --}}
        <div class="md:col-span-4 space-y-8">
            <div class="flex items-center gap-4">
                <div class="bg-white p-1.5 rounded-none">
                    <img src="{{ asset('dap.png') }}" alt="PT Moduvox Tech ID" class="h-8 w-auto" loading="lazy" decoding="async">
                </div>
                <div class="flex flex-col">
                    <span class="text-lg font-extrabold text-white font-headline leading-none uppercase tracking-tighter">PT Moduvox Tech ID</span>
                    <span class="text-[9px] font-bold text-primary tracking-[0.3em] mt-1 uppercase leading-none">Engineering & EPC</span>
                </div>
            </div>
            <p class="text-sm leading-relaxed text-on-surface-variant max-w-sm">
                Penyedia solusi konstruksi infrastruktur nasional dengan spesialisasi pada akurasi teknis, manajemen proyek yang disiplin, dan standar keselamatan kerja tinggi.
            </p>
            <div class="flex gap-4">
                @foreach($socials as $social)
                    <a href="{{ $social->url }}" target="_blank" class="w-10 h-10 bg-white/5 flex items-center justify-center text-white/50 hover:bg-primary hover:text-white transition-industrial">
                        <span class="material-symbols-outlined text-lg">{{ $social->icon }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Navigasi Cepat --}}
        <div class="md:col-span-2">
            <h4 class="font-headline font-extrabold text-white mb-8 text-[11px] uppercase tracking-[0.3em]">Quick Links</h4>
            <ul class="space-y-4">
                <li><a class="text-xs font-bold text-on-surface-variant hover:text-primary transition-industrial uppercase tracking-widest" href="{{ route('home') }}">Beranda</a></li>
                <li><a class="text-xs font-bold text-on-surface-variant hover:text-primary transition-industrial uppercase tracking-widest" href="{{ route('about') }}">Profil</a></li>
                <li><a class="text-xs font-bold text-on-surface-variant hover:text-primary transition-industrial uppercase tracking-widest" href="{{ route('services') }}">Layanan</a></li>
                <li><a class="text-xs font-bold text-on-surface-variant hover:text-primary transition-industrial uppercase tracking-widest" href="{{ route('projects') }}">Portofolio</a></li>
                <li><a class="text-xs font-bold text-on-surface-variant hover:text-primary transition-industrial uppercase tracking-widest" href="{{ route('contact') }}">Kontak</a></li>
            </ul>
        </div>

        {{-- Alamat & Kontak --}}
        <div class="md:col-span-6 space-y-8">
            <h4 class="font-headline font-extrabold text-white mb-8 text-[11px] uppercase tracking-[0.3em]">Operational Contact</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div class="flex gap-4 group">
                        <span class="material-symbols-outlined text-primary text-lg">location_on</span>
                        <p class="text-xs text-on-surface-variant leading-relaxed group-hover:text-white transition-industrial">
                            {{ setting('contact', 'contact_address', 'Jakarta, Indonesia') }}
                        </p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="flex gap-4 group">
                        <span class="material-symbols-outlined text-primary text-lg">call</span>
                        <p class="text-xs text-on-surface-variant group-hover:text-white transition-industrial">
                            {{ setting('contact', 'contact_phone', '+62 (21) 555-0123') }}
                        </p>
                    </div>
                    <div class="flex gap-4 group">
                        <span class="material-symbols-outlined text-primary text-lg">mail</span>
                        <p class="text-xs text-on-surface-variant group-hover:text-white transition-industrial">
                            {{ setting('contact', 'contact_email', 'info@moduvox.id') }}
                        </p>
                    </div>
                </div>
            </div>
            
            {{-- Certificates --}}
            <div class="pt-8 border-t border-white/5 flex items-center gap-10 opacity-30 grayscale hover:opacity-60 transition-industrial">
                <div class="text-[10px] font-black text-white border-2 border-white px-2 py-1 tracking-tighter">ISO 9001</div>
                <div class="text-[10px] font-black text-white border-2 border-white px-2 py-1 tracking-tighter">ISO 45001</div>
                <div class="text-[10px] font-black text-white border-2 border-white px-2 py-1 tracking-tighter">K3 TERVERIFIKASI</div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto pt-10 mt-20 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
        <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-[0.2em]">
            © {{ date('Y') }} PT Moduvox Tech ID. ALL RIGHTS RESERVED.
        </p>
        <div class="flex gap-8">
            <span class="text-[9px] text-white/20 font-black uppercase tracking-[0.4em]">Precision</span>
            <span class="text-[9px] text-white/20 font-black uppercase tracking-[0.4em]">Integritas</span>
            <span class="text-[9px] text-white/20 font-black uppercase tracking-[0.4em]">Execution</span>
        </div>
    </div>
</footer>

