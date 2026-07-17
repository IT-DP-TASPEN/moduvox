@extends('admin.layout')

@section('header', 'Master Divisi')

@section('actions')
<a href="{{ route('admin.divisions.create') }}" class="px-4 py-2 bg-brand-blue text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-100 hover:opacity-90 transition-all">
    + Tambah Divisi
</a>
@endsection

@section('content')
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50">
                    <th class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest w-20">NO</th>
                    <th class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">KODE</th>
                    <th class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">NAMA DIVISI / CABANG</th>
                    <th class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $idx => $item)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-4 px-6 text-sm text-slate-500">{{ $items->firstItem() + $idx }}</td>
                    <td class="py-4 px-6 text-sm font-bold text-slate-700">{{ $item->code }}</td>
                    <td class="py-4 px-6">
                        <span class="font-bold text-slate-700">{{ $item->name }}</span>
                    </td>
                    <td class="py-4 px-6 text-right space-x-2">
                        <a href="{{ route('admin.divisions.edit', $item) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-brand-blue hover:bg-blue-100 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </a>
                        <form action="{{ route('admin.divisions.destroy', $item) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="confirmAction(event, 'Hapus divisi {{ $item->name }}?')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-8 text-center text-slate-500 text-sm">Belum ada master divisi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-6">
    {{ $items->links() }}
</div>
@endsection
