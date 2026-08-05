@props(['user' => null, 'role' => null, 'company' => null, 'branch' => null])
@php
    $user = $user ?? auth()->user();
    $role = $role ?? ($user ? $user->getRoleNames()->first() : 'Guest');
    $company = $company ?? ($user ? \App\Models\Company::find($user->company_id) : null);
    $branch = $branch ?? ($user ? \App\Models\Branch::find($user->branch_id) : null);
@endphp

<aside x-data="{ search: '' }" class="w-72 h-full pearl-gradient text-white flex flex-col shadow-2xl z-20 shrink-0">
    <!-- Logo Area -->
    <div class="p-8">
        <a href="{{ route('dashboard') }}" class="block">
            <h1 class="font-headline font-extrabold text-2xl tracking-tight leading-tight">
                Core Banking <br/> <span class="text-tertiary-fixed">Koperasi</span>
             </h1>
        </a>
        <div class="h-1 w-12 bg-tertiary mt-4"></div>
    </div>

    <!-- Search Area -->
    <div class="px-8 mb-2">
        <div class="relative group">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-white/30 group-focus-within:text-tertiary-fixed transition-colors text-sm">search</span>
            <input x-model="search" type="text" placeholder="Cari menu..." class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 pl-11 pr-4 text-[11px] font-bold text-white placeholder-white/20 focus:ring-4 focus:ring-white/5 focus:border-white/20 outline-none transition-all">
        </div>
    </div>

    <!-- Navigation -->
    <nav id="sidebar-nav" class="flex-grow px-4 mt-4 space-y-1 overflow-y-auto sidebar-scrollbar pb-8">
        @php
            $rawMenus = \App\Models\Menu::active()
                ->main()
                ->orderBy('order')
                ->get();

            if (Route::has('assets.update') && auth()->user()?->canAny(['assets.update', 'assets.inquiry.update'])) {
                $assetsUpdateMenu = $rawMenus->firstWhere('route', 'assets.update');
                $permission = auth()->user()->can('assets.update') ? 'assets.update' : 'assets.inquiry.update';

                if ($assetsUpdateMenu) {
                    $assetsUpdateMenu->permission = $permission;
                    $assetsUpdateMenu->name = 'Perubahan Inventaris';
                } else {
                    $rawMenus->push(new \App\Models\Menu([
                        'name' => 'Perubahan Inventaris',
                        'code' => 'assets.update',
                        'icon' => 'edit_square',
                        'route' => 'assets.update',
                        'permission' => $permission,
                        'category' => 'Aset & Sewa',
                        'order' => 40,
                        'is_active' => true,
                    ]));
                }
            }
                
            $groupedMenus = $rawMenus->groupBy('category');

            $order = [
                '', // Root category (e.g. Dashboard)
                'CIF', 
                'Simpanan', 
                'Simpanan Berjangka', 
                'Pinjaman', 
                'Asuransi',
                'Akuntansi', 
                'Manajemen SHU',
                'Aset & Sewa', 
                'Master Data', 
                'Master Produk', 
                'System',
                'Management'
            ];
            $sortedMenus = collect();
            
            // Add priority categories first
            foreach ($order as $cat) {
                if ($groupedMenus->has($cat)) {
                    $sortedMenus->put($cat, $groupedMenus->get($cat));
                    $groupedMenus->forget($cat);
                }
            }
            
            // Add remaining categories alphabetically
            $groupedMenus->sortKeys()->each(function ($items, $key) use ($sortedMenus) {
                $sortedMenus->put($key, $items);
            });
        @endphp

        @foreach($sortedMenus as $category => $items)
            @php
                // Check if user has permission to at least one menu in this category
                $hasAnyAccess = false;
                foreach($items as $m) {
                    if(Route::has($m->route) && auth()->user()->can($m->permission)) {
                        $hasAnyAccess = true;
                        break;
                    }
                }
            @endphp

            @if($hasAnyAccess)
            <div x-data="{ open: true }" 
                 x-show="search === '' || @js($items->pluck('name')->map(fn($n) => strtolower($n))->toArray()).some(n => n.includes(search.toLowerCase()))"
                 class="mb-2">
                @if($category)
                    <div @click="open = !open" 
                         x-show="search === '' || @js($items->pluck('name')->map(fn($n) => strtolower($n))->toArray()).some(n => n.includes(search.toLowerCase()))"
                         class="pt-4 pb-2 px-4 flex justify-between items-center cursor-pointer hover:bg-white/5 rounded-xl group transition-all">
                        <p class="text-[10px] uppercase tracking-[0.2em] text-white font-bold opacity-70 group-hover:opacity-100 transition-opacity">{{ $category }}</p>
                        <span class="material-symbols-outlined text-sm text-white opacity-70 group-hover:opacity-100 transition-all duration-300 transform" :class="{'rotate-180': open}">expand_more</span>
                    </div>
                @endif

                <div x-show="open || search !== ''" class="space-y-1 mt-1">
                    @foreach($items as $menu)
                        @if(Route::has($menu->route))
                            @can($menu->permission)
                            <div x-show="search === '' || '{{ strtolower($menu->name) }}'.includes(search.toLowerCase())">
                                <a href="{{ route($menu->route) }}" wire:navigate
                                   class="flex items-center px-4 py-3 {{ request()->routeIs($menu->route . '*') && !request()->routeIs('dashboard') ? 'bg-white/10 text-tertiary-fixed shadow-sm' : 'hover:bg-white/5 text-white hover:text-tertiary-fixed' }} rounded-xl font-medium group transition-all">
                                    <span class="material-symbols-outlined mr-3 {{ request()->routeIs($menu->route . '*') && !request()->routeIs('dashboard') ? 'text-tertiary-fixed' : 'opacity-70 group-hover:opacity-100 group-hover:text-tertiary-fixed' }}">
                                        {{ $menu->icon }}
                                    </span>
                                    <span class="flex-grow menu-item-text">{{ $menu->name }}</span>
                                    @if($menu->route === 'approvals.inbox')
                                        <livewire:approvals.badge />
                                    @endif
                                </a>
                            </div>
                            @endcan
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach
    </nav>

    <script>
        function initSidebarScroll() {
            const sidebarNav = document.getElementById('sidebar-nav');
            if (sidebarNav) {
                // Restore scroll position
                const scrollPos = localStorage.getItem('sidebar-scroll');
                if (scrollPos) {
                    sidebarNav.scrollTop = scrollPos;
                }

                // Save scroll position on scroll
                sidebarNav.addEventListener('scroll', function() {
                    localStorage.setItem('sidebar-scroll', sidebarNav.scrollTop);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', initSidebarScroll);
        document.addEventListener('livewire:navigated', initSidebarScroll);
    </script>
</aside>
