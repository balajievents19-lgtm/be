<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFaqCategoryRequest extends FormRequest
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
        $categoryId = $this->route('faq_category')?->id ?? $this->route('faq_category');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('faq_categories', 'slug')->ignore($categoryId),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['boolean'],
        ];
    }
}
