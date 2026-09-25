<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function serviceAccesses(): HasMany
    {
        return $this->hasMany(StaffServiceAccess::class);
    }

    public function galleryCategoryAccesses(): HasMany
    {
        return $this->hasMany(StaffGalleryCategoryAccess::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $allowlist = collect(explode(',', (string) config('auth.admin_emails', '')))
            ->map(fn (string $email): string => strtolower(trim($email)))
            ->filter()
            ->values()
            ->all();

        // Empty allowlist: open outside production. Set ADMIN_EMAILS in production.
        if ($allowlist === []) {
            return ! app()->isProduction();
        }

        if (in_array(strtolower((string) $this->email), $allowlist, true)) {
            return true;
        }

        // Role-assigned Admin users may enter the panel; module policies still apply.
        return $this->roles()->exists();
    }
}
