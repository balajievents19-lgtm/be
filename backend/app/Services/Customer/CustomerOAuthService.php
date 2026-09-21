<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Models\CustomerSocialAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use RuntimeException;

final class CustomerOAuthService
{
    /** @var list<string> */
    public const PROVIDERS = ['google', 'facebook'];

    public function isConfigured(string $provider): bool
    {
        if (! in_array($provider, self::PROVIDERS, true)) {
            return false;
        }

        $id = trim((string) config("services.{$provider}.client_id"));
        $secret = trim((string) config("services.{$provider}.client_secret"));

        return $id !== '' && $secret !== '';
    }

    public function redirectUri(string $provider): string
    {
        $configured = trim((string) config("services.{$provider}.redirect"));
        if ($configured !== '') {
            return $configured;
        }

        return rtrim((string) config('app.url'), '/').'/api/customer/oauth/'.$provider.'/callback';
    }

    /**
     * @param  'google'|'facebook'  $provider
     */
    public function findOrCreateFromProvider(string $provider, SocialiteUser $socialUser): Customer
    {
        $providerId = (string) $socialUser->getId();
        if ($providerId === '') {
            throw new RuntimeException('OAuth provider did not return a stable user id.');
        }

        $existingSocial = CustomerSocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', $providerId)
            ->first();

        if ($existingSocial !== null) {
            $this->syncSocialAccount($existingSocial, $socialUser);

            return $existingSocial->customer;
        }

        $email = $this->resolvedEmail($socialUser);

        return DB::transaction(function () use ($provider, $socialUser, $providerId, $email): Customer {
            $customer = null;

            if ($email !== null) {
                $customer = Customer::query()->where('email', $email)->first();
            }

            if ($customer !== null && $customer->email_verified_at === null) {
                throw new RuntimeException(
                    'This email is already registered but not verified. Sign in with email/password first, then connect this social account.'
                );
            }

            if ($customer === null) {
                if ($email === null) {
                    throw new RuntimeException(
                        'This social account did not provide a verified email. Please register with email/password first, then link social login.'
                    );
                }

                $customer = Customer::query()->create([
                    'name' => $socialUser->getName() ?: ($socialUser->getNickname() ?: Str::before($email, '@')),
                    'email' => $email,
                    'username' => $this->uniqueUsernameFrom($email, $socialUser->getNickname()),
                    'password' => null,
                    'avatar' => $socialUser->getAvatar(),
                    'email_verified_at' => now(),
                    'mobile_verified_at' => null,
                ]);
            } else {
                if ($customer->avatar === null && $socialUser->getAvatar()) {
                    $customer->forceFill(['avatar' => $socialUser->getAvatar()])->save();
                }
            }

            $this->attach($customer, $provider, $socialUser, $providerId);

            return $customer->fresh();
        });
    }

    /**
     * @param  'google'|'facebook'  $provider
     */
    public function link(Customer $customer, string $provider, SocialiteUser $socialUser): Customer
    {
        $providerId = (string) $socialUser->getId();
        if ($providerId === '') {
            throw new RuntimeException('OAuth provider did not return a stable user id.');
        }

        $existing = CustomerSocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', $providerId)
            ->first();

        if ($existing !== null && $existing->customer_id !== $customer->id) {
            throw new RuntimeException('This social account is already connected to another customer.');
        }

        if ($existing !== null) {
            $this->syncSocialAccount($existing, $socialUser);

            return $customer->fresh();
        }

        $this->attach($customer, $provider, $socialUser, $providerId);

        return $customer->fresh();
    }

    private function attach(Customer $customer, string $provider, SocialiteUser $socialUser, string $providerId): void
    {
        CustomerSocialAccount::query()->create([
            'customer_id' => $customer->id,
            'provider' => $provider,
            'provider_user_id' => $providerId,
            'provider_email' => $socialUser->getEmail(),
            'provider_name' => $socialUser->getName(),
            'avatar' => $socialUser->getAvatar(),
        ]);
    }

    private function syncSocialAccount(CustomerSocialAccount $account, SocialiteUser $socialUser): void
    {
        $account->forceFill([
            'provider_email' => $socialUser->getEmail(),
            'provider_name' => $socialUser->getName(),
            'avatar' => $socialUser->getAvatar(),
        ])->save();
    }

    private function resolvedEmail(SocialiteUser $socialUser): ?string
    {
        $email = $socialUser->getEmail();
        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }

    private function uniqueUsernameFrom(string $email, ?string $nickname): string
    {
        $base = Str::slug((string) ($nickname ?: Str::before($email, '@')), '_');
        $base = $base !== '' ? $base : 'customer';
        $base = Str::limit($base, 40, '');

        $candidate = $base;
        $i = 1;
        while (Customer::query()->where('username', $candidate)->exists()) {
            $candidate = $base.'_'.$i;
            $i++;
        }

        return $candidate;
    }
}
