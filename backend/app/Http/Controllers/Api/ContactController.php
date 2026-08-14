<?php

namespace App\Http\Controllers\Api;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactInquiryRequest;
use App\Http\Resources\Api\ContactInquiryResource;
use App\Models\ContactInquiry;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function store(StoreContactInquiryRequest $request): JsonResponse
    {
        $inquiry = new ContactInquiry($request->safe()->only([
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
        ]));

        $inquiry->forceFill([
            'status' => ContactInquiryStatus::New,
            'priority' => ContactInquiryPriority::Medium,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        $inquiry->save();

        return (new ContactInquiryResource($inquiry))
            ->response()
            ->setStatusCode(201);
    }
}
