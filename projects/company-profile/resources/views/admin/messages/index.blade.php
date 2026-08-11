@extends('layouts.admin')
@section('title','Pesan Masuk')
@section('page-title','Pesan Masuk')
@section('page-subtitle','Kelola pesan dan pertanyaan dari pengunjung website')

@section('content')
<div class="mt-2 flex items-center justify-between mb-6">
    <p class="text-slate-500 text-sm">Total <strong class="text-slate-700">{{ $messages->total() }}</strong> pesan</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-6 py-4 text-left font-semibold text-slate-600">Status</th>
                <th class="px-6 py-4 text-left font-semibold text-slate-600">Pengirim</th>
                <th class="px-6 py-4 text-left font-semibold text-slate-600">Subjek & Pesan</th>
                <th class="px-6 py-4 text-left font-semibold text-slate-600">Tanggal</th>
                <th class="px-6 py-4 text-right font-semibold text-slate-600">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($messages as $msg)
            <tr class="hover:bg-slate-50 transition-colors {{ !$msg->is_read ? 'bg-red-50/20' : '' }}">
                <td class="px-6 py-4">
                    @if(!$msg->is_read)
                    <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full"><span class="w-1.5 h-1.5 rounded-full bg-red-600"></span> Pesan Baru</span>
                    @else
                    <span class="text-slate-400 text-xs font-medium px-2.5 py-1">Sudah Dibaca</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <p class="font-bold text-slate-800">{{ $msg->name }}</p>
                    <p class="text-slate-500 text-xs mt-0.5">{{ $msg->email }}</p>
                </td>
                <td class="px-6 py-4">
                    <p class="font-semibold text-slate-800">{{ $msg->subject }}</p>
                    <p class="text-slate-500 text-xs line-clamp-1 max-w-md mt-0.5">{{ $msg->message }}</p>
                </td>
                <td class="px-6 py-4 text-slate-500 text-xs">
                    {{ $msg->created_at->isoFormat('D MMM YYYY, HH:mm') }} WIB<br>
                    <span class="text-slate-400">{{ $msg->created_at->diffForHumans() }}</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.messages.show', $msg) }}" title="Lihat detail"
                           class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all {{ !$msg->is_read ? 'text-blue-600' : '' }}">
                            <span class="material-symbols-outlined text-lg">visibility</span>
                        </a>
                        <form action="{{ route('admin.messages.destroy', $msg) }}" method="POST" class="delete-form">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Hapus">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-16 text-center text-slate-400">
                    <span class="material-symbols-outlined text-4xl block mb-2 opacity-30">inbox</span>
                    Belum terdapat pesan masuk.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($messages->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">{{ $messages->links() }}</div>
    @endif
</div>
@endsection

