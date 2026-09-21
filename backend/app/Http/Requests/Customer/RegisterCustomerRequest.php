<?php

namespace App\Http\Requests\Customer;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterCustomerRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email'],
            'username' => ['nullable', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:customers,username'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'phone' => [
                'required',
                'string',
                'size:10',
                'regex:/^[0-9]{10}$/',
                PhoneNumber::uniqueCustomerRule(),
            ],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'terms.accepted' => 'You must accept the terms of service to register.',
            'phone.regex' => 'Enter exactly 10 digits with no +91, spaces, or punctuation.',
            'phone.size' => 'Enter exactly 10 digits with no +91, spaces, or punctuation.',
            'email.unique' => 'This email is already registered.',
        ];
    }
}
