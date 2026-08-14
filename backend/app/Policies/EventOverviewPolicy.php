<?php

namespace App\Policies;

use App\Models\EventOverview;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class EventOverviewPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'home';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, EventOverview $eventOverview): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, EventOverview $eventOverview): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, EventOverview $eventOverview): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, EventOverview $eventOverview): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDelete(User $user, EventOverview $eventOverview): bool
    {
        return $this->allows($user, 'delete');
    }
}
