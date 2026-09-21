<?php

namespace App\Http\Resources\Api;

use App\Models\ContactInquiry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ContactInquiry */
class ContactInquiryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'name' => $this->name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'company' => $this->company,
            'subject' => $this->subject,
            'message' => $this->message,
            'service_interested' => $this->service_interested,
            'event_date' => $this->event_date?->toDateString(),
            'event_location' => $this->event_location,
            'budget' => $this->budget,
            'source' => $this->source,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
