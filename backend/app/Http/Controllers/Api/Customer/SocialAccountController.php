<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerSocialAccount;
use App\Services\Customer\CustomerSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SocialAccountController extends Controller
{
    public function __construct(
        private readonly CustomerSessionService $sessions,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $accounts = $customer->socialAccounts()
            ->orderBy('provider')
            ->get(['id', 'provider', 'provider_email', 'provider_name', 'created_at']);

        return response()->json(['data' => $accounts]);
    }

    public function destroy(Request $request, string $provider): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        if (! in_array($provider, ['google', 'facebook'], true)) {
            abort(404);
        }

        if (! $this->sessions->recentlyConfirmed($request)) {
            throw ValidationException::withMessages([
                'password' => ['Confirm your password before disconnecting a social account.'],
            ]);
        }

        $deleted = CustomerSocialAccount::query()
            ->where('customer_id', $customer->id)
            ->where('provider', $provider)
            ->delete();

        if ($deleted) {
            $this->sessions->notify($customer, 'Social account disconnected', [
                ucfirst($provider).' is no longer connected to your Balaji Royal Events account.',
            ]);
        }

        return response()->json(['message' => 'Social account disconnected.']);
    }
}
