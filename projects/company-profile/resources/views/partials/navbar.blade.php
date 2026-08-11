<nav x-data="{ open: false }" class="fixed top-0 w-full z-50 bg-[#0F172A] border-b border-white/10 transition-industrial" id="navbar">
    <div class="flex justify-between items-center px-6 md:px-8 py-5 max-w-7xl mx-auto">
        <a href="{{ route('home') }}" class="flex items-center gap-3 group">
            <div class="bg-white p-1.5 rounded-sm group-hover:scale-105 transition-industrial">
                <img src="{{ asset('dap.png') }}" alt="PT Moduvox Tech ID" class="h-8 w-auto" loading="eager" decoding="async">
            </div>
            <div class="flex flex-col">
                <span class="text-lg font-extrabold tracking-tighter text-white font-headline leading-none">PT Moduvox Tech ID</span>
                <span class="text-[9px] font-bold text-primary tracking-[0.3em] mt-1 leading-none">ENGINEERING & EPC</span>
            </div>
        </a>

        <div class="hidden md:flex items-center gap-10">
            @php
                $links = [
                    ['route' => 'home', 'label' => 'Beranda', 'active' => request()->routeIs('home')],
                    ['route' => 'about', 'label' => 'Profil', 'active' => request()->routeIs('about')],
                    ['route' => 'services', 'label' => 'Layanan', 'active' => request()->routeIs('services')],
                    ['route' => 'projects', 'label' => 'Portofolio', 'active' => request()->routeIs('projects*')],
                    ['route' => 'career', 'label' => 'Karir', 'active' => request()->routeIs('career*')],
                    ['route' => 'contact', 'label' => 'Kontak', 'active' => request()->routeIs('contact')],
                ];
            @endphp
            
            @foreach($links as $link)
                <a href="{{ route($link['route']) }}" 
                   class="font-headline font-extrabold tracking-wider text-[11px] uppercase transition-industrial {{ $link['active'] ? 'text-primary' : 'text-white/70 hover:text-white' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-4">
            <a href="{{ route('contact') }}"
               class="hidden lg:inline-flex bg-primary hover:bg-primary-dark text-white px-8 py-3 rounded-none font-headline font-extrabold text-[11px] uppercase tracking-widest transition-industrial shadow-lg shadow-primary/20">
                Hubungi Kami
            </a>
            <button type="button" class="md:hidden inline-flex items-center justify-center w-12 h-12 bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-industrial"
                    aria-label="Toggle menu" @click="open = !open">
                <span class="material-symbols-outlined" x-text="open ? 'close' : 'menu'">menu</span>
            </button>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-cloak @click.outside="open = false"
         class="md:hidden bg-surface border-t border-white/10 shadow-2xl">
        <div class="px-6 py-8 flex flex-col gap-6">
            @foreach($links as $link)
                <a href="{{ route($link['route']) }}" 
                   class="font-headline font-extrabold text-xs uppercase tracking-widest {{ $link['active'] ? 'text-primary' : 'text-white/60' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
            <a href="{{ route('contact') }}" class="mt-4 inline-flex justify-center bg-primary text-white px-6 py-4 rounded-none font-headline font-extrabold text-xs uppercase tracking-widest">
                Hubungi Kami
            </a>
        </div>
    </div>
</nav>

