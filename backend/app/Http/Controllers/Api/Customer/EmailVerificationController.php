<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ResendEmailVerificationRequest;
use App\Http\Requests\Customer\VerifyEmailOtpRequest;
use App\Http\Resources\Api\CustomerResource;
use App\Models\Customer;
use App\Services\Customer\CustomerSessionService;
use App\Services\Customer\EmailVerificationService;
use App\Support\Captcha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly EmailVerificationService $emails,
        private readonly CustomerSessionService $sessions,
    ) {}

    public function verify(VerifyEmailOtpRequest $request): JsonResponse
    {
        Captcha::assertValid($request);

        $customer = $this->emails->verifyCode(
            $request->string('email')->toString(),
            $request->string('otp')->toString(),
        );

        $this->sessions->login($customer, $request);

        return response()->json([
            'data' => new CustomerResource($customer->fresh()),
            'message' => 'Email verified. You are now signed in.',
        ]);
    }

    public function resend(ResendEmailVerificationRequest $request): JsonResponse
    {
        Captcha::assertValid($request);

        $email = strtolower($request->string('email')->toString());
        $customer = Customer::query()
            ->where('email', $email)
            ->whereNull('email_verified_at')
            ->first();

        if ($customer !== null) {
            $meta = $this->emails->send($customer, $request->ip());

            return response()->json([
                'message' => 'A verification email was sent.',
                'verification' => [
                    'masked_email' => $meta['masked_email'],
                    'resend_after' => $meta['resend_after'],
                    'sent' => true,
                ],
            ]);
        }

        return response()->json([
            'message' => 'If this email can be verified, a message was sent.',
            'verification' => [
                'masked_email' => $this->emails->maskEmail($email),
                'resend_after' => (int) config('otp.resend_seconds', 60),
                'sent' => false,
            ],
        ]);
    }

    public function verifyLink(Request $request, int $id, string $hash): RedirectResponse
    {
        $frontend = rtrim((string) (config('seo.site_url') ?: env('SITE_URL', 'http://localhost:3000')), '/');

        try {
            $this->emails->verifySigned($id, $hash);
        } catch (\Throwable) {
            return redirect()->away($frontend.'/login?verified=0');
        }

        return redirect()->away($frontend.'/login?verified=1');
    }
}
