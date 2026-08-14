<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGalleryItemRequest extends FormRequest
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
        return [
            'gallery_category_id' => ['required', 'integer', Rule::exists('gallery_categories', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('gallery_items', 'slug')],
            'description' => ['nullable', 'string'],
            'image' => ['required', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'vimeo_url' => ['nullable', 'url', 'max:255'],
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
