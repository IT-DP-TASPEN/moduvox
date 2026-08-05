<div>
    <x-header title="Dashboard" subtitle="Selamat datang kembali" :user="$user" :role="$role" />

    <div class="p-8 max-w-7xl mx-auto">
        <!-- Welcome Section -->
        <div class="mb-12">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h3 class="font-headline font-bold text-4xl text-slate-900 mb-2 tracking-tight">Selamat Datang, {{ $user->name }}!</h3>
                    <p class="text-slate-500 font-medium flex items-center text-sm">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full mr-3 animate-pulse"></span>
                        Sistem Core Banking siap membantu operasional Anda hari ini.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-4 gap-8">
            <!-- Account Info Section -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-[2.5rem] p-8 border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-500 group">
                    <div class="w-16 h-16 bg-slate-900 rounded-2xl flex items-center justify-center text-white mb-6 group-hover:scale-110 transition-transform duration-500">
                        <span class="material-symbols-outlined text-3xl">account_circle</span>
                    </div>
                    <h5 class="font-headline font-bold text-xl text-slate-900 mb-1">Informasi Akun</h5>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-6">Credential & Lokasi</p>

                    <div class="space-y-4">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <p class="text-[9px] uppercase tracking-wider text-slate-400 font-extrabold mb-1">Username</p>
                            <p class="text-xs font-bold text-slate-900">{{ $user->username }}</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <p class="text-[9px] uppercase tracking-wider text-slate-400 font-extrabold mb-1">Role Utama</p>
                            <span class="inline-block px-3 py-1 bg-slate-900 text-white text-[9px] font-black rounded-full">
                                {{ strtoupper($role) }}
                            </span>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <p class="text-[9px] uppercase tracking-wider text-slate-400 font-extrabold mb-1">Perusahaan</p>
                            <p class="text-xs font-bold text-slate-900 truncate">{{ $company->company_name ?? '-' }}</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <p class="text-[9px] uppercase tracking-wider text-slate-400 font-extrabold mb-1">Cabang</p>
                            <p class="text-xs font-bold text-slate-900">{{ $branch->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="lg:col-span-3 h-full">
                <div class="bg-white rounded-[2.5rem] p-10 border border-slate-200 shadow-sm h-full flex flex-col">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center space-x-3">
                            <div class="p-2.5 bg-slate-900 text-white rounded-2xl shadow-lg shadow-slate-900/10">
                                <span class="material-symbols-outlined text-xl">history</span>
                            </div>
                            <div>
                                <h5 class="font-headline font-bold text-2xl text-slate-900">Menu Terakhir</h5>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Akses Cepat Monitoring</p>
                            </div>
                        </div>
                    </div>

                    @if(count($recentMenus) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-10">
                        @foreach($recentMenus as $menu)
                        <a href="{{ route($menu['route']) }}"
                            class="group relative bg-slate-50 p-6 rounded-[2rem] border border-slate-100 hover:border-slate-900 transition-all duration-500 overflow-hidden">
                            <div class="absolute -right-6 -top-6 w-24 h-24 bg-slate-900/5 rounded-full group-hover:scale-[4] transition-transform duration-700"></div>

                            <div class="relative flex flex-col items-start">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-slate-900 mb-4 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500">
                                    <span class="material-symbols-outlined text-xl group-hover:scale-110 transition-transform">{{ $menu['icon'] }}</span>
                                </div>
                                <h6 class="font-headline font-bold text-sm text-slate-900 mb-1 line-clamp-1">{{ $menu['display_name'] }}</h6>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter opacity-70">Buka Modul</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @else
                    <div class="bg-slate-50/50 rounded-[2rem] p-12 border border-slate-200 border-dashed text-center mb-10 flex-grow flex flex-col justify-center">
                        <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center mx-auto mb-4 text-slate-400">
                            <span class="material-symbols-outlined text-3xl">track_changes</span>
                        </div>
                        <h6 class="font-bold text-slate-900 mb-1">Riwayat Kunjungan Kosong</h6>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed">Gunakan sidebar untuk mulai menjelajahi<br/>modul-modul koperasi.</p>
                    </div>
                    @endif

                    <!-- Security Alert Bar -->
                    <div class="mt-auto pt-6 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 border border-emerald-100">
                                <span class="material-symbols-outlined text-xl">verified_user</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">Keamanan Terjamin</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.1em]">Sesi Anda diamankan dengan protokol enkripsi modern.</p>
                            </div>
                        </div>
                        <div class="hidden md:block text-right">
                            <p class="text-[9px] uppercase tracking-[0.2em] font-black text-slate-300 mb-0.5">Server Time</p>
                            <p class="text-xs font-black text-slate-900">{{ now()->format('H:i') }} WIB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>