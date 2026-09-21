<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EventTypeResource;
use App\Models\EventType;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;

class EventTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $payload = ContentCache::remember(ContentCache::EVENT_TYPES, function () {
            return EventTypeResource::collection(
                EventType::query()->active()->ordered()->get()
            )->resolve();
        });

        return response()->json(['data' => $payload]);
    }
}
