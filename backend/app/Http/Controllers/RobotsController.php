<?php

namespace App\Http\Controllers;

use App\Services\Seo\SeoService;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(SeoService $seo): Response
    {
        return response($seo->robotsTxt(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
