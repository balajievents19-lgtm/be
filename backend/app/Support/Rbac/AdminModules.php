<?php

namespace App\Support\Rbac;

final class AdminModules
{
    public const GUARD = 'web';

    public const ROLE_SUPER_ADMIN = 'Super Admin';

    public const ROLE_CONTENT_MANAGER = 'Content Manager';

    public const ROLE_LEAD_MANAGER = 'Lead Manager';

    public const ROLE_NEWSLETTER_MANAGER = 'Newsletter Manager';

    /**
     * Specialist identities. Permissions are assigned per user via Content Access, not role name.
     *
     * @var list<string>
     */
    public const SPECIALIST_ROLES = [
        'Photographer',
        'Tent Manager',
        'Wedding Planner',
        'Event Manager',
        'Decorator',
        'DJ/Sound Manager',
        'Catering Manager',
        'Bridal/Mehndi Manager',
        'SFX Manager',
    ];

    /**
     * @var list<string>
     */
    public const WEBSITE = [
        'home',
        'header',
        'slider',
        'about',
        'services',
        'gallery',
        'packages',
        'testimonials',
        'blog',
        'faq',
        'contact',
        'footer',
        'seo',
    ];

    /**
     * @var list<string>
     */
    public const CRM = [
        'leads',
        'newsletter',
    ];

    /**
     * @var list<string>
     */
    public const USERS = [
        'users',
    ];

    /**
     * @var list<string>
     */
    public const ACTIONS = [
        'view',
        'create',
        'update',
        'delete',
    ];

    /**
     * @return list<string>
     */
    public static function allModules(): array
    {
        return [...self::WEBSITE, ...self::CRM, ...self::USERS];
    }

    /**
     * @return list<string>
     */
    public static function allPermissions(): array
    {
        $permissions = [];

        foreach (self::allModules() as $module) {
            foreach (self::ACTIONS as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        return $permissions;
    }

    /**
     * @return list<string>
     */
    public static function websitePermissions(): array
    {
        $permissions = [];

        foreach (self::WEBSITE as $module) {
            foreach (self::ACTIONS as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        return $permissions;
    }

    /**
     * @return list<string>
     */
    public static function leadManagerPermissions(): array
    {
        return [
            'leads.view',
            'leads.create',
            'leads.update',
        ];
    }

    /**
     * @return list<string>
     */
    public static function newsletterManagerPermissions(): array
    {
        return [
            'newsletter.view',
            'newsletter.create',
            'newsletter.update',
        ];
    }

    public static function permission(string $module, string $action): string
    {
        return "{$module}.{$action}";
    }
}
