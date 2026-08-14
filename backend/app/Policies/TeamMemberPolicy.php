<?php

namespace App\Policies;

use App\Models\TeamMember;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class TeamMemberPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'about';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, TeamMember $teamMember): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, TeamMember $teamMember): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, TeamMember $teamMember): bool
    {
        return $this->allows($user, 'delete');
    }
}
