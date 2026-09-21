<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Models\CustomerSession;
use App\Notifications\CustomerSecurityAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

final class CustomerSessionService
{
    public function login(Customer $customer, Request $request, bool $remember = false): void
    {
        Auth::guard('customer')->login($customer, $remember);

        if ($request->hasSession()) {
            $request->session()->regenerate();
            $this->record($customer, $request);
            $request->session()->put('customer.auth.confirmed_at', now()->timestamp);
        }

        $customer->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $this->notify($customer, 'New login to your account', [
            'We noticed a new sign-in to your Balaji Royal Events account.',
        ]);
    }

    public function record(Customer $customer, Request $request): void
    {
        if (! $request->hasSession()) {
            return;
        }

        CustomerSession::query()->updateOrCreate(
            [
                'customer_id' => $customer->id,
                'laravel_session_id' => $request->session()->getId(),
            ],
            [
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                'last_activity_at' => now(),
            ]
        );
    }

    public function logoutCurrent(Request $request): void
    {
        $customer = $request->user('customer');

        if ($request->hasSession() && $customer) {
            CustomerSession::query()
                ->where('customer_id', $customer->id)
                ->where('laravel_session_id', $request->session()->getId())
                ->delete();
        }

        Auth::guard('customer')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }

    public function logoutOthers(Customer $customer, Request $request): int
    {
        $current = $request->hasSession() ? $request->session()->getId() : null;

        $query = CustomerSession::query()->where('customer_id', $customer->id);
        if ($current) {
            $query->where('laravel_session_id', '!=', $current);
        }

        $others = $query->get();
        $ids = $others->pluck('laravel_session_id')->filter()->values()->all();

        if ($ids !== [] && Schema::hasTable('sessions')) {
            DB::table('sessions')->whereIn('id', $ids)->delete();
        }

        CustomerSession::query()->whereIn('id', $others->pluck('id'))->delete();

        return $others->count();
    }

    public function destroy(Customer $customer, string $sessionUuid, Request $request): bool
    {
        $row = CustomerSession::query()
            ->where('customer_id', $customer->id)
            ->where('id', $sessionUuid)
            ->firstOrFail();

        $currentId = $request->hasSession() ? $request->session()->getId() : null;
        $isCurrent = $currentId !== null && hash_equals($currentId, $row->laravel_session_id);

        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('id', $row->laravel_session_id)->delete();
        }

        $row->delete();

        if ($isCurrent) {
            $this->logoutCurrent($request);
        }

        return $isCurrent;
    }

    public function recentlyConfirmed(Request $request): bool
    {
        if (! $request->hasSession()) {
            return false;
        }

        $confirmed = (int) $request->session()->get('customer.auth.confirmed_at', 0);

        return $confirmed >= now()->timestamp - 900;
    }

    public function confirmPassword(Request $request): void
    {
        if ($request->hasSession()) {
            $request->session()->put('customer.auth.confirmed_at', now()->timestamp);
        }
    }

    public function notify(Customer $customer, string $title, array $lines): void
    {
        if (! filled($customer->email)) {
            return;
        }

        try {
            $customer->notify(new CustomerSecurityAlert($title, $lines));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
