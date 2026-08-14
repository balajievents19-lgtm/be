<?php

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class FaqPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'faq';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, Faq $faq): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, Faq $faq): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, Faq $faq): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, Faq $faq): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDelete(User $user, Faq $faq): bool
    {
        return $this->allows($user, 'delete');
    }
}
