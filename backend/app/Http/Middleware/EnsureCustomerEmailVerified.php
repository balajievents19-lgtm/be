<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = $request->user('customer');

        if ($customer === null) {
            return response()->json(['message' => 'Authentication required.'], 401);
        }

        if ($customer->email_verified_at === null) {
            return response()->json([
                'message' => 'Verify your email address to continue.',
                'code' => 'email_verification_required',
            ], 403);
        }

        return $next($request);
    }
}
