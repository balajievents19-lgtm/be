<x-filament-widgets::widget>
    @php
        $breakdowns = $this->getBreakdowns();
        $sources = $breakdowns['sources'];
        $interests = $breakdowns['interests'];
        $assignments = $breakdowns['assignments'];
    @endphp

    <div class="grid gap-6 md:grid-cols-3">
        <x-filament::section>
            <x-slot name="heading">Lead sources</x-slot>
            <x-slot name="description">Where enquiries came from in the selected period.</x-slot>

            @if ($sources->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No Lead sources in this period.</p>
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($sources as $source => $count)
                        <li class="flex items-center justify-between gap-3">
                            <span class="font-medium text-gray-950 dark:text-white">{{ $source }}</span>
                            <span class="text-gray-600 dark:text-gray-300">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Services / Packages</x-slot>
            <x-slot name="description">Top enquiry interests from existing Lead data.</x-slot>

            @if ($interests->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No service or package interests in this period.</p>
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($interests as $row)
                        <li class="flex items-start justify-between gap-3">
                            <span>
                                <span class="font-medium text-gray-950 dark:text-white">{{ $row->name }}</span>
                                @if (filled($row->source))
                                    <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">{{ $row->source }}</span>
                                @endif
                            </span>
                            <span class="text-gray-600 dark:text-gray-300">{{ $row->aggregate }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Assigned workload</x-slot>
            <x-slot name="description">Lead ownership in the selected period.</x-slot>

            @if ($assignments->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No assigned Leads in this period.</p>
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($assignments as $row)
                        <li class="flex items-center justify-between gap-3">
                            <span class="font-medium text-gray-950 dark:text-white">{{ $row->label }}</span>
                            <span class="text-gray-600 dark:text-gray-300">{{ $row->aggregate }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
