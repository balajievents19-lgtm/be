<?php

namespace App\Http\Controllers\Api;

use App\Enums\NewsletterSubscriberStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsletterSubscriberRequest;
use App\Http\Resources\Api\NewsletterSubscriberResource;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;

class NewsletterController extends Controller
{
    public function store(StoreNewsletterSubscriberRequest $request): JsonResponse
    {
        $email = strtolower((string) $request->validated('email'));

        $subscriber = NewsletterSubscriber::query()->updateOrCreate(
            ['email' => $email],
            [
                'first_name' => $request->validated('first_name'),
                'last_name' => $request->validated('last_name'),
                'status' => NewsletterSubscriberStatus::Active,
                'ip_address' => $request->ip(),
                'subscribed_at' => now(),
            ],
        );

        return (new NewsletterSubscriberResource($subscriber))
            ->response()
            ->setStatusCode($subscriber->wasRecentlyCreated ? 201 : 200);
    }
}
