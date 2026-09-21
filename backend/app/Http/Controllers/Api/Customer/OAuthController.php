<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerOAuthService;
use App\Services\Customer\CustomerSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class OAuthController extends Controller
{
    public function __construct(
        private readonly CustomerOAuthService $oauth,
        private readonly CustomerSessionService $sessions,
    ) {}

    public function redirect(Request $request, string $provider): RedirectResponse|JsonResponse
    {
        if (! $this->isValidProvider($provider)) {
            return response()->json(['message' => 'Unsupported OAuth provider.'], 404);
        }

        if (! $this->oauth->isConfigured($provider)) {
            return response()->json([
                'message' => 'OAuth is not configured for this provider yet.',
                'provider' => $provider,
                'status' => 'configuration_pending',
            ], 503);
        }

        config(["services.{$provider}.redirect" => $this->oauth->redirectUri($provider)]);

        if ($request->boolean('link') && Auth::guard('customer')->check()) {
            $request->session()->put('customer.oauth.link', true);
        } else {
            $request->session()->forget('customer.oauth.link');
        }

        $scopes = $provider === 'facebook' ? ['email', 'public_profile'] : [];

        $driver = Socialite::driver($provider)->stateless(false);

        if ($scopes !== []) {
            $driver->scopes($scopes);
        }

        return $driver->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse|JsonResponse
    {
        $frontend = rtrim((string) (config('seo.site_url') ?: env('SITE_URL', 'http://localhost:3000')), '/');

        if (! $this->isValidProvider($provider)) {
            return redirect()->away("{$frontend}/login?oauth=unsupported");
        }

        if (! $this->oauth->isConfigured($provider)) {
            return redirect()->away("{$frontend}/login?oauth=not_configured&provider={$provider}");
        }

        config(["services.{$provider}.redirect" => $this->oauth->redirectUri($provider)]);

        try {
            $socialUser = Socialite::driver($provider)->stateless(false)->user();
            $linking = (bool) $request->session()->pull('customer.oauth.link', false);
            $authCustomer = Auth::guard('customer')->user();

            if ($linking && $authCustomer) {
                $customer = $this->oauth->link($authCustomer, $provider, $socialUser);
                $this->sessions->notify($customer, 'Social account connected', [
                    ucfirst($provider).' is now connected to your Balaji Royal Events account.',
                ]);
            } else {
                $customer = $this->oauth->findOrCreateFromProvider($provider, $socialUser);
                $this->sessions->login($customer, $request, true);
            }

            $query = 'oauth=success&provider='.$provider;

            return redirect()->away("{$frontend}/account?{$query}");
        } catch (Throwable $e) {
            Log::warning('Customer OAuth callback failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            $code = str_contains(strtolower($e->getMessage()), 'email')
                ? 'email_required'
                : 'failed';

            return redirect()->away("{$frontend}/login?oauth={$code}&provider={$provider}");
        }
    }

    private function isValidProvider(string $provider): bool
    {
        return in_array($provider, CustomerOAuthService::PROVIDERS, true);
    }
}
