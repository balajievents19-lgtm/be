<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGalleryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $itemId = $this->route('gallery_item')?->id ?? $this->route('gallery_item');

        return [
            'gallery_category_id' => ['required', 'integer', Rule::exists('gallery_categories', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('gallery_items', 'slug')->ignore($itemId),
            ],
            'description' => ['nullable', 'string'],
            'image' => ['required_if:media_type,image', 'nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'vimeo_url' => ['nullable', 'url', 'max:255'],
            'media_type' => ['nullable', 'in:image,video'],
            'video_source' => ['required_if:media_type,video', 'nullable', 'in:youtube,instagram,facebook,other'],
            'video_url' => ['required_if:media_type,video', 'nullable', 'url', 'max:2048'],
            'featured' => ['boolean'],
            'homepage_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'opengraph_image' => ['nullable', 'string', 'max:255'],
        ];
    }
}
