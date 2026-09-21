<?php

namespace App\Policies;

use App\Models\EventType;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class EventTypePolicy
{
    use ChecksModulePermission;

    protected static string $module = 'home';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, EventType $eventType): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, EventType $eventType): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, EventType $eventType): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, EventType $eventType): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDelete(User $user, EventType $eventType): bool
    {
        return $this->allows($user, 'delete');
    }
}
