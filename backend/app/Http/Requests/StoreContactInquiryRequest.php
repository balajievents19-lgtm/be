<?php

namespace App\Http\Requests;

use App\Models\EventType;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreContactInquiryRequest extends FormRequest
{
    public const CUSTOMER_REQUIRED_SOURCES = ['service_inquiry', 'package_inquiry'];

    public function authorize(): bool
    {
        return true;
    }

    public function requiresAuthenticatedCustomer(): bool
    {
        return in_array((string) $this->input('source'), self::CUSTOMER_REQUIRED_SOURCES, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $customerInquiry = $this->requiresAuthenticatedCustomer();

        return [
            'name' => ['required', 'string', 'max:255'],
            'mobile' => $customerInquiry
                ? ['required', 'string', 'size:10', 'regex:/^[0-9]{10}$/']
                : ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s\-()]{8,28}$/'],
            'email' => $customerInquiry
                ? ['required', 'email', 'max:255']
                : ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'service_interested' => ['nullable', 'string', 'max:255'],
            'event_type_id' => [
                'nullable',
                'integer',
                Rule::exists('event_types', 'id')->where(fn ($query) => $query->where('status', true)->whereNull('deleted_at')),
            ],
            'event_date' => ['nullable', 'date', 'after_or_equal:today'],
            'event_location' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'max:64'],
            'customer_id' => ['prohibited'],
            'website' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->requiresAuthenticatedCustomer()) {
                if ($this->filled('mobile') && ! PhoneNumber::isExactTenDigits((string) $this->input('mobile'))) {
                    $validator->errors()->add('mobile', 'Enter exactly 10 digits with no +91, spaces, or punctuation.');
                }

                return;
            }

            $digits = preg_replace('/\D+/', '', (string) $this->input('mobile', '')) ?? '';
            if ($this->filled('mobile') && (strlen($digits) < 10 || strlen($digits) > 15)) {
                $validator->errors()->add('mobile', 'Enter a valid phone number with at least 10 digits.');
            }

            if ($this->input('source') !== 'slider') {
                return;
            }

            if (! filled($this->input('event_date'))) {
                $validator->errors()->add('event_date', 'Event date is required.');
            }

            if (! filled($this->input('event_location'))) {
                $validator->errors()->add('event_location', 'Event location is required.');
            }

            $eventTypeId = $this->input('event_type_id');
            $serviceInterested = trim((string) $this->input('service_interested', ''));

            if (! filled($eventTypeId) && $serviceInterested === '') {
                $validator->errors()->add('event_type_id', 'A valid event type is required.');

                return;
            }

            if (filled($eventTypeId)) {
                return;
            }

            $exists = EventType::query()
                ->active()
                ->where(function ($query) use ($serviceInterested): void {
                    $query->where('name', $serviceInterested)
                        ->orWhere('slug', $serviceInterested);
                })
                ->exists();

            if (! $exists) {
                $validator->errors()->add('service_interested', 'Selected event type is invalid or inactive.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'website.prohibited' => 'Spam detected.',
            'customer_id.prohibited' => 'Customer identity is determined from the authenticated session.',
            'event_date.after_or_equal' => 'Event date cannot be in the past.',
            'mobile.regex' => 'Enter exactly 10 digits with no +91, spaces, or punctuation.',
            'mobile.size' => 'Enter exactly 10 digits with no +91, spaces, or punctuation.',
        ];
    }
}
