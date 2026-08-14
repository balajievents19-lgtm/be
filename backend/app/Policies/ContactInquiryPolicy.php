<?php

namespace App\Policies;

use App\Models\ContactInquiry;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class ContactInquiryPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'leads';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, ContactInquiry $contactInquiry): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, ContactInquiry $contactInquiry): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, ContactInquiry $contactInquiry): bool
    {
        return $this->allows($user, 'delete');
    }
}
