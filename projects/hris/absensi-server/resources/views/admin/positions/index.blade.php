@extends('admin.layout')

@section('header', 'Workspace Struktur & Benefit')

@section('content')
<div class="h-[calc(100vh-140px)] -m-8 flex flex-col overflow-hidden bg-[#F8FAFC]" 
     x-data="orgWorkspace()" 
     x-init="init()">
    
    <!-- 1. FILTER BAR (GLOBAL CONTROL) -->
    <div class="bg-white border-b border-slate-200 px-8 py-4 flex flex-wrap items-center justify-between gap-4 z-20 shadow-sm">
        <div class="flex items-center gap-6">
            <div class="flex p-1 bg-slate-100 rounded-xl">
                <template x-for="s in ['ALL', 'PUSAT', 'CABANG']">
                    <button @click="scope = s" 
                        :class="scope === s ? 'bg-white shadow-sm text-brand-blue' : 'text-slate-500 hover:text-slate-700'"
                        class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all" 
                        x-text="s"></button>
                </template>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-blue-50 text-brand-blue rounded-full text-[10px] font-black uppercase tracking-wider">
                    Total: <span x-text="stats.total"></span>
                </span>
                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-wider">
                    Pusat: <span x-text="stats.pusat"></span>
                </span>
                <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-[10px] font-black uppercase tracking-wider">
                    Cabang: <span x-text="stats.cabang"></span>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-1 max-w-md">
            <div class="relative w-full">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </span>
                <input type="text" x-model="search" placeholder="Cari nama jabatan..." 
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-brand-blue/20 transition-all">
            </div>
            <button @click="isModalOpen = true" class="bg-brand-blue text-white p-2.5 rounded-xl shadow-lg shadow-blue-100 hover:opacity-90 transition-all shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            </button>
        </div>
    </div>

    <div class="flex-1 flex overflow-hidden">
        <!-- 2. LEFT PANEL — Struktur Divisi -->
        <div class="w-72 bg-white border-r border-slate-200 flex flex-col shrink-0">
            <div class="p-6 border-b border-slate-50">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Struktur Divisi</h4>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-1">
                <button @click="selectedDivId = null" 
                    :class="selectedDivId === null ? 'bg-brand-blue/5 text-brand-blue' : 'text-slate-600 hover:bg-slate-50'"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all text-left group">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        <span class="text-xs font-bold">Semua Divisi</span>
                    </div>
                </button>
                @foreach($divisions as $div)
                <button @click="selectedDivId = {{ $div->id }}" 
                    :class="selectedDivId === {{ $div->id }} ? 'bg-brand-blue/5 text-brand-blue' : 'text-slate-600 hover:bg-slate-50'"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all text-left group">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-brand-blue transition-colors"></div>
                        <span class="text-xs font-bold truncate">{{ $div->name }}</span>
                    </div>
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-400 rounded-md text-[9px] font-black">{{ $div->positions_count }}</span>
                </button>
                @endforeach
                
                <button @click="isDivModalOpen = true" class="w-full mt-4 flex items-center gap-2 px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider hover:text-brand-blue transition-all border-t border-slate-50 pt-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Tambah Divisi
                </button>
            </div>
        </div>

        <!-- 3. MAIN PANEL — Position Cards -->
        <div class="flex-1 overflow-y-auto bg-[#F8FAFC] p-8 space-y-8 scroll-smooth" id="main-canvas">
            <template x-for="div in filteredStructure" :key="div.id">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-6 bg-slate-800 rounded-full"></div>
                        <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest" x-text="div.name"></h3>
                    </div>
                    
                    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-8 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Jabatan</th>
                                    <th class="px-8 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Scope</th>
                                    <th class="px-8 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Base Tunjangan</th>
                                    <th class="px-8 py-4 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <template x-for="pos in div.positions" :key="pos.id">
                                    <tr class="group hover:bg-slate-50/30 transition-all cursor-pointer" 
                                        @click="selectPosition(pos.id)"
                                        :class="activePosId === pos.id ? 'bg-brand-blue/[0.02]' : ''">
                                        <td class="px-8 py-5">
                                            <span class="font-bold text-slate-700 text-xs" x-text="pos.name"></span>
                                        </td>
                                        <td class="px-8 py-5">
                                            <span :class="pos.category === 'PUSAT' ? 'bg-blue-50 text-brand-blue' : 'bg-amber-50 text-amber-600'" 
                                                class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider" 
                                                x-text="pos.category"></span>
                                        </td>
                                        <td class="px-8 py-5" @click.stop>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-black text-slate-300">Rp</span>
                                                <input type="text" 
                                                    :value="formatRupiah(pos.current_amount)"
                                                    @change="updateAmount(pos.id, $event.target.value)"
                                                    class="w-32 bg-transparent border-none p-0 text-xs font-black text-slate-800 focus:ring-0">
                                            </div>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-300 group-hover:text-brand-blue transition-all ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>

        <!-- 4. RIGHT PANEL — Detail -->
        <div class="w-[450px] bg-white border-l border-slate-200 shrink-0 flex flex-col z-30"
             x-show="activePosId !== null"
             x-transition:enter="translate-x-full transition-transform duration-300"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="translate-x-full transition-transform duration-300">
            
            <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Config Detail</h4>
                    <p class="text-sm font-black text-slate-800" x-text="detail.name"></p>
                </div>
                <button @click="activePosId = null" class="p-2 hover:bg-slate-200 rounded-xl transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-8 space-y-8 relative">
                <!-- Loading Overlay -->
                <div x-show="isDetailsLoading" class="absolute inset-0 bg-white/80 backdrop-blur-[2px] z-50 flex items-center justify-center">
                    <div class="flex flex-col items-center gap-3">
                        <svg class="animate-spin h-8 w-8 text-brand-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Memuat Detail...</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <h5 class="text-[10px] font-black text-slate-800 uppercase tracking-widest">Basic Information</h5>
                    <div class="grid gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Jabatan</label>
                            <input type="text" x-model="detail.name" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-xs font-bold text-slate-700">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Divisi</label>
                            <select x-model="detail.division_id" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-xs font-bold text-slate-700">
                                @foreach($divisions as $div) <option value="{{ $div->id }}">{{ $div->name }}</option> @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Scope Kantor</label>
                            <div class="flex p-1 bg-slate-50 rounded-xl">
                                <button @click="detail.category = 'PUSAT'" :class="detail.category === 'PUSAT' ? 'bg-white shadow-sm text-slate-800' : 'text-slate-400'" class="flex-1 py-2 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all">PUSAT</button>
                                <button @click="detail.category = 'CABANG'" :class="detail.category === 'CABANG' ? 'bg-white shadow-sm text-slate-800' : 'text-slate-400'" class="flex-1 py-2 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all">CABANG</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 pt-6 border-t border-slate-50">
                    <h5 class="text-[10px] font-black text-slate-800 uppercase tracking-widest">Base Allowance</h5>
                    <div class="bg-slate-50 rounded-2xl p-6 flex flex-col items-center">
                        <div class="flex items-baseline gap-2">
                            <span class="text-sm font-black text-slate-400">Rp</span>
                            <input type="text" :value="formatRupiah(detail.amount)" @change="detail.amount = $event.target.value.replace(/[^,\d]/g, '')" class="bg-transparent border-none p-0 text-3xl font-black text-slate-800 focus:ring-0 text-center w-full">
                        </div>
                    </div>
                </div>

                <div class="space-y-4 pt-6 border-t border-slate-50">
                    <h5 class="text-[10px] font-black text-slate-800 uppercase tracking-widest">Approval Workflow</h5>
                    <div x-show="detail.category === 'PUSAT'" class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Manager (Lvl 1)</label>
                            <select x-model="detail.approver_id" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-xs font-bold text-slate-700">
                                <option value="">Pilih Manager...</option>
                                @foreach($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Direktur (Lvl 2)</label>
                            <select x-model="detail.director_id" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-xs font-bold text-slate-700">
                                <option value="">Pilih Direktur...</option>
                                @foreach($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                    <div x-show="detail.category === 'CABANG'" class="bg-amber-50 p-4 rounded-2xl text-[10px] font-bold text-amber-700">
                        Approval jabatan Cabang mengikuti Jalur Approval Kantor Cabang (Pemimpin Cabang).
                    </div>
                </div>
            </div>

            <div class="p-8 border-t border-slate-100 bg-white">
                <button @click="saveDetail()" class="w-full py-4 bg-brand-blue text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-100 hover:opacity-90 transition-all">
                    Simpan Perubahan
                </button>
                <button @click="confirmDelete()" class="w-full mt-4 py-3 text-[10px] font-black text-red-300 uppercase tracking-widest hover:text-red-500 transition-all">Hapus Jabatan</button>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div x-show="isModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[100]">
        <div class="bg-white rounded-[2.5rem] p-10 w-full max-w-md shadow-2xl" @click.away="isModalOpen = false">
            <h3 class="text-xl font-black text-slate-800 mb-6 uppercase tracking-tight">Buat Jabatan Baru</h3>
            <form method="POST" action="{{ route('admin.positions.store') }}">
                @csrf
                <div class="space-y-4" x-data="{ newCat: 'CABANG' }">
                    <input type="text" name="name" required class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none font-bold text-slate-700" placeholder="Nama Jabatan">
                    <div class="flex p-1 bg-slate-100 rounded-2xl">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="category" value="PUSAT" x-model="newCat" class="hidden">
                            <div :class="newCat === 'PUSAT' ? 'bg-white shadow-sm text-slate-800' : 'text-slate-400'" class="py-3 text-center rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">PUSAT</div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="category" value="CABANG" x-model="newCat" class="hidden">
                            <div :class="newCat === 'CABANG' ? 'bg-white shadow-sm text-slate-800' : 'text-slate-400'" class="py-3 text-center rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">CABANG</div>
                        </label>
                    </div>
                    <select name="division_id" required class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none font-bold text-slate-700">
                        <option value="">Pilih Divisi...</option>
                        @foreach($divisions as $div) <option value="{{ $div->id }}">{{ $div->name }}</option> @endforeach
                    </select>
                    <div class="relative">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 font-bold text-slate-400">Rp</span>
                        <input type="text" name="amount_display" onkeyup="this.nextElementSibling.value = this.value.replace(/[^,\d]/g, '')" class="w-full pl-12 pr-6 py-4 rounded-2xl bg-slate-50 border-none font-black text-slate-700" required placeholder="Tunjangan">
                        <input type="hidden" name="amount" value="0">
                    </div>
                </div>
                <div class="flex gap-3 mt-10">
                    <button type="button" @click="isModalOpen = false" class="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl text-sm font-bold">Batal</button>
                    <button type="submit" class="flex-1 py-4 bg-brand-blue text-white rounded-2xl text-sm font-bold shadow-lg shadow-blue-100">Buat Sekarang</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="isDivModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[110]">
        <div class="bg-white rounded-[2.5rem] p-10 w-full max-w-md shadow-2xl" @click.away="isDivModalOpen = false">
            <h3 class="text-xl font-black text-slate-800 mb-6 uppercase tracking-tight">Tambah Divisi</h3>
            <form method="POST" action="{{ route('admin.positions.store-division') }}">
                @csrf
                <input type="text" name="name" required class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none font-bold text-slate-700" placeholder="Nama Divisi">
                <div class="flex gap-3 mt-10">
                    <button type="button" @click="isDivModalOpen = false" class="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl text-sm font-bold">Batal</button>
                    <button type="submit" class="flex-1 py-4 bg-slate-800 text-white rounded-2xl text-sm font-bold shadow-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function orgWorkspace() {
    return {
        scope: 'ALL',
        search: '',
        selectedDivId: null,
        activePosId: null,
        isDetailsLoading: false,
        isModalOpen: false,
        isDivModalOpen: false,
        stats: {
            total: {{ $stats['total_positions'] }},
            pusat: {{ $stats['total_pusat'] }},
            cabang: {{ $stats['total_cabang'] }}
        },
        structure: [
            @foreach($divisions as $div)
            {
                id: {{ $div->id }},
                name: '{{ $div->name }}',
                positions: [
                    @foreach($positions->where('division_id', $div->id) as $pos)
                    {
                        id: {{ $pos->id }},
                        name: '{{ $pos->name }}',
                        category: '{{ $pos->category }}',
                        current_amount: {{ $pos->currentAllowance() ? $pos->currentAllowance()->amount : 0 }}
                    },
                    @endforeach
                ]
            },
            @endforeach
        ],
        detail: { id: null, name: '', division_id: null, category: 'CABANG', amount: 0, usage: 0, approver_id: '', director_id: '' },

        get filteredStructure() {
            return this.structure.map(div => {
                let filteredPositions = div.positions.filter(pos => {
                    let matchScope = this.scope === 'ALL' || pos.category === this.scope;
                    let matchSearch = pos.name.toLowerCase().includes(this.search.toLowerCase());
                    return matchScope && matchSearch;
                });
                return { ...div, positions: filteredPositions };
            }).filter(div => {
                let matchDiv = this.selectedDivId === null || div.id === this.selectedDivId;
                return matchDiv && div.positions.length > 0;
            });
        },

        init() {},
        formatRupiah(number) { return new Intl.NumberFormat('id-ID').format(number); },

        async selectPosition(id) {
            if (this.activePosId === id) return;
            this.activePosId = id;
            this.isDetailsLoading = true;
            
            // Reset detail to avoid showing old data
            this.detail = { id: null, name: 'Memuat...', division_id: '', category: '', amount: 0, usage: 0, approver_id: '', director_id: '' };

            try {
                const res = await fetch(`{{ url('admin/api/positions') }}/${id}/detail`);
                if (!res.ok) throw new Error('Network response was not ok');
                const data = await res.json();
                
                this.detail = {
                    id: data.position.id,
                    name: data.position.name,
                    division_id: data.position.division_id,
                    category: data.position.category,
                    amount: data.allowance ? data.allowance.amount : 0,
                    usage: data.usage_count, 
                    approver_id: data.approval_rules ? data.approval_rules.approver_id : '',
                    director_id: data.approval_rules ? data.approval_rules.director_id : ''
                };
            } catch (e) {
                console.error("Error loading position detail:", e);
                Toast.fire({ icon: 'error', title: 'Gagal memuat detail jabatan' });
                this.activePosId = null;
            } finally {
                this.isDetailsLoading = false;
            }
        },

        async updateAmount(id, formattedVal) {
            const amount = formattedVal.replace(/[^,\d]/g, '');
            try {
                await $.post(`{{ url('admin/api/positions') }}/${id}/quick-allowance`, { _token: '{{ csrf_token() }}', amount: amount });
                this.structure.forEach(div => { div.positions.forEach(p => { if(p.id === id) p.current_amount = amount; }); });
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Tunjangan diperbarui', showConfirmButton: false, timer: 1500 });
            } catch (e) { console.error(e); }
        },

        async saveDetail() {
            try {
                await $.post(`{{ url('admin/api/positions') }}/${this.detail.id}/save-config`, {
                    _token: '{{ csrf_token() }}', name: this.detail.name, division_id: this.detail.division_id,
                    category: this.detail.category, amount: this.detail.amount,
                    approver_id: this.detail.approver_id, director_id: this.detail.director_id
                });
                location.reload();
            } catch (e) { console.error(e); }
        },

        confirmDelete() {
            Swal.fire({ title: 'Hapus Jabatan?', text: "Data tidak bisa dikembalikan.", icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus!' }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.createElement('form'); form.method = 'POST'; form.action = `{{ url('admin/positions') }}/${this.activePosId}`;
                    form.innerHTML = `@csrf @method('DELETE')`; document.body.appendChild(form); form.submit();
                }
            });
        }
    }
}
</script>
@endpush
@endsection
