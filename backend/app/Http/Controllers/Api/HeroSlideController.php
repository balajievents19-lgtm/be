<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HeroSlideResource;
use App\Models\HeroSlide;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;

class HeroSlideController extends Controller
{
    public function index(): JsonResponse
    {
        $payload = ContentCache::remember(ContentCache::HERO, function () {
            $slides = HeroSlide::query()
                ->active()
                ->ordered()
                ->get();

            return HeroSlideResource::collection($slides)->response()->getData(true);
        });

        return response()->json($payload);
    }
}
