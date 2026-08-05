@props(['title', 'subtitle', 'user', 'role'])

@php
    $businessDate = config('app.business_date');
    $timezone = config('app.timezone');
    $systemTime = (new \DateTimeImmutable('now', new \DateTimeZone($timezone)))->format('d/m/Y H:i');
@endphp

<header class="bg-white sticky top-0 z-40 border-b border-surface-dim px-8 py-4 flex justify-between items-center gap-6 h-20 shadow-sm">
    <div class="min-w-0">
        <h2 class="text-xl font-headline font-bold text-slate-900">{{ $title }}</h2>
        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest opacity-70">{{ $subtitle }}</p>
    </div>
    
    <div class="flex items-center justify-end gap-5 min-w-0">
        <!-- Optional Actions Slot -->
        @if(isset($actions))
            <div class="flex items-center space-x-4 border-r border-slate-200 pr-5 min-w-0">
                {{ $actions }}
            </div>
        @endif

        <div class="hidden md:flex shrink-0 items-center gap-2 min-w-[168px] px-3 py-2 rounded-2xl border {{ $businessDate ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-slate-50 text-slate-600 border-slate-100' }}">
            <span class="material-symbols-outlined text-lg shrink-0">{{ $businessDate ? 'event_repeat' : 'schedule' }}</span>
            <div class="leading-tight whitespace-nowrap">
                <p class="text-[8px] font-black uppercase tracking-widest">{{ $businessDate ? 'Backdate Aktif' : 'Tanggal Sistem' }}</p>
                <p class="text-[11px] font-black mt-0.5">{{ now()->format('d/m/Y H:i') }}</p>
                @if($businessDate)
                    <p class="text-[8px] font-bold opacity-60 mt-0.5">Server {{ $systemTime }}</p>
                @endif
            </div>
        </div>

        <!-- User Profile Dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center space-x-3 p-1.5 pl-3 rounded-2xl hover:bg-slate-50 transition-all group border border-transparent hover:border-slate-200 active:scale-95">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold text-slate-900 group-hover:text-slate-900 transition-colors leading-none">{{ $user->name }}</p>
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider opacity-60 leading-none mt-1">{{ $role }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white font-bold shadow-lg shadow-slate-900/20 group-hover:shadow-slate-900/40 transition-all">
                    {{ substr($user->name, 0, 1) }}
                </div>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="absolute right-0 mt-3 w-56 bg-white rounded-3xl shadow-2xl border border-surface-dim overflow-hidden py-2 z-50"
                 x-cloak>
                
                <div class="px-5 py-3 border-b border-surface-dim mb-2 bg-surface/30">
                    <p class="text-[10px] font-bold text-outline uppercase tracking-widest">Informasi Akun</p>
                </div>

                <a href="{{ route('profile') }}" class="flex items-center space-x-3 px-5 py-3 text-sm text-primary hover:bg-surface transition-colors font-medium">
                    <span class="material-symbols-outlined text-lg opacity-60">account_circle</span>
                    <span>Profil Saya</span>
                </a>

                @can('system.business-date')
                <a href="{{ route('system.business-date') }}" class="flex items-center space-x-3 px-5 py-3 text-sm text-primary hover:bg-surface transition-colors font-medium">
                    <span class="material-symbols-outlined text-lg opacity-60">event_repeat</span>
                    <span>Tanggal Operasional</span>
                </a>
                @endcan

                <div class="border-t border-surface-dim my-2"></div>

                <button wire:click="logout" class="w-full flex items-center space-x-3 px-5 py-3 text-sm text-error hover:bg-error/5 transition-colors font-bold">
                    <span class="material-symbols-outlined text-lg">logout</span>
                    <span>Keluar Sistem</span>
                </button>
            </div>
        </div>
    </div>
</header>
