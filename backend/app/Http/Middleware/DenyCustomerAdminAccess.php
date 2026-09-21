<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deny Filament panel access for customer-authenticated sessions.
 * Customers use the customer guard only — never Admin.
 */
class DenyCustomerAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user('customer') !== null && $request->user('web') === null) {
            abort(403, 'Customer accounts cannot access the admin panel.');
        }

        return $next($request);
    }
}
