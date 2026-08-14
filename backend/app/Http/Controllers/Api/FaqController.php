<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FaqResource;
use App\Models\Faq;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    public function index(): JsonResponse
    {
        $payload = ContentCache::remember(ContentCache::FAQS, function () {
            $faqs = Faq::query()
                ->with('category:id,name,slug')
                ->active()
                ->ordered()
                ->select([
                    'id',
                    'faq_category_id',
                    'question',
                    'slug',
                    'answer',
                    'featured',
                    'homepage_featured',
                    'sort_order',
                    'status',
                ])
                ->get();

            return FaqResource::collection($faqs)->additional([
                'schema' => Faq::faqPageSchema($faqs),
            ])->response()->getData(true);
        });

        return response()->json($payload);
    }

    public function show(string $slug): JsonResponse
    {
        $payload = ContentCache::rememberShow('faqs', $slug, function () use ($slug) {
            $faq = Faq::query()
                ->with('category:id,name,slug')
                ->active()
                ->where('slug', $slug)
                ->firstOrFail();

            return (new FaqResource($faq))->additional([
                'schema' => Faq::faqPageSchema(collect([$faq])),
            ])->response()->getData(true);
        });

        return response()->json($payload);
    }
}
