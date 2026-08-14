<?php

namespace App\Contracts;

use App\Models\NavigationItem;
use Illuminate\Support\Collection;

interface NavigationRepository
{
    /**
     * @return Collection<int, NavigationItem>
     */
    public function getHeaderItems(): Collection;

    /**
     * @return Collection<int, NavigationItem>
     */
    public function getFooterItems(): Collection;
}
