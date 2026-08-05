<div wire:poll.30s class="inline-flex">
    @if($count > 0)
        <span class="inline-flex items-center justify-center bg-rose-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-lg shadow-rose-500/30 animate-pulse border border-white/20 select-none">
            {{ $count }}
        </span>
    @endif
</div>
