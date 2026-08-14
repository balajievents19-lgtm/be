<x-filament-widgets::widget>
    @php
        $lists = $this->getLists();
        $recent = $lists['recent'];
        $overdue = $lists['overdue'];
        $upcoming = $lists['upcoming'];
        $leadsUrl = $lists['leadsUrl'];
    @endphp

    <div class="grid gap-6 xl:grid-cols-3">
        <x-filament::section class="xl:col-span-1">
            <x-slot name="heading">Recent Leads</x-slot>
            <x-slot name="description">Newest enquiries in the selected period.</x-slot>
            <x-slot name="headerEnd">
                <x-filament::link :href="$leadsUrl" size="sm">
                    View Leads
                </x-filament::link>
            </x-slot>

            @if ($recent->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No recent Leads.</p>
            @else
                <ul class="divide-y divide-gray-200 dark:divide-white/10">
                    @foreach ($recent as $lead)
                        <li class="py-3">
                            <a
                                href="{{ $this->leadUrl($lead->id) }}"
                                class="block text-sm no-underline hover:opacity-80"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-medium text-gray-950 dark:text-white">{{ $lead->name }}</div>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $lead->mobile }}
                                            @if (filled($lead->service_interested))
                                                · {{ $lead->service_interested }}
                                            @endif
                                            @if (filled($lead->source))
                                                · {{ $lead->source }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right text-xs text-gray-500 dark:text-gray-400">
                                        <div>{{ $lead->status?->label() ?? $lead->status }}</div>
                                        <div>{{ $lead->priority?->label() ?? $lead->priority }}</div>
                                        <div>{{ $lead->created_at?->format('d M Y') }}</div>
                                    </div>
                                </div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Assigned: {{ $lead->assignee?->name ?? 'Unassigned' }}
                                    · Follow-up: {{ $lead->follow_up_at?->format('d M Y H:i') ?? '—' }}
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Overdue Follow-ups</x-slot>
            <x-slot name="description">follow_up_at is in the past.</x-slot>

            @if ($overdue->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No overdue follow-ups.</p>
            @else
                <ul class="divide-y divide-gray-200 dark:divide-white/10">
                    @foreach ($overdue as $lead)
                        <li class="py-3">
                            <a href="{{ $this->leadUrl($lead->id) }}" class="block text-sm no-underline hover:opacity-80">
                                <div class="font-medium text-gray-950 dark:text-white">{{ $lead->name }}</div>
                                <div class="mt-1 text-xs text-danger-600 dark:text-danger-400">
                                    Due {{ $lead->follow_up_at?->format('d M Y H:i') }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $lead->assignee?->name ?? 'Unassigned' }}
                                    @if (filled($lead->service_interested))
                                        · {{ $lead->service_interested }}
                                    @endif
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Upcoming Follow-ups</x-slot>
            <x-slot name="description">follow_up_at is now or later.</x-slot>

            @if ($upcoming->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming follow-ups.</p>
            @else
                <ul class="divide-y divide-gray-200 dark:divide-white/10">
                    @foreach ($upcoming as $lead)
                        <li class="py-3">
                            <a href="{{ $this->leadUrl($lead->id) }}" class="block text-sm no-underline hover:opacity-80">
                                <div class="font-medium text-gray-950 dark:text-white">{{ $lead->name }}</div>
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                    {{ $lead->follow_up_at?->format('d M Y H:i') }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $lead->assignee?->name ?? 'Unassigned' }}
                                    @if (filled($lead->service_interested))
                                        · {{ $lead->service_interested }}
                                    @endif
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
