<x-filament::page>
    {{-- Selalu tampilkan AccountWidget --}}
    <div class="mb-6">
        @livewire(\App\Filament\Widgets\UserLoginWidget::class)
    </div>

    {{-- Dropdown dan Widget sesuai pilihan --}}
    <div class="space-y-6">
        <div class="max-w-xxl">
            {{ $this->form }}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($this->getRenderedWidget() as $widgetClass)
                @livewire($widgetClass, [], key($widgetClass))
            @endforeach
        </div>
    </div>
</x-filament::page>