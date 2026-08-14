<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactInquiryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'service_interested' => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'event_location' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'max:64'],
            // Honeypot — must remain empty (spam protection).
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'website.prohibited' => 'Spam detected.',
        ];
    }
}
