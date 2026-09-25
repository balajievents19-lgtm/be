<x-filament-panels::page>
    <p class="fi-section-header-description text-sm text-gray-500">
        Review staff-created gallery media before it can appear on the website or Android app. Only Super Admin can approve or reject.
    </p>

    {{ $this->table }}

    @php($external = $this->pendingExternalMedia())
    @if ($external !== [])
        <div class="mt-8">
            <h2 class="text-lg font-semibold mb-3">External media queue</h2>
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th class="p-3">Title</th>
                            <th class="p-3">Type</th>
                            <th class="p-3">Category</th>
                            <th class="p-3">Service</th>
                            <th class="p-3">Uploaded by</th>
                            <th class="p-3">Status</th>
                            <th class="p-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($external as $item)
                            <tr class="border-t border-gray-100 dark:border-white/10">
                                <td class="p-3">{{ $item->title }}</td>
                                <td class="p-3">{{ $item->provider }} / {{ $item->media_type }}</td>
                                <td class="p-3">{{ $item->category?->name ?? '—' }}</td>
                                <td class="p-3">{{ $item->service?->name ?? '—' }}</td>
                                <td class="p-3">{{ $item->creator?->name ?? '—' }}</td>
                                <td class="p-3">{{ $item->moderation_status }}{{ $item->brand_review_required ? ' (brand)' : '' }}</td>
                                <td class="p-3 space-x-2">
                                    <x-filament::button size="sm" color="success" wire:click="approveExternal({{ $item->id }})">Approve</x-filament::button>
                                    <x-filament::button size="sm" color="danger" wire:click="rejectExternal({{ $item->id }})">Reject</x-filament::button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>
