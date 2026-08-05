{{-- Top Navigation Bar --}}
<header class="h-[72px] bg-white border-b border-gray-200 flex items-center justify-between px-8 transition-all duration-300">
    {{-- Left: Page Title --}}
    <div>
        <h1 class="text-xl font-bold text-gray-800 tracking-tight">@yield('page-title', 'Dashboard')</h1>
    </div>

    {{-- Right: User Info --}}
    <div class="flex items-center gap-6">
        {{-- Notification Bell --}}
        <button class="relative text-gray-400 hover:text-primary-600 transition-colors p-2 rounded-full hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <i class="fa-solid fa-bell text-[1.125rem]"></i>
            <span id="notif-badge" class="absolute top-1.5 right-1.5 bg-danger-500 text-white text-[9px] font-bold w-3.5 h-3.5 rounded-full flex items-center justify-center hidden border-2 border-white">0</span>
        </button>

        {{-- User Dropdown --}}
        <div x-data="{ open: false }" class="relative">
            <div @click="open = !open" @click.away="open = false" class="flex items-center gap-3 cursor-pointer p-1.5 rounded-lg hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100 select-none">
                <div class="w-9 h-9 rounded-full bg-primary-600 text-white flex items-center justify-center text-sm font-semibold shadow-sm">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <div class="hidden sm:block text-right">
                    <p class="text-sm font-semibold text-gray-700 leading-none">{{ auth()->user()->name ?? 'User' }}</p>
                    <p class="text-[11px] text-gray-500 mt-1 leading-none">{{ auth()->user()->roles->first()->name ?? '-' }}</p>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 ml-1 transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </div>
            
            {{-- Dropdown Menu --}}
            <div x-show="open" style="display: none;"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                <div class="px-4 py-3 border-b border-gray-50">
                    <p class="text-sm font-bold text-gray-800 truncate">{{ auth()->user()->name ?? 'User' }}</p>
                    <p class="text-xs text-gray-500 truncate mt-0.5">{{ auth()->user()->email ?? '-' }}</p>
                    @if(auth()->user()->kantor)
                    <div class="mt-2 text-[10px] text-primary-700 bg-primary-50 px-2 py-1 rounded-md inline-block font-bold uppercase tracking-wider">
                        <i class="fa-solid fa-building mr-1"></i> {{ auth()->user()->kantor->nama }}
                    </div>
                    @endif
                </div>
                
                <a href="{{ route('profile.index') }}" class="block px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition-colors">
                    <i class="fa-regular fa-circle-user text-gray-400 w-5"></i> Profil Saya
                </a>
                
                <div class="border-t border-gray-50 mt-1 pt-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                            <i class="fa-solid fa-arrow-right-from-bracket text-red-400 w-5"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
