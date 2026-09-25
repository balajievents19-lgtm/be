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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Throwable;

class ContactController extends Controller
{
    public function store(StoreContactInquiryRequest $request): JsonResponse
    {
        $gated = $this->assertVerifiedCustomerInquiry($request);
        if ($gated instanceof JsonResponse) {
            return $gated;
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
        if (! $customer instanceof Customer) {
            return $this->rejectEnquiry($request, 401, 'customer_auth_required');
        }

        $phone = PhoneNumber::localTen($customer->phone);
        $data['name'] = (string) $customer->name;
        $data['email'] = $customer->email;
        $data['mobile'] = $phone;

        $eventTypeId = $request->integer('event_type_id') ?: null;
        if ($eventTypeId) {
            $eventType = EventType::query()->active()->find($eventTypeId);
            if ($eventType) {
                $data['service_interested'] = $eventType->name;
            }
        }

        $inquiry = new ContactInquiry($data);

        $inquiry->forceFill([
            'customer_id' => $customer->id,
            'status' => ContactInquiryStatus::New,
            'priority' => ContactInquiryPriority::Medium,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        $inquiry->save();

        Log::info('enquiry.accepted', [
            'inquiry_id' => $inquiry->id,
            'customer_id' => $customer->id,
            'source' => $inquiry->source,
        ]);

        $this->notifyTeam($inquiry);

        return (new ContactInquiryResource($inquiry->load('customer')))
            ->response()
            ->setStatusCode(201);
    }

    private function rejectEnquiry(StoreContactInquiryRequest $request, int $status, string $code): JsonResponse
    {
        Log::notice('enquiry.rejected', [
            'code' => $code,
            'customer_id' => $request->user('customer')?->id,
            'source' => $request->input('source'),
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Please verify your account before submitting an enquiry.',
            'code' => $code,
        ], $status);
    }

    private function assertVerifiedCustomerInquiry(StoreContactInquiryRequest $request): ?JsonResponse
    {
        $customer = $request->user('customer');

        if ($customer === null) {
            return $this->rejectEnquiry($request, 401, 'customer_auth_required');
        }

        $customer->loadMissing('socialAccounts');

        if (! $customer->isVerifiedForEnquiry()) {
            return $this->rejectEnquiry($request, 403, 'email_verification_required');
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
