<?php

namespace App\Http\Controllers\Api;

use App\Contracts\NavigationRepository;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Navigation\NavigationItemResource;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    public function __construct(
        private readonly NavigationRepository $navigationRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $placement = (string) $request->query('placement', 'header');

        $cacheKey = $placement === 'footer'
            ? ContentCache::NAVIGATION_FOOTER
            : ContentCache::NAVIGATION_HEADER;

        $payload = ContentCache::remember($cacheKey, function () use ($placement) {
            $items = $placement === 'footer'
                ? $this->navigationRepository->getFooterItems()
                : $this->navigationRepository->getHeaderItems();

            return NavigationItemResource::collection($items)->response()->getData(true);
        });

        return response()->json($payload);
    }
}
