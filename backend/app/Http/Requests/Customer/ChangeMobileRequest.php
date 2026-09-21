<?php

namespace App\Http\Requests\Customer;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class ChangeMobileRequest extends FormRequest
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
        $id = $this->user('customer')?->id;

        return [
            'current_password' => ['required', 'string'],
            'phone' => [
                'required',
                'string',
                'size:10',
                'regex:/^[0-9]{10}$/',
                PhoneNumber::uniqueCustomerRule($id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter exactly 10 digits with no +91, spaces, or punctuation.',
            'phone.size' => 'Enter exactly 10 digits with no +91, spaces, or punctuation.',
        ];
    }
}
