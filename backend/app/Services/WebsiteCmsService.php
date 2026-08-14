<?php

namespace App\Services;

use App\Contracts\NavigationRepository;
use App\DTO\NavigationItemData;
use App\Models\Setting;

class WebsiteCmsService
{
    public function __construct(
        private readonly NavigationRepository $navigationRepository,
    ) {}

    public function settings(): Setting
    {
        return Setting::singleton();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function headerNavigation(): array
    {
        return $this->navigationRepository
            ->getHeaderItems()
            ->map(fn ($item): array => NavigationItemData::fromModel($item)->toArray())
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function footerNavigation(): array
    {
        return $this->navigationRepository
            ->getFooterItems()
            ->map(fn ($item): array => NavigationItemData::fromModel($item)->toArray())
            ->values()
            ->all();
    }
}
