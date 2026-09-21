<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ChangeEmailRequest;
use App\Http\Requests\Customer\ChangeMobileRequest;
use App\Http\Requests\Customer\ChangePasswordRequest;
use App\Http\Requests\Customer\ConfirmPasswordRequest;
use App\Http\Resources\Api\CustomerResource;
use App\Models\Customer;
use App\Notifications\CustomerVerifyEmailChange;
use App\Services\Customer\CustomerSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountSecurityController extends Controller
{
    public function __construct(
        private readonly CustomerSessionService $sessions,
    ) {}

    public function confirmPassword(ConfirmPasswordRequest $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        if ($customer->password === null || ! Hash::check($request->string('password')->toString(), $customer->password)) {
            throw ValidationException::withMessages([
                'password' => ['The current password is incorrect.'],
            ]);
        }

        $this->sessions->confirmPassword($request);

        return response()->json(['message' => 'Password confirmed.']);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $current = $request->string('current_password')->toString();
        $new = $request->string('password')->toString();

        if ($customer->password === null || ! Hash::check($current, $customer->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        if (Hash::check($new, $customer->password)) {
            throw ValidationException::withMessages([
                'password' => ['Choose a password you have not used before.'],
            ]);
        }

        $customer->forceFill([
            'password' => $new,
            'password_changed_at' => now(),
            'remember_token' => Str::random(60),
        ])->save();

        $this->sessions->confirmPassword($request);
        $this->sessions->logoutOthers($customer, $request);
        $this->sessions->notify($customer, 'Your password was changed', [
            'The password on your Balaji Royal Events account was updated.',
        ]);

        return response()->json(['message' => 'Password updated.']);
    }

    public function changeMobile(ChangeMobileRequest $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        if ($customer->password === null || ! Hash::check($request->string('current_password')->toString(), $customer->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $customer->forceFill(['phone' => $request->validated('phone')])->save();
        $this->sessions->confirmPassword($request);
        $this->sessions->notify($customer, 'Mobile number updated', [
            'The mobile number on your Balaji Royal Events account was updated.',
        ]);

        return response()->json([
            'message' => 'Mobile number updated.',
            'data' => new CustomerResource($customer->fresh()),
        ]);
    }

    public function changeEmail(ChangeEmailRequest $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        if ($customer->password === null || ! Hash::check($request->string('current_password')->toString(), $customer->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $email = strtolower($request->string('email')->toString());
        $token = Str::random(64);

        $customer->forceFill([
            'pending_email' => $email,
            'pending_email_expires_at' => now()->addHour(),
            'pending_email_token_hash' => hash('sha256', $token),
        ])->save();

        $frontend = rtrim((string) (config('seo.site_url') ?: env('SITE_URL', 'http://localhost:3001')), '/');
        $url = $frontend.'/account?verify_email='.$token;

        Notification::route('mail', $email)
            ->notify(new CustomerVerifyEmailChange($url));

        $this->sessions->confirmPassword($request);

        return response()->json([
            'message' => 'Check the new email address for a confirmation link.',
            'data' => new CustomerResource($customer->fresh()),
        ]);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $token = $request->string('token')->toString();

        if (
            $token === ''
            || $customer->pending_email === null
            || $customer->pending_email_token_hash === null
            || $customer->pending_email_expires_at === null
            || $customer->pending_email_expires_at->isPast()
            || ! hash_equals($customer->pending_email_token_hash, hash('sha256', $token))
        ) {
            throw ValidationException::withMessages([
                'token' => ['This email confirmation link is invalid or has expired.'],
            ]);
        }

        $old = $customer->email;
        $customer->forceFill([
            'email' => $customer->pending_email,
            'email_verified_at' => now(),
            'pending_email' => null,
            'pending_email_expires_at' => null,
            'pending_email_token_hash' => null,
        ])->save();

        $this->sessions->notify($customer, 'Your email address was changed', [
            'The email on your Balaji Royal Events account was updated.',
        ]);

        return response()->json([
            'data' => new CustomerResource($customer->fresh()),
            'message' => 'Email address updated.',
            'previous_email' => $old,
        ]);
    }
}
