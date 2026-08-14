<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
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
        $serviceId = $this->route('service')?->id ?? $this->route('service');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('services', 'slug')->ignore($serviceId),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'full_description' => ['nullable', 'string'],
            'featured_image' => ['required', 'string', 'max:255'],
            'banner_image' => ['nullable', 'string', 'max:255'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'featured' => ['boolean'],
            'status' => ['boolean'],
            'show_on_homepage' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'seo_keywords' => ['nullable', 'string'],
            'opengraph_image' => ['nullable', 'string', 'max:255'],
        ];
    }
}
