@props([
    'paginator',
    'pageAction' => 'gotoPage',
])

@if($paginator && $paginator->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 bg-gradient-to-r from-slate-50 to-white">
        <div class="flex items-center justify-between gap-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                Menampilkan {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} dari {{ $paginator->total() }}
            </p>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="{{ $pageAction }}({{ max(1, $paginator->currentPage() - 1) }})"
                    @disabled(!$paginator->onFirstPage())
                    class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all {{ $paginator->onFirstPage() ? 'bg-slate-100 text-slate-300 border-slate-200 cursor-not-allowed' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-900 hover:text-white hover:border-slate-900' }}"
                >
                    Prev
                </button>
                @for($page = max(1, $paginator->currentPage() - 2); $page <= min($paginator->lastPage(), $paginator->currentPage() + 2); $page++)
                    <button
                        type="button"
                        wire:click="{{ $pageAction }}({{ $page }})"
                        class="w-9 h-9 rounded-xl text-[10px] font-black border transition-all {{ $page === $paginator->currentPage() ? 'bg-slate-900 text-white border-slate-900 shadow-md shadow-slate-900/20' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100' }}"
                    >
                        {{ $page }}
                    </button>
                @endfor
                <button
                    type="button"
                    wire:click="{{ $pageAction }}({{ min($paginator->lastPage(), $paginator->currentPage() + 1) }})"
                    @disabled(!$paginator->hasMorePages())
                    class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all {{ !$paginator->hasMorePages() ? 'bg-slate-100 text-slate-300 border-slate-200 cursor-not-allowed' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-900 hover:text-white hover:border-slate-900' }}"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
@endif
