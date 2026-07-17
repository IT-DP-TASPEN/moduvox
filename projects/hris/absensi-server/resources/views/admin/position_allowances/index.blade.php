@extends('admin.layout')

@section('header', 'Master Tunjangan Jabatan')

@section('content')
<div class="space-y-8">
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-8 border-b border-slate-100 bg-slate-50/50 space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Daftar Tunjangan per Jabatan</h3>
                    <p class="text-slate-500 text-sm">Nominal ini akan otomatis ditarik untuk karyawan tetap sesuai jabatannya.</p>
                </div>
                <button onclick="document.getElementById('addPositionModal').classList.remove('hidden')" class="bg-brand-blue text-white px-6 py-2.5 rounded-2xl text-sm font-bold hover:opacity-90 transition-all shadow-lg shadow-blue-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Jabatan
                </button>
            </div>

            <!-- Search Bar -->
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" id="tableSearch" placeholder="Cari nama jabatan..." 
                    class="w-full pl-12 pr-6 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent transition-all text-sm font-medium">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left" id="positionTable">
                <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-black tracking-widest">
                    <tr>
                        <th class="px-8 py-4">Nama Jabatan</th>
                        <th class="px-8 py-4 w-80">Nominal Tunjangan</th>
                        <th class="px-8 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($items as $item)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-4">
                            <span class="font-bold text-slate-700 uppercase text-xs position-name">{{ $item->position_name }}</span>
                        </td>
                        <td class="px-8 py-4">
                            <form method="POST" action="{{ route('admin.position-allowances.update', $item->id) }}" class="flex items-center gap-3">
                                @csrf @method('PUT')
                                <div class="relative flex-1">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                                    <input type="text" data-rupiah value="{{ number_format($item->amount, 0, ',', '.') }}" 
                                        class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-brand-blue focus:bg-white transition-all">
                                    <input type="hidden" name="amount" value="{{ $item->amount }}">
                                </div>
                                <button type="submit" class="bg-emerald-50 text-emerald-600 px-6 py-3 rounded-xl text-xs font-bold hover:bg-emerald-500 hover:text-white transition-all shadow-sm">Simpan</button>
                            </form>
                        </td>
                        <td class="px-8 py-4 text-right">
                            <form action="{{ route('admin.position-allowances.destroy', $item->id) }}" method="POST" class="inline-block">
                                @csrf @method('DELETE')
                                <button type="button" onclick="confirmAction(event, 'Hapus jabatan {{ $item->position_name }} dari master data?')" class="p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Jabatan -->
<div id="addPositionModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl relative">
        <h3 class="text-lg font-bold text-slate-800 mb-6">Tambah Jabatan Baru</h3>
        <form method="POST" action="{{ route('admin.position-allowances.store') }}" onsubmit="return syncBeforeSubmit(this)">
            @csrf
            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Nama Jabatan</label>
                    <input type="text" name="position_name" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-brand-blue transition-all font-semibold text-slate-700" placeholder="Contoh: Manager IT">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Nominal Tunjangan</label>
                    <div class="relative">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 font-bold text-slate-400">Rp</span>
                        <input type="text" data-rupiah class="w-full pl-12 pr-6 py-3 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-brand-blue transition-all font-bold text-slate-700" required>
                        <input type="hidden" name="amount" value="0">
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-8">
                <button type="button" onclick="document.getElementById('addPositionModal').classList.add('hidden')" class="flex-1 py-3 bg-slate-100 text-slate-600 rounded-2xl text-sm font-bold hover:bg-slate-200 transition-all">Batal</button>
                <button type="submit" class="flex-1 py-3 bg-brand-blue text-white rounded-2xl text-sm font-bold hover:opacity-90 transition-all shadow-lg shadow-blue-100">Simpan Jabatan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function formatRupiah(angka) {
        if (!angka) return '';
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    $(document).ready(function() {
        // Real-time search
        $('#tableSearch').on('keyup', function() {
            let value = $(this).val().toLowerCase();
            $('#positionTable tbody tr').filter(function() {
                $(this).toggle($(this).find('.position-name').text().toLowerCase().indexOf(value) > -1)
            });
        });

        $('[data-rupiah]').on('input', function() {
            let val = $(this).val().replace(/\D/g, '');
            $(this).val(formatRupiah(val));
            $(this).siblings('input[type="hidden"]').val(val);
        });

        $('form').on('submit', function() {
            $(this).find('[data-rupiah]').each(function() {
                let val = $(this).val().replace(/\D/g, '');
                $(this).siblings('input[type="hidden"]').val(val);
            });
        });
    });

    function syncBeforeSubmit(form) {
        let display = $(form).find('[data-rupiah]');
        let hidden = $(form).find('input[name="amount"][type="hidden"]');
        hidden.val(display.val().replace(/\D/g, ''));
        return true;
    }
</script>
@endpush
@endsection
