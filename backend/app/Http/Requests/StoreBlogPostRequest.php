<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlogPostRequest extends FormRequest
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
            'blog_category_id' => ['required', 'integer', Rule::exists('blog_categories', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('blog_posts', 'slug')],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'banner_image' => ['nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'featured' => ['boolean'],
            'homepage_featured' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'status' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'seo_keywords' => ['nullable', 'string'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'opengraph_image' => ['nullable', 'string', 'max:255'],
            'schema_type' => ['nullable', 'string', 'max:100'],
            'reading_time' => ['nullable', 'integer', 'min:1'],
            'author' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
        ];
    }
}
