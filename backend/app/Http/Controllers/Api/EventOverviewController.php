<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EventOverviewResource;
use App\Models\EventOverview;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;

class EventOverviewController extends Controller
{
    public function index(): JsonResponse
    {
        $payload = ContentCache::remember(ContentCache::EVENT_OVERVIEWS, function () {
            $items = EventOverview::query()
                ->active()
                ->ordered()
                ->select([
                    'id',
                    'title',
                    'caption',
                    'description',
                    'image',
                    'link_url',
                    'featured',
                    'homepage_featured',
                    'sort_order',
                    'status',
                ])
                ->get();

            return EventOverviewResource::collection($items)->response()->getData(true);
        });

        return response()->json($payload);
    }
}
