<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Services\Gallery\GalleryOriginalStorage;
use App\Support\Media\DisplayImageFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class GalleryDownloadController extends Controller
{
    public function __construct(
        private readonly GalleryOriginalStorage $originals,
        private readonly DisplayImageFactory $images
    ) {}

    public function __invoke(Request $request, int $id): Response
    {
        // Customer must be authenticated (auth:customer middleware).
        if ($request->user('customer') === null) {
            return response()->json(['message' => 'Authentication required.'], 401);
        }

        $item = GalleryItem::query()->active()->find($id);
        if ($item === null) {
            return response()->json(['message' => 'Gallery item not found.'], 404);
        }

        if (! $this->originals->hasPrivateOriginal($item)) {
            return response()->json(['message' => 'Original download is not available for this item.'], 404);
        }

        try {
            return $this->originals->download($item, $this->images);
        } catch (Throwable) {
            return response()->json(['message' => 'Unable to download this file.'], 404);
        }
    }
}
