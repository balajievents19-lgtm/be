<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFaqRequest extends FormRequest
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
        $faqId = $this->route('faq')?->id ?? $this->route('faq');

        return [
            'faq_category_id' => ['required', 'integer', Rule::exists('faq_categories', 'id')],
            'question' => ['required', 'string', 'max:500'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('faqs', 'slug')->ignore($faqId),
            ],
            'answer' => ['nullable', 'string'],
            'featured' => ['boolean'],
            'homepage_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
        ];
    }
}
