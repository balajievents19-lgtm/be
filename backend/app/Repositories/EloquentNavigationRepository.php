<?php

namespace App\Repositories;

use App\Contracts\NavigationRepository;
use App\Models\NavigationItem;
use Illuminate\Support\Collection;

class EloquentNavigationRepository implements NavigationRepository
{
    public function getHeaderItems(): Collection
    {
        return NavigationItem::query()
            ->visible()
            ->header()
            ->withinPublicationWindow()
            ->ordered()
            ->get();
    }

    public function getFooterItems(): Collection
    {
        return NavigationItem::query()
            ->visible()
            ->footer()
            ->withinPublicationWindow()
            ->ordered()
            ->get();
    }
}
