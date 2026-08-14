<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Quick actions
        </x-slot>

        <x-slot name="description">
            Shortcuts for authorized Admin work.
        </x-slot>

        <div class="flex flex-wrap gap-3">
            @foreach ($this->getLinks() as $link)
                <x-filament::button
                    tag="a"
                    :href="$link['url']"
                    color="gray"
                >
                    {{ $link['label'] }}
                </x-filament::button>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
