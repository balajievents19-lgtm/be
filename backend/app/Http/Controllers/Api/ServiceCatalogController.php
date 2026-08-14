<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ServiceCategoryResource;
use App\Http\Resources\Api\ServicePackageResource;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;

class ServiceCatalogController extends Controller
{
    public function categories(): JsonResponse
    {
        $payload = ContentCache::remember('api.content.service_categories', function () {
            $categories = ServiceCategory::query()
                ->active()
                ->ordered()
                ->get();

            return ServiceCategoryResource::collection($categories)->response()->getData(true);
        });

        return response()->json($payload);
    }

    public function packages(): JsonResponse
    {
        $payload = ContentCache::remember('api.content.service_packages', function () {
            $packages = ServicePackage::query()
                ->active()
                ->ordered()
                ->get();

            return ServicePackageResource::collection($packages)->response()->getData(true);
        });

        return response()->json($payload);
    }
}
