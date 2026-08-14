<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHeroSlideRequest extends FormRequest
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
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'desktop_image' => ['required', 'string', 'max:255'],
            'mobile_image' => ['nullable', 'string', 'max:255'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'overlay_opacity' => ['required', 'integer', 'min:0', 'max:100'],
            'text_alignment' => ['required', Rule::in(['left', 'center', 'right'])],
            'status' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
