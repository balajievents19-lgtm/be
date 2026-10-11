<?php

namespace App\Policies;

use App\Models\EstimateCatalogueItem;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class EstimateCatalogueItemPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'estimates';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, EstimateCatalogueItem $estimateCatalogueItem): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, EstimateCatalogueItem $estimateCatalogueItem): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, EstimateCatalogueItem $estimateCatalogueItem): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, EstimateCatalogueItem $estimateCatalogueItem): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDelete(User $user, EstimateCatalogueItem $estimateCatalogueItem): bool
    {
        return $this->allows($user, 'delete');
    }
}
