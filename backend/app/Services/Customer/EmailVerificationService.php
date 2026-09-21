<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Models\CustomerOtp;
use App\Notifications\CustomerVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

final class EmailVerificationService
{
    /** @var array<string, string> */
    private array $lastCodes = [];

    /**
     * @return array{masked_email: string, resend_after: int, sent: bool}
     */
    public function send(Customer $customer, ?string $ip = null): array
    {
        $email = strtolower((string) $customer->email);
        $cooldown = (int) config('otp.resend_seconds', 60);
        $existing = $this->activeOtp($email);

        if ($existing && $existing->last_sent_at?->gt(now()->subSeconds($cooldown))) {
            throw ValidationException::withMessages([
                'email' => ['Please wait before requesting another verification email.'],
            ]);
        }

        if ($existing) {
            $existing->forceFill(['consumed_at' => now()])->save();
        }

        $code = $this->generateCode();
        $this->rememberForTests($email, $code);

        CustomerOtp::query()->create([
            'customer_id' => $customer->id,
            'purpose' => CustomerOtp::PURPOSE_REGISTER,
            'phone' => 'email',
            'email' => $email,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'max_attempts' => (int) config('otp.max_attempts', 5),
            'channel' => 'email',
            'ip_address' => $ip,
            'expires_at' => now()->addMinutes((int) config('otp.expiry_minutes', 10)),
            'last_sent_at' => now(),
        ]);

        $customer->notify(new CustomerVerifyEmail($code, $this->signedUrl($customer)));

        return [
            'masked_email' => $this->maskEmail($email),
            'resend_after' => $cooldown,
            'sent' => true,
        ];
    }

    public function verifyCode(string $email, string $code): Customer
    {
        $email = strtolower(trim($email));
        $otp = $this->activeOtp($email);

        if ($otp === null) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired verification code.'],
            ]);
        }

        if ($otp->attempts >= $otp->max_attempts) {
            $otp->forceFill(['consumed_at' => now()])->save();
            throw ValidationException::withMessages([
                'otp' => ['Too many attempts. Request a new verification email.'],
            ]);
        }

        $otp->increment('attempts');
        $otp->refresh();

        if (! Hash::check($code, $otp->code_hash)) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired verification code.'],
            ]);
        }

        $otp->forceFill(['consumed_at' => now()])->save();

        $customer = $otp->customer ?? Customer::query()->where('email', $email)->first();
        if ($customer === null) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired verification code.'],
            ]);
        }

        $this->markVerified($customer);

        return $customer->fresh();
    }

    public function verifySigned(int $id, string $hash): Customer
    {
        $customer = Customer::query()->find($id);
        if ($customer === null || ! hash_equals(sha1($customer->email), $hash)) {
            throw ValidationException::withMessages([
                'email' => ['This verification link is invalid or has expired.'],
            ]);
        }

        $this->markVerified($customer);
        CustomerOtp::query()
            ->where('purpose', CustomerOtp::PURPOSE_REGISTER)
            ->where('email', strtolower($customer->email))
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        return $customer->fresh();
    }

    public function lastCode(string $email): ?string
    {
        return $this->lastCodes[strtolower($email)] ?? null;
    }

    public function maskEmail(string $email): string
    {
        $email = strtolower(trim($email));
        $at = strpos($email, '@');
        if ($at === false) {
            return '****';
        }

        $local = substr($email, 0, $at);
        $domain = substr($email, $at);
        $keep = min(2, strlen($local));

        return substr($local, 0, $keep).str_repeat('*', max(1, strlen($local) - $keep)).$domain;
    }

    public function signedUrl(Customer $customer): string
    {
        return URL::temporarySignedRoute(
            'api.customer.email.verify-link',
            now()->addMinutes((int) config('otp.expiry_minutes', 10)),
            [
                'id' => $customer->id,
                'hash' => sha1($customer->email),
            ],
        );
    }

    private function markVerified(Customer $customer): void
    {
        if ($customer->email_verified_at === null) {
            $customer->forceFill(['email_verified_at' => now()])->save();
        }
    }

    private function activeOtp(string $email): ?CustomerOtp
    {
        return CustomerOtp::query()
            ->where('purpose', CustomerOtp::PURPOSE_REGISTER)
            ->where('email', strtolower($email))
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
    }

    private function generateCode(): string
    {
        $length = max(4, (int) config('otp.length', 6));
        $max = (10 ** $length) - 1;

        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    private function rememberForTests(string $email, string $code): void
    {
        if (app()->environment('testing')) {
            $this->lastCodes[$email] = $code;
        }
    }
}
