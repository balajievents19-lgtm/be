<?php

namespace App\Http\Controllers\Api;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactInquiryRequest;
use App\Http\Resources\Api\ContactInquiryResource;
use App\Models\ContactInquiry;
use App\Models\Customer;
use App\Models\EventType;
use App\Models\Setting;
use App\Notifications\NewContactInquiryNotification;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Throwable;

class ContactController extends Controller
{
    public function store(StoreContactInquiryRequest $request): JsonResponse
    {
        if ($request->requiresAuthenticatedCustomer()) {
            $gated = $this->assertVerifiedCustomerInquiry($request);
            if ($gated instanceof JsonResponse) {
                return $gated;
            }
        }

        $data = $request->safe()->only([
            'name',
            'mobile',
            'email',
            'company',
            'subject',
            'message',
            'service_interested',
            'event_date',
            'event_location',
            'budget',
            'source',
        ]);

        $customer = $request->user('customer');
        if ($request->requiresAuthenticatedCustomer() && $customer instanceof Customer) {
            $phone = PhoneNumber::localTen($customer->phone);
            $data['name'] = (string) $customer->name;
            $data['email'] = $customer->email;
            $data['mobile'] = $phone;
        }

        $eventTypeId = $request->integer('event_type_id') ?: null;
        if ($eventTypeId) {
            $eventType = EventType::query()->active()->find($eventTypeId);
            if ($eventType) {
                $data['service_interested'] = $eventType->name;
            }
        }

        $inquiry = new ContactInquiry($data);

        $inquiry->forceFill([
            'customer_id' => $request->requiresAuthenticatedCustomer() && $customer instanceof Customer
                ? $customer->id
                : null,
            'status' => ContactInquiryStatus::New,
            'priority' => ContactInquiryPriority::Medium,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        $inquiry->save();

        $this->notifyTeam($inquiry);

        return (new ContactInquiryResource($inquiry->load('customer')))
            ->response()
            ->setStatusCode(201);
    }

    private function assertVerifiedCustomerInquiry(StoreContactInquiryRequest $request): ?JsonResponse
    {
        $customer = $request->user('customer');

        if ($customer === null) {
            return response()->json([
                'message' => 'Sign in with a verified customer account to send this inquiry.',
                'code' => 'customer_auth_required',
            ], 401);
        }

        if (! $customer->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Verify your email address to continue.',
                'code' => 'email_verification_required',
            ], 403);
        }

        $registeredPhone = PhoneNumber::localTen($customer->phone);
        if ($registeredPhone === null) {
            throw ValidationException::withMessages([
                'mobile' => ['Add a registered 10-digit mobile number to your account before sending an inquiry.'],
            ]);
        }

        $inputEmail = strtolower(trim((string) $request->input('email', '')));
        if ($inputEmail !== strtolower($customer->email)) {
            throw ValidationException::withMessages([
                'email' => ['Inquiry email must match your registered verified email.'],
            ]);
        }

        $inputMobile = (string) $request->input('mobile', '');
        if (! PhoneNumber::isExactTenDigits($inputMobile) || $inputMobile !== $registeredPhone) {
            throw ValidationException::withMessages([
                'mobile' => ['Inquiry phone must match your registered mobile number.'],
            ]);
        }

        return null;
    }

    private function notifyTeam(ContactInquiry $inquiry): void
    {
        $email = Setting::query()->value('email') ?: Setting::query()->value('support_email');

        if (! filled($email)) {
            return;
        }

        try {
            Notification::route('mail', $email)
                ->notify(new NewContactInquiryNotification($inquiry));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
