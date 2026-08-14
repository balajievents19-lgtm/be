<?php

namespace App\Policies;

use App\Models\FaqCategory;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class FaqCategoryPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'faq';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, FaqCategory $faqCategory): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, FaqCategory $faqCategory): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, FaqCategory $faqCategory): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, FaqCategory $faqCategory): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDelete(User $user, FaqCategory $faqCategory): bool
    {
        return $this->allows($user, 'delete');
    }
}
