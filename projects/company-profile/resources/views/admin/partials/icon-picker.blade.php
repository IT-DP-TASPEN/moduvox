{{-- Icon Picker Dropdown Component --}}
{{-- Usage: @include('admin.partials.icon-picker', ['fieldName' => 'icon', 'currentIcon' => $service->icon ?? 'build', 'label' => 'Ikon Layanan']) --}}
@php
$icons = [
    // Konstruksi & Engineering
    ['name' => 'engineering', 'label' => 'Engineering'],
    ['name' => 'architecture', 'label' => 'Architecture'],
    ['name' => 'construction', 'label' => 'Construction'],
    ['name' => 'build', 'label' => 'Build'],
    ['name' => 'foundation', 'label' => 'Foundation'],
    ['name' => 'apartment', 'label' => 'Apartment'],
    ['name' => 'domain', 'label' => 'Domain'],
    ['name' => 'home_work', 'label' => 'Home Work'],
    ['name' => 'corporate_fare', 'label' => 'Corporate'],
    ['name' => 'warehouse', 'label' => 'Warehouse'],
    ['name' => 'factory', 'label' => 'Factory'],
    ['name' => 'precision_manufacturing', 'label' => 'Manufacturing'],
    ['name' => 'settings', 'label' => 'Settings'],
    ['name' => 'handyman', 'label' => 'Handyman'],
    ['name' => 'plumbing', 'label' => 'Plumbing'],
    ['name' => 'electrical_services', 'label' => 'Electrical'],
    ['name' => 'hardware', 'label' => 'Hardware'],
    ['name' => 'design_services', 'label' => 'Design'],
    ['name' => 'draw', 'label' => 'Draw'],
    ['name' => 'straighten', 'label' => 'Straighten'],
    // Safety & Quality
    ['name' => 'health_and_safety', 'label' => 'Safety'],
    ['name' => 'verified', 'label' => 'Verified'],
    ['name' => 'shield', 'label' => 'Shield'],
    ['name' => 'security', 'label' => 'Security'],
    ['name' => 'gpp_good', 'label' => 'Good'],
    // Transportasi & Logistik
    ['name' => 'local_shipping', 'label' => 'Shipping'],
    ['name' => 'inventory_2', 'label' => 'Inventory'],
    ['name' => 'package_2', 'label' => 'Package'],
    // Sosial Media & Komunikasi
    ['name' => 'share', 'label' => 'Share'],
    ['name' => 'hub', 'label' => 'Hub'],
    ['name' => 'language', 'label' => 'Language/Web'],
    ['name' => 'groups', 'label' => 'Groups'],
    ['name' => 'forum', 'label' => 'Forum'],
    ['name' => 'chat', 'label' => 'Chat'],
    ['name' => 'mail', 'label' => 'Mail'],
    ['name' => 'call', 'label' => 'Call'],
    ['name' => 'videocam', 'label' => 'Video'],
    ['name' => 'photo_camera', 'label' => 'Camera'],
    ['name' => 'tag', 'label' => 'Tag'],
    ['name' => 'link', 'label' => 'Link'],
    ['name' => 'public', 'label' => 'Public'],
    ['name' => 'diversity_3', 'label' => 'Diversity'],
    ['name' => 'campaign', 'label' => 'Campaign'],
    ['name' => 'thumb_up', 'label' => 'Like'],
    ['name' => 'favorite', 'label' => 'Favorite'],
    ['name' => 'star', 'label' => 'Star'],
    // Bisnis
    ['name' => 'work', 'label' => 'Work'],
    ['name' => 'business_center', 'label' => 'Business'],
    ['name' => 'handshake', 'label' => 'Handshake'],
    ['name' => 'trending_up', 'label' => 'Trending'],
    ['name' => 'payments', 'label' => 'Payments'],
    ['name' => 'analytics', 'label' => 'Analytics'],
];
$selectedIcon = old($fieldName ?? 'icon', $currentIcon ?? 'build');
$fieldId = ($fieldName ?? 'icon') . '_picker_' . rand(100,999);
@endphp

<div>
    <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $label ?? 'Pilih Ikon *' }}</label>
    <div class="flex gap-4 items-start">
        <div class="flex-1 relative" x-data="{ open: false, search: '', selected: '{{ $selectedIcon }}' }">
            {{-- Hidden input --}}
            <input type="hidden" name="{{ $fieldName ?? 'icon' }}" :value="selected">

            {{-- Trigger Button --}}
            <button type="button" @click="open = !open"
                    class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm text-left flex items-center justify-between gap-3 focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white hover:bg-slate-50 transition-colors">
                <span class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-red-600 text-xl" x-text="selected"></span>
                    <span class="text-slate-700 font-medium" x-text="selected"></span>
                </span>
                <span class="material-symbols-outlined text-slate-400 text-lg transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
            </button>

            {{-- Dropdown Panel --}}
            <div x-show="open" @click.outside="open = false" x-cloak x-transition
                 class="absolute z-50 mt-2 w-full bg-white border border-slate-200 rounded-xl shadow-2xl max-h-80 overflow-hidden">
                {{-- Search --}}
                <div class="p-3 border-b border-slate-100 sticky top-0 bg-white z-10">
                    <input type="text" x-model="search" placeholder="Cari ikon..." autofocus
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                {{-- Icons Grid --}}
                <div class="p-3 overflow-y-auto max-h-60 grid grid-cols-4 gap-2">
                    @foreach($icons as $icon)
                    <button type="button"
                            x-show="'{{ $icon['name'] }}'.includes(search.toLowerCase()) || '{{ strtolower($icon['label']) }}'.includes(search.toLowerCase())"
                            @click="selected = '{{ $icon['name'] }}'; open = false"
                            :class="selected === '{{ $icon['name'] }}' ? 'bg-red-50 border-red-300 ring-2 ring-red-200' : 'bg-slate-50 border-transparent hover:bg-slate-100'"
                            class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-all">
                        <span class="material-symbols-outlined text-xl" :class="selected === '{{ $icon['name'] }}' ? 'text-red-600' : 'text-slate-600'">{{ $icon['name'] }}</span>
                        <span class="text-[9px] font-medium text-slate-500 leading-tight text-center truncate w-full">{{ $icon['label'] }}</span>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Live Preview --}}
        <div class="w-16 h-12 bg-slate-900 border border-slate-700 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-white text-2xl" x-text="selected">{{ $selectedIcon }}</span>
        </div>
    </div>
    @error($fieldName ?? 'icon')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>

