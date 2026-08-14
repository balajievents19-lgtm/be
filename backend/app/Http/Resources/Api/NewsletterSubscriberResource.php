<?php

namespace App\Http\Resources\Api;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NewsletterSubscriber */
class NewsletterSubscriberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'status' => $this->status?->value,
            'subscribed_at' => $this->subscribed_at?->toIso8601String(),
        ];
    }
}
