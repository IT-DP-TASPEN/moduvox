<tr class="hover:bg-slate-50/50 transition-all">
    <td class="px-8 py-5">
        <div class="flex flex-col">
            <span class="font-bold text-slate-700 text-xs">{{ $pos->name }}</span>
            @if($pos->category === 'PUSAT' && $pos->division)
            <span class="text-[9px] font-black text-slate-300 uppercase tracking-wider">{{ $pos->division->name }}</span>
            @endif
        </div>
    </td>
    <td class="px-8 py-5 w-72">
        <form method="POST" action="{{ route('admin.positions.update', $pos->id) }}" class="flex items-center gap-3">
            @csrf @method('PUT')
            <input type="text" class="rupiah-input flex-1 px-4 py-2 bg-slate-50 border-none rounded-xl text-xs font-bold" value="{{ $pos->currentAllowance() ? $pos->currentAllowance()->amount : 0 }}">
            <input type="hidden" name="amount" value="{{ $pos->currentAllowance() ? $pos->currentAllowance()->amount : 0 }}">
            <button type="submit" class="p-2 text-emerald-500 hover:bg-emerald-50 rounded-lg transition-all opacity-0 group-hover:opacity-100"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></button>
        </form>
    </td>
    <td class="px-8 py-5 text-right">
        <div class="flex justify-end gap-2">
            <form action="{{ route('admin.positions.destroy', $pos->id) }}" method="POST">
                @csrf @method('DELETE')
                <button type="button" onclick="confirmAction(event, 'Hapus jabatan {{ $pos->name }}?')" class="p-2 text-slate-300 hover:text-red-500 rounded-lg transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </form>
        </div>
    </td>
</tr>
