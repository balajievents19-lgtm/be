<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SettingResource;
use App\Models\Setting;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function show(): JsonResponse
    {
        $payload = ContentCache::remember(ContentCache::SETTINGS, function () {
            return (new SettingResource(Setting::singleton()))->response()->getData(true);
        });

        return response()->json($payload);
    }
}
