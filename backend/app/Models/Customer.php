<?php

namespace App\Models;

use App\Notifications\CustomerResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable implements CanResetPasswordContract
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use CanResetPassword, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'phone',
        'password',
        'avatar',
        'email_verified_at',
        'mobile_verified_at',
        'last_login_at',
        'last_login_ip',
        'password_changed_at',
        'pending_email',
        'pending_email_expires_at',
        'pending_email_token_hash',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'pending_email_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'pending_email_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(CustomerSocialAccount::class);
    }

    public function loginSessions(): HasMany
    {
        return $this->hasMany(CustomerSession::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(ContactInquiry::class);
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomerResetPassword($token));
    }
}
