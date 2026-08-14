<?php

namespace App\Policies;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermission;

class NewsletterSubscriberPolicy
{
    use ChecksModulePermission;

    protected static string $module = 'newsletter';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $this->allows($user, 'delete');
    }
}
