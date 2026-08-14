<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
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
            'company_name' => ['required', 'string', 'max:255'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'company_description' => ['nullable', 'string'],

            'logo' => ['nullable', 'string', 'max:255'],
            'dark_logo' => ['nullable', 'string', 'max:255'],
            'footer_logo' => ['nullable', 'string', 'max:255'],
            'favicon' => ['nullable', 'string', 'max:255'],

            'phone' => ['nullable', 'string', 'max:50'],
            'alternate_phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'google_map_embed' => ['nullable', 'string'],

            'facebook' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'twitter' => ['nullable', 'url', 'max:255'],

            'working_hours' => ['nullable', 'string'],
            'holiday_text' => ['nullable', 'string'],
            'emergency_contact' => ['nullable', 'string', 'max:100'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'opengraph_image' => ['nullable', 'string', 'max:255'],
            'robots' => ['nullable', 'string', 'max:100'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'google_analytics_id' => ['nullable', 'string', 'max:100'],
            'google_search_console_verification' => ['nullable', 'string', 'max:255'],
            'facebook_pixel_id' => ['nullable', 'string', 'max:100'],

            'primary_color' => ['required', 'string', 'max:20'],
            'secondary_color' => ['required', 'string', 'max:20'],
            'theme_mode' => ['required', Rule::in(['light', 'dark', 'system'])],

            'footer_about' => ['nullable', 'string'],
            'copyright_text' => ['nullable', 'string', 'max:255'],
        ];
    }
}
