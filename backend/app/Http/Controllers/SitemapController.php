<?php

namespace App\Http\Controllers;

use App\Services\Seo\SeoService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(SeoService $seo): Response
    {
        return response($seo->sitemapXml(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
