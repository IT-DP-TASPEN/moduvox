@extends('admin.layout')
@section('header', 'Master Data Payroll')

@section('content')
<div class="space-y-8">

    {{-- Tab Navigation --}}
    <div class="flex gap-3">
        <a href="{{ route('admin.master-data.index', ['tab' => 'gapok']) }}"
            class="px-6 py-3 rounded-2xl text-sm font-bold transition-all {{ $tab == 'gapok' ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200' }}">
            Tabel Gaji Pokok (SKG & Golongan)
        </a>
        <a href="{{ route('admin.master-data.index', ['tab' => 'honorarium']) }}"
            class="px-6 py-3 rounded-2xl text-sm font-bold transition-all {{ $tab == 'honorarium' ? 'bg-brand-blue text-white shadow-lg shadow-blue-100' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200' }}">
            Tabel Honorarium (Kontrak)
        </a>
    </div>

    @if(session('error'))
    <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="font-medium text-sm">{{ session('error') }}</span>
    </div>
    @endif

    {{-- ═══════════════ TAB: GAPOK ═══════════════ --}}
    @if($tab == 'gapok')
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Master Gaji Pokok Tetap</h3>
                <p class="text-xs text-slate-400 mt-1">Tabel SKG (1-30) × Golongan (I-X) = Nominal Gaji Pokok</p>
            </div>
            <div class="flex items-center gap-4">
                <form method="GET" action="{{ route('admin.master-data.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="gapok">
                    <select name="grade" onchange="this.form.submit()" class="px-4 py-2.5 rounded-2xl border border-slate-200 text-sm font-semibold bg-white text-slate-600 focus:ring-2 focus:ring-blue-400">
                        <option value="">Semua Golongan</option>
                        @foreach(['I','II','III','IV','V','VI','VII','VIII','IX','X'] as $gr)
                        <option value="{{ $gr }}" {{ isset($filterGrade) && $filterGrade == $gr ? 'selected' : '' }}>Golongan {{ $gr }}</option>
                        @endforeach
                    </select>
                </form>
                <button onclick="document.getElementById('addGapokModal').classList.remove('hidden')"
                    class="bg-brand-blue text-white px-5 py-2.5 rounded-2xl text-sm font-semibold hover:opacity-90 transition-all shadow-lg shadow-blue-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Data
                </button>
            </div>
        </div>

        {{-- Gapok Table grouped by Grade --}}
        @foreach($gapokGrouped as $grade => $items)
        <div class="border-b border-slate-50">
            <div class="px-6 py-3 bg-slate-50">
                <h4 class="text-sm font-bold text-brand-blue">Golongan {{ $grade }}</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-400 text-[11px] uppercase tracking-wider">
                            <th class="px-6 py-3 w-20">SKG</th>
                            <th class="px-6 py-3 w-32">Golongan</th>
                            <th class="px-6 py-3">Nominal Gaji Pokok</th>
                            <th class="px-6 py-3 w-40 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $g)
                        <tr class="border-t border-slate-50 hover:bg-slate-50/50">
                            <td class="px-6 py-3 font-bold text-slate-700">{{ $g->skg }}</td>
                            <td class="px-6 py-3 font-bold text-slate-700">{{ $g->grade }}</td>
                            <td class="px-6 py-3">
                                <form method="POST" action="{{ route('admin.master-data.gapok.update', $g->id) }}" class="flex items-center gap-2" onsubmit="return syncRupiah(this)">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="amount" value="{{ $g->amount }}">
                                    <input type="text" data-rupiah value="{{ number_format($g->amount, 0, ',', '.') }}"
                                        class="w-52 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                                    <button type="submit" class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-xl text-xs font-bold hover:bg-blue-100 transition-all">Simpan</button>
                                </form>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <form method="POST" action="{{ route('admin.master-data.gapok.destroy', $g->id) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="confirmAction(event, 'Hapus Gapok SKG {{ $g->skg }} Golongan {{ $g->grade }}?')" class="px-3 py-1.5 bg-red-50 text-red-500 rounded-xl text-xs font-bold hover:bg-red-100 transition-all">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Add Gapok Modal --}}
    <div id="addGapokModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl">
            <h3 class="text-lg font-bold text-slate-800 mb-6">Tambah Data Gaji Pokok</h3>
            <form method="POST" action="{{ route('admin.master-data.gapok.store') }}" onsubmit="return syncRupiah(this)">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">SKG</label>
                        <input type="number" name="skg" min="1" max="30" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold" placeholder="1-30">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Golongan</label>
                        <select name="grade" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold">
                            <option value="">Pilih Golongan</option>
                            @foreach(['I','II','III','IV','V','VI','VII','VIII','IX','X'] as $gr)
                            <option value="{{ $gr }}">{{ $gr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Nominal Gaji Pokok (Rp)</label>
                        <input type="hidden" name="amount" value="0">
                        <input type="text" data-rupiah required class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold" placeholder="Rp 3.339.358">
                    </div>
                </div>
                <div class="flex gap-3 mt-8">
                    <button type="button" onclick="document.getElementById('addGapokModal').classList.add('hidden')" class="flex-1 py-3 bg-slate-100 text-slate-600 rounded-2xl text-sm font-bold hover:bg-slate-200 transition-all">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-brand-blue text-white rounded-2xl text-sm font-bold hover:opacity-90 transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @else
    {{-- ═══════════════ TAB: HONORARIUM ═══════════════ --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Master Honorarium (Kontrak)</h3>
                <p class="text-xs text-slate-400 mt-1">Jabatan × Tingkatan (MUDA/MADYA/UTAMA) = Nominal Honorarium</p>
            </div>
            <button onclick="document.getElementById('addHonorModal').classList.remove('hidden')"
                class="bg-brand-blue text-white px-5 py-2.5 rounded-2xl text-sm font-semibold hover:opacity-90 transition-all shadow-lg shadow-blue-100 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Data
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-400 text-[11px] uppercase tracking-wider bg-slate-50">
                        <th class="px-6 py-3">Jabatan</th>
                        <th class="px-6 py-3 w-28">Tingkat</th>
                        <th class="px-6 py-3">Nominal Honorarium</th>
                        <th class="px-6 py-3 w-40 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($honorData as $h)
                    <tr class="border-t border-slate-50 hover:bg-slate-50/50">
                        <td class="px-6 py-3 font-bold text-slate-700">{{ $h->position_name }}</td>
                        <td class="px-6 py-3">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold
                                {{ $h->level == 'UTAMA' ? 'bg-amber-100 text-amber-700' : ($h->level == 'MADYA' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                                {{ $h->level }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <form method="POST" action="{{ route('admin.master-data.honorarium.update', $h->id) }}" class="flex items-center gap-2" onsubmit="return syncRupiah(this)">
                                @csrf @method('PUT')
                                <input type="hidden" name="amount" value="{{ $h->amount }}">
                                <input type="text" data-rupiah value="{{ number_format($h->amount, 0, ',', '.') }}"
                                    class="w-52 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                                <button type="submit" class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-xl text-xs font-bold hover:bg-blue-100 transition-all">Simpan</button>
                            </form>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <form method="POST" action="{{ route('admin.master-data.honorarium.destroy', $h->id) }}">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="confirmAction(event, 'Hapus Honorarium {{ $h->position_name }} - {{ $h->level }}?')" class="px-3 py-1.5 bg-red-50 text-red-500 rounded-xl text-xs font-bold hover:bg-red-100 transition-all">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Honorarium Modal --}}
    <div id="addHonorModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl">
            <h3 class="text-lg font-bold text-slate-800 mb-6">Tambah Data Honorarium</h3>
            <form method="POST" action="{{ route('admin.master-data.honorarium.store') }}" onsubmit="return syncRupiah(this)">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Nama Jabatan</label>
                        <input type="text" name="position_name" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold" placeholder="Branch Manager">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Tingkatan</label>
                        <select name="level" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold">
                            <option value="">Pilih Tingkatan</option>
                            <option value="MUDA">MUDA</option>
                            <option value="MADYA">MADYA</option>
                            <option value="UTAMA">UTAMA</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Nominal Honorarium (Rp)</label>
                        <input type="hidden" name="amount" value="0">
                        <input type="text" data-rupiah required class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold" placeholder="Rp 7.006.877">
                    </div>
                </div>
                <div class="flex gap-3 mt-8">
                    <button type="button" onclick="document.getElementById('addHonorModal').classList.add('hidden')" class="flex-1 py-3 bg-slate-100 text-slate-600 rounded-2xl text-sm font-bold hover:bg-slate-200 transition-all">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-brand-blue text-white rounded-2xl text-sm font-bold hover:opacity-90 transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

<script>
    // Format angka ke Rupiah (titik sebagai pemisah ribuan)
    function formatRupiah(angka) {
        const num = angka.toString().replace(/\D/g, '');
        return num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Live formatting saat user mengetik
    document.querySelectorAll('[data-rupiah]').forEach(function(el) {
        el.addEventListener('input', function() {
            const raw = this.value.replace(/\D/g, '');
            this.value = formatRupiah(raw);
        });
    });

    // Sync value ke hidden input sebelum submit
    function syncRupiah(form) {
        const display = form.querySelector('[data-rupiah]');
        const hidden = form.querySelector('input[name="amount"][type="hidden"]');
        if (display && hidden) {
            hidden.value = display.value.replace(/\D/g, '');
        }
        return true;
    }
</script>
@endsection
