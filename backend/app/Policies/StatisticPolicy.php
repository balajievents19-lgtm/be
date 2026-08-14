<?php

namespace App\Policies;

use App\Models\Statistic;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class StatisticPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'home';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, Statistic $statistic): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, Statistic $statistic): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, Statistic $statistic): bool
    {
        return $this->allows($user, 'delete');
    }
}
