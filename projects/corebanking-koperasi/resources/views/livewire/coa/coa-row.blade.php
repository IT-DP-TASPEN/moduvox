<tr class="hover:bg-primary/[0.02] transition-colors group">
    <td class="px-6 py-4">
        <div class="flex items-center">
            {{-- Indentation Tree Lines --}}
            @for($i = 0; $i < $level; $i++)
                <div class="w-6 h-full flex justify-center">
                    <div class="w-[1px] h-full bg-surface-dim/50"></div>
                </div>
            @endfor
            
            <div class="flex items-center space-x-3">
                @if(!$coa->is_leaf)
                    <span class="material-symbols-outlined text-[16px] text-primary/40">folder_open</span>
                @else
                    <span class="material-symbols-outlined text-[14px] text-outline/30 ml-0.5">remove</span>
                @endif
                <span class="font-mono text-[10px] bg-surface-dim/30 px-2 py-0.5 rounded text-outline font-black tracking-tighter">{{ $coa->coa_code }}</span>
            </div>
        </div>
    </td>
    <td class="px-6 py-4">
        <p class="text-[13px] tracking-tight uppercase {{ $coa->is_leaf ? 'text-primary font-medium' : 'text-primary font-black opacity-90' }}">
            {{ $coa->name }}
        </p>
    </td>
    <td class="px-6 py-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest border
            @if($coa->type == 'ASSET') bg-blue-50 text-blue-700 border-blue-100/50
            @elseif($coa->type == 'LIABILITY') bg-rose-50 text-rose-700 border-rose-100/50
            @elseif($coa->type == 'EQUITY') bg-indigo-50 text-indigo-700 border-indigo-100/50
            @elseif($coa->type == 'REVENUE') bg-emerald-50 text-emerald-700 border-emerald-100/50
            @else bg-amber-50 text-amber-700 border-amber-100/50 @endif">
            <span class="w-1 h-1 rounded-full mr-1.5 
                @if($coa->type == 'ASSET') bg-blue-500
                @elseif($coa->type == 'LIABILITY') bg-rose-500
                @elseif($coa->type == 'EQUITY') bg-indigo-500
                @elseif($coa->type == 'REVENUE') bg-emerald-500
                @else bg-amber-500 @endif"></span>
            {{ $coa->category_label }}
        </span>
    </td>
    <td class="px-6 py-4">
        <div class="flex items-center space-x-1.5">
            @if($coa->is_cash)
                <span class="bg-amber-100/50 text-amber-800 text-[8px] font-black px-1.5 py-0.5 rounded uppercase tracking-tighter border border-amber-200/50">Liquidity</span>
            @endif
            @if(!$coa->is_leaf)
                <span class="bg-surface-dim/40 text-outline text-[8px] font-black px-1.5 py-0.5 rounded uppercase tracking-tighter">Header</span>
            @endif
        </div>
    </td>
    <td class="px-6 py-4 text-right">
        <div class="flex justify-end space-x-1 opacity-0 group-hover:opacity-100 transition-all duration-300">
            @if(!$coa->is_leaf || $level < 3)
                @can('coa.create')
                <button wire:click="create({{ $coa->id }})" class="p-2 text-outline hover:text-primary hover:bg-primary/5 rounded-xl transition-all" title="Tambah Sub-Akun">
                    <span class="material-symbols-outlined text-[18px]">add_box</span>
                </button>
                @endcan
            @endif
            @can('coa.update')
            <button wire:click="edit({{ $coa->id }})" class="p-2 text-outline hover:text-primary hover:bg-primary/5 rounded-xl transition-all" title="Edit Akun">
                <span class="material-symbols-outlined text-[18px]">edit_square</span>
            </button>
            @endcan
        </div>
    </td>
</tr>

@if(($renderChildren ?? true) && $coa->children->count() > 0)
    @foreach($coa->children->sortBy('coa_code') as $child)
        @include('livewire.coa.coa-row', ['coa' => $child, 'level' => $level + 1, 'renderChildren' => $renderChildren ?? true])
    @endforeach
@endif
