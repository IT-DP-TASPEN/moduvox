@extends('admin.layout')

@section('header', 'Pengajuan Izin')

@section('content')
<div class="admin-card">
    <div class="p-6 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
        <h4 class="font-bold text-slate-800">Daftar Pengajuan Izin</h4>
        
        <form action="{{ route('admin.permit_requests.index') }}" method="GET" class="flex flex-wrap items-center gap-1.5">
            <!-- Search -->
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..." class="admin-input pl-11 w-36">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Status -->
            <select name="status" class="admin-input w-24">
                <option value="">Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>

            <!-- Bulan -->
            <select name="month" class="admin-input w-28">
                <option value="">Bulan</option>
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>

            <!-- Tahun -->
            <select name="year" class="admin-input w-24">
                <option value="">Tahun</option>
                @foreach(range(date('Y'), date('Y')-2) as $y)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>

            <!-- Tipe -->
            <select name="type" class="admin-input w-32">
                <option value="">Tipe</option>
                <option value="Sakit" {{ request('type') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                <option value="Keperluan Keluarga" {{ request('type') == 'Keperluan Keluarga' ? 'selected' : '' }}>Keperluan Keluarga</option>
                <option value="Lainnya" {{ request('type') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
            </select>

            <button type="submit" class="btn-primary">Filter</button>
            
            @if(request()->anyFilled(['search', 'status', 'type', 'month', 'year']))
                <a href="{{ route('admin.permit_requests.index') }}" class="bg-slate-100 text-slate-400 hover:text-red-500 transition-colors p-2 rounded-xl" title="Reset Filter">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="admin-table-thead">
                    <th class="px-6 py-4">Karyawan</th>
                    <th class="px-6 py-4">Tipe Izin</th>
                    <th class="px-6 py-4 text-center">Waktu Pengajuan</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Lampiran</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($items as $it)
                <tr class="admin-table-row">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-[10px] font-bold text-slate-400 uppercase">
                                {{ substr($it->user->name, 0, 2) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800 leading-none">{{ $it->user->name }}</p>
                                <p class="text-[10px] text-slate-500 mt-1">{{ $it->user->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-bold text-slate-700">{{ $it->type }}</span>
                    </td>
                    <td class="px-6 py-4 text-center text-sm font-medium text-slate-800">
                        {{ $it->requested_at->format('d M Y, H:i') }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="badge {{ $it->status == 'approved' ? 'badge-success' : ($it->status == 'rejected' ? 'badge-danger' : 'badge-warning') }}">
                            {{ $it->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($it->photo_path)
                        <a href="{{ asset('storage/' . $it->photo_path) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase bg-indigo-50 px-2 py-1 rounded-lg transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            VIEW
                        </a>
                        @else
                        <span class="text-[10px] text-slate-400 italic font-medium">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($it->status == 'pending')
                        <div class="flex justify-end gap-2">
                            <form action="{{ route('admin.permit_requests.status', $it) }}" method="POST" onsubmit="confirmAction(event, 'Setujui pengajuan izin ini?')">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button class="px-3 py-2 rounded-xl bg-emerald-600 text-white text-[10px] font-bold hover:opacity-90 shadow-sm shadow-emerald-100 uppercase tracking-tighter transition-all">Approve</button>
                            </form>
                            <form action="{{ route('admin.permit_requests.status', $it) }}" method="POST" onsubmit="confirmAction(event, 'Tolak pengajuan izin ini?')">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button class="px-3 py-2 rounded-xl bg-rose-600 text-white text-[10px] font-bold hover:opacity-90 shadow-sm shadow-rose-100 uppercase tracking-tighter transition-all">Reject</button>
                            </form>
                        </div>
                        @else
                        <div class="text-right flex flex-col items-end">
                            <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Processed By</span>
                            <span class="text-xs font-bold text-slate-700 mt-0.5">{{ $it->approver->name ?? 'Admin' }}</span>
                            <p class="text-[10px] text-slate-400 font-medium">{{ $it->approved_at?->format('d/m/y H:i') }}</p>
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-6 border-t border-slate-50">
        {{ $items->links() }}
    </div>
</div>
@endsection

