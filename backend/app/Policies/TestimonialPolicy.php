<?php

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class TestimonialPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'testimonials';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, Testimonial $testimonial): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, Testimonial $testimonial): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDelete(User $user, Testimonial $testimonial): bool
    {
        return $this->allows($user, 'delete');
    }
}
