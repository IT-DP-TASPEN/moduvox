@extends('admin.layout')

@section('header', 'Manajemen Banner')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h3 class="text-xl font-bold text-slate-800">Banners Beranda</h3>
    <a href="{{ route('admin.banners.create') }}" class="bg-brand-blue text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
        Tambah Banner Baru
    </a>
</div>

<div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider">
                    <th class="px-6 py-4">Preview</th>
                    <th class="px-6 py-4">Judul</th>
                    <th class="px-6 py-4">Urutan</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($banners as $banner)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="w-24 h-12 bg-slate-100 rounded-lg overflow-hidden">
                            <img src="{{ asset('storage/' . $banner->image_path) }}" class="w-full h-full object-cover">
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-slate-800">
                        {{ $banner->title ?? 'Tanpa Judul' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">
                        {{ $banner->sort_order }}
                    </td>
                    <td class="px-6 py-4">
                        @if($banner->is_active)
                            <span class="px-2 py-1 bg-green-50 text-green-600 text-[10px] font-bold rounded uppercase">Aktif</span>
                        @else
                            <span class="px-2 py-1 bg-red-50 text-red-600 text-[10px] font-bold rounded uppercase">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.banners.edit', $banner) }}" class="p-2 text-slate-400 hover:text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="confirmAction(event, 'Hapus banner ini?')" class="p-2 text-slate-400 hover:text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-slate-500 text-sm">Belum ada banner yang ditambahkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
