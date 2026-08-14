<?php

namespace App\Http\Requests;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactInquiryRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'mobile' => ['sometimes', 'required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['sometimes', 'required', 'string', 'max:5000'],
            'service_interested' => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'budget' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'required', Rule::enum(ContactInquiryStatus::class)],
            'priority' => ['sometimes', 'required', Rule::enum(ContactInquiryPriority::class)],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'admin_notes' => ['nullable', 'string'],
        ];
    }
}
