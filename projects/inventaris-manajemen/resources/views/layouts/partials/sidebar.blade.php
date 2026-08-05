{{-- Sidebar Navigation --}}
<aside class="w-72 bg-white border-r border-gray-200 flex flex-col min-h-screen transition-all duration-300">
    {{-- Logo --}}
    <div class="h-[72px] flex items-center px-8 border-b border-gray-100 bg-white">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded bg-amber-500/20 text-amber-500 flex items-center justify-center">
                <i class="fa-solid fa-boxes-stacked text-sm"></i>
            </div>
            <span class="font-bold text-slate-800 text-lg tracking-tight">MODUVOX</span>
        </div>
    </div>

    {{-- Menu --}}
    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
                  {{ request()->routeIs('dashboard') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <i class="fa-solid fa-house w-5 text-center {{ request()->routeIs('dashboard') ? 'text-primary-600' : 'text-gray-400' }}"></i>
            Dashboard
        </a>

        {{-- Data Inventaris --}}
        <div class="pt-5">
            <p class="px-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Data Inventaris</p>
            <ul class="space-y-1 font-medium">
                <li>
                    <a href="{{ route('inventaris.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('inventaris.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="w-5 text-center {{ request()->routeIs('inventaris.*') ? 'text-primary-600' : 'text-gray-400' }}"><i class="fa-solid fa-boxes-stacked"></i></div>
                        Master Inventaris
                    </a>
                </li>
                <li>
                    <a href="{{ route('motor.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('motor.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="w-5 text-center {{ request()->routeIs('motor.*') ? 'text-primary-600' : 'text-gray-400' }}"><i class="fa-solid fa-motorcycle"></i></div>
                        Inventaris Motor
                    </a>
                </li>
                <li>
                    <a href="{{ route('tanah.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('tanah.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="w-5 text-center {{ request()->routeIs('tanah.*') ? 'text-primary-600' : 'text-gray-400' }}"><i class="fa-solid fa-map-location-dot"></i></div>
                        Inventaris Tanah
                    </a>
                </li>
                <li>
                    <a href="{{ route('penyusutan_list.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('penyusutan_list.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="w-5 text-center {{ request()->routeIs('penyusutan_list.*') ? 'text-primary-600' : 'text-gray-400' }}"><i class="fa-solid fa-arrow-trend-down"></i></div>
                        Data Penyusutan
                    </a>
                </li>
                <li>
                    <a href="{{ route('transaksi.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('transaksi.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="w-5 text-center {{ request()->routeIs('transaksi.*') ? 'text-primary-600' : 'text-gray-400' }}"><i class="fa-solid fa-right-left"></i></div>
                        Transaksi Inventaris
                    </a>
                </li>
            </ul>
        </div>

        {{-- Financials --}}
        <div class="pt-5">
            <p class="px-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Financials</p>
            <div class="space-y-1 font-medium">
                <a href="{{ route('penyusutan.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('penyusutan.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fa-solid fa-calculator w-5 text-center {{ request()->routeIs('penyusutan.*') ? 'text-primary-600' : 'text-gray-400' }}"></i>
                    Hitung Penyusutan
                </a>
                <a href="{{ route('api-journals.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('api-journals.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fa-solid fa-file-invoice-dollar w-5 text-center {{ request()->routeIs('api-journals.*') ? 'text-primary-600' : 'text-gray-400' }}"></i>
                    Status Jurnal API
                </a>
            </div>
        </div>

        {{-- Laporan --}}
        <div class="pt-5">
            <p class="px-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Laporan</p>
            <div class="space-y-1 font-medium">
                <a href="{{ route('reports.nominatif.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('reports.nominatif.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fa-solid fa-file-invoice w-5 text-center {{ request()->routeIs('reports.nominatif.*') ? 'text-primary-600' : 'text-gray-400' }}"></i>
                    Rincian Nominatif
                </a>
                <a href="{{ route('reports.penyusutan.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('reports.penyusutan.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fa-solid fa-file-invoice-dollar w-5 text-center {{ request()->routeIs('reports.penyusutan.*') ? 'text-primary-600' : 'text-gray-400' }}"></i>
                    Laporan Penyusutan
                </a>
            </div>
        </div>

        {{-- Master Data --}}
        <div class="pt-5">
            <p class="px-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Master Data</p>
            <div class="space-y-1 font-medium">
                <a href="{{ route('master.kantor.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('master.kantor.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fa-solid fa-building w-5 text-center {{ request()->routeIs('master.kantor.*') ? 'text-primary-600' : 'text-gray-400' }}"></i>
                    Kantor Cabang
                </a>
                <a href="{{ route('master.golongan.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('master.golongan.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fa-solid fa-layer-group w-5 text-center {{ request()->routeIs('master.golongan.*') ? 'text-primary-600' : 'text-gray-400' }}"></i>
                    Golongan Aset
                </a>
                <a href="{{ route('master.jenis.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('master.jenis.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fa-solid fa-tags w-5 text-center {{ request()->routeIs('master.jenis.*') ? 'text-primary-600' : 'text-gray-400' }}"></i>
                    Jenis Aset
                </a>
                <a href="{{ route('master.lokasi.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('master.lokasi.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fa-solid fa-map-location-dot w-5 text-center {{ request()->routeIs('master.lokasi.*') ? 'text-primary-600' : 'text-gray-400' }}"></i>
                    Lokasi
                </a>
                <a href="{{ route('master.ruangan.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('master.ruangan.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fa-solid fa-door-open w-5 text-center {{ request()->routeIs('master.ruangan.*') ? 'text-primary-600' : 'text-gray-400' }}"></i>
                    Ruangan
                </a>
                <a href="{{ route('master.sumber-dana.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('master.sumber-dana.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fa-solid fa-wallet w-5 text-center {{ request()->routeIs('master.sumber-dana.*') ? 'text-primary-600' : 'text-gray-400' }}"></i>
                    Sumber Dana
                </a>
            </div>
        </div>

        {{-- Manajemen Sistem --}}
        <div class="pt-5 mb-6">
            <p class="px-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Sistem</p>
            <div class="space-y-1 font-medium">
                <a href="{{ route('system.users.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('system.users.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fa-solid fa-users-gear w-5 text-center {{ request()->routeIs('system.users.*') ? 'text-primary-600' : 'text-gray-400' }}"></i>
                    User & Role
                </a>
                <a href="{{ route('system.audit.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('system.audit.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fa-solid fa-shield-halved w-5 text-center {{ request()->routeIs('system.audit.*') ? 'text-primary-600' : 'text-gray-400' }}"></i>
                    Audit Trail
                </a>
                <a href="{{ url('log-viewer') }}" target="_blank" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->is('log-viewer*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fa-solid fa-list-check w-5 text-center {{ request()->is('log-viewer*') ? 'text-primary-600' : 'text-gray-400' }}"></i>
                    Log Viewer
                </a>
                @role('Super Admin')
                <a href="{{ route('system.reset.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('system.reset.*') ? 'bg-red-50 text-red-700' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}">
                    <i class="fa-solid fa-rotate-left w-5 text-center {{ request()->routeIs('system.reset.*') ? 'text-red-600' : 'text-gray-400' }}"></i>
                    Reset Data
                </a>
                @endrole
            </div>
        </div>
    </nav>
</aside>
