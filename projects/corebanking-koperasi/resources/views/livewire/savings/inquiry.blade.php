<div class="p-0">
    <x-header title="Inquiry Rekening Simpanan" subtitle="Pencarian profil rekening, saldo, dan riwayat mutasi anggota"
        :user="$user" :role="$role">
        <x-slot:actions>
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <select wire:model.live="filter_branch"
                        class="pl-3 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-bold text-slate-600 appearance-none">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                        <span class="material-symbols-outlined text-sm">expand_more</span>
                    </div>
                </div>
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live="search" type="text" placeholder="No Rekening atau Nama..."
                        class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-medium w-64">
                </div>
                @can('savings.create')
                <a href="{{ route('savings.create') }}" wire:navigate
                    class="flex items-center space-x-2 bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-sm">add_card</span>
                    <span>Buka Rekening</span>
                </a>
                @endcan
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-10">
        @if($viewMode === 'grid')
        
        @if(!empty($search))
        <div class="mb-6 flex items-center justify-between px-2">
            <div class="flex items-center space-x-2 text-slate-500">
                <span class="material-symbols-outlined text-sm">info</span>
                <p class="text-[11px] font-bold uppercase tracking-widest">
                    Ditemukan <span class="text-slate-900">{{ $totalResults }}</span> Rekening untuk pencarian "{{ $search }}"
                </p>
            </div>
        </div>
        @endif

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/50">
                            <th
                                class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-center w-20">
                                Aksi</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">
                                No. Rekening</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">
                                Nama Peserta</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">
                                Produk</th>
                            <th
                                class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-right">
                                Saldo Saat Ini</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">
                                Cabang</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $item)
                        <tr wire:key="saving-row-{{ $item->id }}" class="hover:bg-slate-50/50 transition-colors group">
                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('savings.inquiry.detail', $item->id) }}" wire:navigate
                                        class="p-2 bg-white text-slate-600 hover:bg-slate-50 rounded-xl shadow-sm border border-slate-200 transition-all hover:text-slate-900"
                                        title="Detail">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                    </a>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-sm font-extrabold text-slate-800 tracking-wider font-mono">{{
                                    $item->account_no }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-sm text-slate-900 uppercase leading-none mb-1">{{
                                    $item->cif->name }}</p>
                                <p class="text-[10px] text-slate-500 font-bold tracking-widest">{{ $item->cif->nik }}
                                </p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-[10px] text-slate-600 uppercase tracking-widest shrink-0">{{
                                    $item->product->name }}</p>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <p class="font-black text-sm text-slate-900 tracking-tight">Rp {{
                                    number_format($item->balance, 2, ',', '.') }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-[10px] text-slate-500 uppercase tracking-widest">{{
                                    $item->branch->name }}</p>
                            </td>
                            <td class="py-4 px-6">
                                @php
                                $statusClass = match($item->status) {
                                'ACTIVE' => 'bg-emerald-100 text-emerald-700',
                                'BLOCKED' => 'bg-rose-100 text-rose-700',
                                'DORMANT' => 'bg-amber-100 text-amber-700',
                                'CLOSED' => 'bg-slate-100 text-slate-600',
                                default => 'bg-slate-50 text-slate-400'
                                };
                                @endphp
                                <span
                                    class="px-3 py-1 text-[9px] font-black uppercase tracking-wider rounded-lg {{ $statusClass }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-32 text-center text-slate-400">
                                <span class="material-symbols-outlined text-5xl mb-4 opacity-20">person_search</span>
                                <p class="text-sm font-bold">Lakukan pencarian no rekening atau nama...</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $items->links() }}
            </div>
            @endif
        </div>

        @else

        @if($selectedAccount)
        @include('livewire.savings.partials.comprehensive-detail')
        @else
        <div class="bg-white rounded-[2rem] p-20 text-center border border-slate-200">
            <span class="material-symbols-outlined text-4xl text-slate-300 animate-pulse">sync</span>
            <p class="text-sm font-bold text-slate-400 mt-4 uppercase tracking-widest">Memuat Data Rekening...</p>
        </div>
        @endif

        @endif
    </div>
</div>