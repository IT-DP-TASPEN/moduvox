@extends('admin.layout')

@section('header')
<div class="flex items-center gap-4">
    <a href="{{ route('admin.division-approvers.index') }}" class="p-2 hover:bg-slate-100 rounded-full transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Edit Setting Approval</h1>
    </div>
</div>
@endsection

@section('content')
<form action="{{ route('admin.division-approvers.update', $divisionApprover) }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50 bg-slate-50/50">
            <h4 class="font-bold text-slate-800">Data Approval Divisi</h4>
        </div>
        <div class="p-8 space-y-6">
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Nama Divisi</label>
                <div class="relative">
                    <input type="text" placeholder="Cari Divisi..." onkeyup="filterSelect(this, 'division_select')" class="w-full px-4 py-2 mb-2 text-xs border border-slate-100 rounded-xl bg-slate-50 focus:ring-1 focus:ring-brand-blue outline-none transition-all">
                    <select name="division_name" id="division_select" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-brand-blue outline-none transition-all">
                        <option value="">Pilih Divisi</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->name }}" {{ old('division_name', $divisionApprover->division_name) == $division->name ? 'selected' : '' }}>{{ $division->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('division_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Approver (Kepala Divisi)</label>
                <div class="relative">
                    <input type="text" placeholder="Cari Nama / Jabatan..." onkeyup="filterSelect(this, 'approver_select')" class="w-full px-4 py-2 mb-2 text-xs border border-slate-100 rounded-xl bg-slate-50 focus:ring-1 focus:ring-brand-blue outline-none transition-all">
                    <select name="approver_id" id="approver_select" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-brand-blue outline-none transition-all">
                        <option value="">-- Pilih Approver --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('approver_id', $divisionApprover->approver_id) == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->title }})</option>
                        @endforeach
                    </select>
                </div>
                @error('approver_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Direktur (Atasan Approver)</label>
                <div class="relative">
                    <input type="text" placeholder="Cari Nama / Jabatan..." onkeyup="filterSelect(this, 'director_select')" class="w-full px-4 py-2 mb-2 text-xs border border-slate-100 rounded-xl bg-slate-50 focus:ring-1 focus:ring-brand-blue outline-none transition-all">
                    <select name="director_id" id="director_select" class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-brand-blue outline-none transition-all">
                        <option value="">-- Pilih Direktur --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('director_id', $divisionApprover->director_id) == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->title }})</option>
                        @endforeach
                    </select>
                </div>
                @error('director_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-4">
        <a href="{{ route('admin.division-approvers.index') }}" class="px-8 py-4 rounded-2xl border border-slate-200 font-bold text-slate-500 hover:bg-slate-50 transition-all">Batal</a>
        <button type="submit" class="px-12 py-4 rounded-2xl bg-brand-blue text-white font-bold shadow-xl shadow-blue-100 hover:opacity-90 transition-all">Simpan</button>
    </div>
</form>

<script>
    function filterSelect(input, selectId) {
        const filter = input.value.toLowerCase();
        const select = document.getElementById(selectId);
        const options = select.options;

        for (let i = 0; i < options.length; i++) {
            const txtValue = options[i].textContent || options[i].innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1 || options[i].value === "") {
                options[i].style.display = "";
            } else {
                options[i].style.display = "none";
            }
        }
    }
</script>
@endsection
