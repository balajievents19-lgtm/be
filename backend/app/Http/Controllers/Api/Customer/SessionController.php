<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerSession;
use App\Services\Customer\CustomerSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    public function __construct(
        private readonly CustomerSessionService $sessions,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $this->sessions->record($customer, $request);
        $currentId = $request->hasSession() ? $request->session()->getId() : null;

        $items = CustomerSession::query()
            ->where('customer_id', $customer->id)
            ->orderByDesc('last_activity_at')
            ->get()
            ->map(function (CustomerSession $session) use ($currentId) {
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $this->summarizeAgent((string) $session->user_agent),
                    'last_activity_at' => $session->last_activity_at,
                    'is_current' => $currentId !== null && hash_equals($currentId, $session->laravel_session_id),
                ];
            });

        return response()->json(['data' => $items]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $loggedOutCurrent = $this->sessions->destroy($customer, $id, $request);

        return response()->json([
            'message' => $loggedOutCurrent ? 'Logged out.' : 'Session ended.',
        ]);
    }

    public function destroyOthers(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $count = $this->sessions->logoutOthers($customer, $request);

        return response()->json([
            'message' => 'Other sessions were signed out.',
            'count' => $count,
        ]);
    }

    private function summarizeAgent(string $agent): string
    {
        $agent = trim($agent);
        if ($agent === '') {
            return 'Unknown device';
        }

        return Str::limit($agent, 80, '…');
    }
}
