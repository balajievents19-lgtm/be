<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ForgotPasswordRequest;
use App\Http\Requests\Customer\LoginCustomerRequest;
use App\Http\Requests\Customer\RegisterCustomerRequest;
use App\Http\Requests\Customer\ResetPasswordRequest;
use App\Http\Resources\Api\CustomerResource;
use App\Models\Customer;
use App\Services\Customer\CustomerOAuthService;
use App\Services\Customer\CustomerSessionService;
use App\Services\Customer\EmailVerificationService;
use App\Support\Captcha;
use App\Support\PhoneNumber;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly EmailVerificationService $emails,
        private readonly CustomerSessionService $sessions,
    ) {}

    public function register(RegisterCustomerRequest $request): JsonResponse
    {
        Captcha::assertValid($request);

        $data = $request->validated();
        $username = $data['username'] ?? $this->uniqueUsernameFromEmail($data['email']);

        $customer = Customer::query()->create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'username' => $username,
            'phone' => $data['phone'],
            'password' => $data['password'],
            'email_verified_at' => null,
        ]);

        $meta = $this->emails->send($customer, $request->ip());

        return response()->json([
            'data' => new CustomerResource($customer->fresh()),
            'verification' => [
                'required' => true,
                'sent' => $meta['sent'],
                'masked_email' => $meta['masked_email'],
                'resend_after' => $meta['resend_after'],
            ],
            'message' => 'Check your email for a verification code.',
        ], 201);
    }

    public function login(LoginCustomerRequest $request): JsonResponse
    {
        Captcha::assertValid($request);

        $login = $request->string('login')->toString();
        $password = $request->string('password')->toString();
        $customer = $this->findForLogin($login);

        if (
            $customer === null
            || $customer->password === null
            || ! Hash::check($password, $customer->password)
        ) {
            throw ValidationException::withMessages([
                'login' => ['These credentials do not match our records.'],
            ]);
        }

        if ($customer->email_verified_at === null) {
            return response()->json([
                'message' => 'Verify your email address before signing in.',
                'code' => 'email_verification_required',
                'email' => $customer->email,
            ], 403);
        }

        $this->sessions->login($customer, $request, $request->boolean('remember'));

        return response()->json([
            'data' => new CustomerResource($customer->fresh()),
            'message' => 'Login successful.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->sessions->logoutCurrent($request);

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $this->sessions->record($customer, $request);

        return response()->json([
            'data' => new CustomerResource($customer),
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        Captcha::assertValid($request);

        $generic = 'If that account exists, we sent password reset instructions.';
        $identifier = (string) ($request->input('login') ?: $request->input('email') ?: $request->input('phone'));

        $customer = $this->findForLogin($identifier);

        if ($customer && filled($customer->email)) {
            Password::broker('customers')->sendResetLink(['email' => $customer->email]);
        }

        return response()->json(['message' => $generic]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        Captcha::assertValid($request);

        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Customer $customer, string $password): void {
                $customer->forceFill([
                    'password' => $password,
                    'password_changed_at' => now(),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($customer));
                $this->sessions->notify($customer, 'Your password was reset', [
                    'Your Balaji Royal Events password was changed using a reset link.',
                ]);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => ['This password reset token is invalid or has expired.'],
            ]);
        }

        return response()->json([
            'message' => 'Password has been reset. You can now log in.',
        ]);
    }

    public function oauthProviders(): JsonResponse
    {
        $oauth = app(CustomerOAuthService::class);

        return response()->json([
            'data' => [
                'google' => $oauth->isConfigured('google'),
                'facebook' => $oauth->isConfigured('facebook'),
                'instagram' => false,
            ],
        ]);
    }

    private function findForLogin(string $login): ?Customer
    {
        $login = trim($login);
        if ($login === '') {
            return null;
        }

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return Customer::query()->where('email', strtolower($login))->first();
        }

        $ten = PhoneNumber::localTen($login);
        if ($ten !== null) {
            $byPhone = Customer::query()
                ->where(function ($query) use ($ten): void {
                    $query->where('phone', $ten)
                        ->orWhere('phone', '91'.$ten)
                        ->orWhere('phone', '0'.$ten);
                })
                ->first();
            if ($byPhone) {
                return $byPhone;
            }
        }

        return Customer::query()->where('username', $login)->first();
    }

    private function uniqueUsernameFromEmail(string $email): string
    {
        $base = Str::slug(Str::before($email, '@'), '_');
        $base = $base !== '' ? Str::limit($base, 40, '') : 'customer';
        $candidate = $base;
        $i = 1;
        while (Customer::query()->where('username', $candidate)->exists()) {
            $candidate = $base.'_'.$i;
            $i++;
        }

        return $candidate;
    }
}
