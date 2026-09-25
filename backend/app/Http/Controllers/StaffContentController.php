<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\ExternalMedia;
use App\Models\GalleryItem;
use App\Support\ContentCache;
use App\Support\Staff\ContentModeration;
use App\Support\Staff\StaffContentAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffContentController extends Controller
{
    use AuthorizesRequests;
    public function storeGalleryItem(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('create', GalleryItem::class);

        $data = $request->validate([
            'gallery_category_id' => ['required', 'integer', 'exists:gallery_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255'],
            'youtube_url' => ['nullable', 'string', 'max:2048'],
            'video_url' => ['nullable', 'string', 'max:2048'],
            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'exists:services,id'],
            'status' => ['sometimes', 'boolean'],
        ]);

        $categoryId = (int) $data['gallery_category_id'];
        if (! StaffContentAccess::canCreateInGalleryCategory($user, $categoryId)) {
            abort(403, 'You are not assigned to this gallery category.');
        }

        $serviceIds = $data['services'] ?? [];
        if ($serviceIds !== [] && ! StaffContentAccess::servicesAreAllowed($user, $serviceIds)) {
            abort(403, 'You cannot assign an unauthorized service.');
        }

        if (! StaffContentAccess::isSuperAdmin($user) && array_key_exists('status', $data) && $data['status']) {
            abort(403, 'Staff cannot publish content.');
        }

        unset($data['services']);
        $payload = StaffContentAccess::isSuperAdmin($user)
            ? array_merge($data, ['created_by' => $user->id, 'updated_by' => $user->id])
            : ContentModeration::prepareStaffCreate($user, $data);

        if (blank($payload['slug'] ?? null)) {
            $payload['slug'] = Str::slug($payload['title']).'-'.Str::random(6);
        }

        $item = GalleryItem::query()->create($payload);
        if ($serviceIds !== []) {
            $item->services()->sync($serviceIds);
        }

        if (! StaffContentAccess::isSuperAdmin($user)) {
            ContentModeration::notifySuperAdmins($item, $user, (bool) $item->brand_review_required);
        }

        $this->flushContent();

        return response()->json(['data' => $item->fresh()], 201);
    }

    public function updateGalleryItem(Request $request, GalleryItem $galleryItem): JsonResponse
    {
        $user = $request->user();
        $this->authorize('update', $galleryItem);

        $data = $request->validate([
            'gallery_category_id' => ['sometimes', 'integer', 'exists:gallery_categories,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'boolean'],
            'moderation_status' => ['sometimes', 'string'],
        ]);

        if (isset($data['gallery_category_id'])
            && ! StaffContentAccess::canEditOwnInGalleryCategory($user, (int) $data['gallery_category_id'])
            && ! StaffContentAccess::isSuperAdmin($user)) {
            abort(403, 'You cannot move content to an unauthorized category.');
        }

        if (! StaffContentAccess::isSuperAdmin($user)) {
            if ((array_key_exists('status', $data) && $data['status'])
                || in_array($data['moderation_status'] ?? null, ['published', 'approved'], true)) {
                abort(403, 'Staff cannot publish content.');
            }
            $data = ContentModeration::prepareStaffUpdate($user, $galleryItem, $data);
        }

        $galleryItem->fill($data)->save();
        $this->flushContent();

        return response()->json(['data' => $galleryItem->fresh()]);
    }

    public function destroyGalleryItem(Request $request, GalleryItem $galleryItem): JsonResponse
    {
        $this->authorize('delete', $galleryItem);
        $galleryItem->delete();
        $this->flushContent();

        return response()->json(['deleted' => true]);
    }

    public function approveGalleryItem(Request $request, GalleryItem $galleryItem): JsonResponse
    {
        $this->authorize('approve', $galleryItem);
        ContentModeration::approve($request->user(), $galleryItem);
        $this->flushContent();

        return response()->json(['data' => $galleryItem->fresh()]);
    }

    public function rejectGalleryItem(Request $request, GalleryItem $galleryItem): JsonResponse
    {
        $this->authorize('approve', $galleryItem);
        ContentModeration::reject($request->user(), $galleryItem, $request->string('notes')->toString() ?: null);
        $this->flushContent();

        return response()->json(['data' => $galleryItem->fresh()]);
    }

    public function storeExternalMedia(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('create', ExternalMedia::class);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'media_type' => ['required', 'string', 'max:50'],
            'provider' => ['required', 'string', 'max:50'],
            'gallery_category_id' => ['nullable', 'integer', 'exists:gallery_categories,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'status' => ['sometimes', 'boolean'],
            'homepage_featured' => ['sometimes', 'boolean'],
        ]);

        if (! empty($data['gallery_category_id']) && ! StaffContentAccess::canCreateInGalleryCategory($user, (int) $data['gallery_category_id'])) {
            abort(403, 'You are not assigned to this gallery category.');
        }
        if (! empty($data['service_id']) && ! StaffContentAccess::canCreateInService($user, (int) $data['service_id'])) {
            abort(403, 'You are not assigned to this service.');
        }
        if (! StaffContentAccess::isSuperAdmin($user) && ($data['status'] ?? false)) {
            abort(403, 'Staff cannot publish content.');
        }

        $payload = StaffContentAccess::isSuperAdmin($user)
            ? array_merge($data, ['created_by' => $user->id, 'updated_by' => $user->id])
            : ContentModeration::prepareStaffCreate($user, $data);

        $record = ExternalMedia::query()->create($payload);
        if (! StaffContentAccess::isSuperAdmin($user)) {
            ContentModeration::notifySuperAdmins($record, $user, (bool) $record->brand_review_required);
        }
        $this->flushContent();

        return response()->json(['data' => $record->fresh()], 201);
    }

    public function destroyExternalMedia(Request $request, ExternalMedia $externalMedia): JsonResponse
    {
        $this->authorize('delete', $externalMedia);
        $externalMedia->delete();
        $this->flushContent();

        return response()->json(['deleted' => true]);
    }

    public function approve(Request $request, string $type, int $id): JsonResponse
    {
        $record = $this->resolve($type, $id);
        $this->authorize('approve', $record);
        ContentModeration::approve($request->user(), $record);
        $this->flushContent();

        return response()->json(['data' => $record->fresh()]);
    }

    public function reject(Request $request, string $type, int $id): JsonResponse
    {
        $record = $this->resolve($type, $id);
        $this->authorize('approve', $record);
        ContentModeration::reject($request->user(), $record);
        $this->flushContent();

        return response()->json(['data' => $record->fresh()]);
    }

    private function resolve(string $type, int $id): Model
    {
        return match ($type) {
            'gallery-items' => GalleryItem::query()->findOrFail($id),
            'external-media' => ExternalMedia::query()->findOrFail($id),
            'blog-posts' => BlogPost::query()->findOrFail($id),
            default => abort(404),
        };
    }

    private function flushContent(): void
    {
        ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::HOME, ContentCache::EXTERNAL_MEDIA);
    }
}
