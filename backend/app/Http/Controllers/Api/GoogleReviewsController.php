<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Google\GoogleReviewsService;
use Illuminate\Http\JsonResponse;

class GoogleReviewsController extends Controller
{
    public function __construct(private readonly GoogleReviewsService $googleReviews) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->googleReviews->publicPayload(),
        ]);
    }
}
