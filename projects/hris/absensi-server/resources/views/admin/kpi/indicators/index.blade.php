@extends('admin.layout')

@section('header', 'Pengaturan Indikator KPI')

@section('content')
<div class="admin-card">
    <div class="p-6 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h4 class="font-bold text-slate-800 leading-none">Daftar Indikator Penilaian</h4>
            <p class="text-[10px] text-slate-400 mt-1 font-medium uppercase tracking-wider">Atur kriteria yang digunakan untuk penilaian KPI mobile</p>
        </div>
        
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')" class="btn-primary">
            + Tambah Indikator
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-[10px] font-black uppercase tracking-wider">
                    <th class="px-6 py-4" style="width: 80px;">Urutan</th>
                    <th class="px-6 py-4">Label Indikator</th>
                    <th class="px-6 py-4">Deskripsi / Panduan</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($indicators as $i)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 text-xs font-bold text-slate-400">
                        #{{ $i->sort_order }}
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-slate-800">{{ $i->label }}</p>
                        <p class="text-[10px] text-slate-400 font-mono">{{ $i->slug }}</p>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-500 max-w-xs">
                        {{ $i->description }}
                    </td>
                    <td class="px-6 py-4">
                        @if($i->is_active)
                            <span class="px-2 py-1 bg-green-100 text-green-600 rounded text-[10px] font-bold">AKTIF</span>
                        @else
                            <span class="px-2 py-1 bg-slate-100 text-slate-400 rounded text-[10px] font-bold">NON-AKTIF</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button onclick='openEditModal(@json($i))' class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" />
                                </svg>
                            </button>
                            <form action="{{ route('admin.kpi-indicators.destroy', $i->id) }}" method="POST" onsubmit="return confirm('Hapus indikator ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add -->
<div id="modal-add" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-slate-50 flex justify-between items-center">
            <h4 class="font-bold text-slate-800">Tambah Indikator Baru</h4>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form action="{{ route('admin.kpi-indicators.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Nama Indikator</label>
                <input type="text" name="label" required class="admin-input w-full" placeholder="Contoh: Kedisiplinan">
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Deskripsi / Panduan</label>
                <textarea name="description" class="admin-input w-full" rows="3" placeholder="Panduan singkat untuk penilai..."></textarea>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Urutan Tampil</label>
                <input type="number" name="sort_order" value="1" required class="admin-input w-full">
            </div>
            <div class="pt-4">
                <button type="submit" class="btn-primary w-full py-4">Simpan Indikator</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modal-edit" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-slate-50 flex justify-between items-center">
            <h4 class="font-bold text-slate-800">Edit Indikator</h4>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="form-edit" method="POST" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Nama Indikator</label>
                <input type="text" name="label" id="edit-label" required class="admin-input w-full">
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Deskripsi / Panduan</label>
                <textarea name="description" id="edit-description" class="admin-input w-full" rows="3"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Urutan Tampil</label>
                    <input type="number" name="sort_order" id="edit-sort-order" required class="admin-input w-full">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Status</label>
                    <select name="is_active" id="edit-active" class="admin-input w-full">
                        <option value="1">AKTIF</option>
                        <option value="0">NON-AKTIF</option>
                    </select>
                </div>
            </div>
            <div class="pt-4">
                <button type="submit" class="btn-primary w-full py-4">Perbarui Indikator</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(indicator) {
        document.getElementById('form-edit').action = `/admin/kpi-indicators/${indicator.id}`;
        document.getElementById('edit-label').value = indicator.label;
        document.getElementById('edit-description').value = indicator.description || '';
        document.getElementById('edit-sort-order').value = indicator.sort_order;
        document.getElementById('edit-active').value = indicator.is_active ? "1" : "0";
        document.getElementById('modal-edit').classList.remove('hidden');
    }
</script>
@endsection
