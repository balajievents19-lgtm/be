<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ExternalMediaResource;
use App\Models\ExternalMedia;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExternalMediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $homepageOnly = $request->boolean('homepage');

        $payload = ContentCache::remember(
            $homepageOnly ? ContentCache::EXTERNAL_MEDIA.'.homepage' : ContentCache::EXTERNAL_MEDIA,
            function () use ($homepageOnly) {
                $query = ExternalMedia::query()->active()->ordered();

                if ($homepageOnly) {
                    $query->homepage();
                }

                return ExternalMediaResource::collection($query->get())->resolve();
            }
        );

        return response()->json(['data' => $payload]);
    }
}
