<?php

namespace App\Support\Estimates;

use App\Models\Estimate;
use App\Models\Setting;
use App\Support\Brand;
use Illuminate\Support\Facades\Storage;

final class EstimateCompanySnapshot
{
    /**
     * @return array<string, string|null>
     */
    public static function fromSettings(): array
    {
        $settings = Setting::query()->first();

        return [
            'issued_company_name' => Brand::name($settings?->company_name),
            'issued_company_tagline' => Brand::rewrite($settings?->company_tagline) ?: 'Trusted wedding & event management in Rajasthan',
            'issued_phone' => $settings?->whatsapp ?: $settings?->phone,
            'issued_email' => $settings?->email,
            'issued_address' => $settings?->address,
            'issued_website' => rtrim((string) config('app.url'), '/'),
            'issued_logo_path' => $settings?->logo ?: $settings?->footer_logo,
        ];
    }

    public static function apply(Estimate $estimate, bool $overwriteExisting = false): void
    {
        $snapshot = self::fromSettings();

        foreach ($snapshot as $key => $value) {
            if (! $overwriteExisting && filled($estimate->{$key})) {
                continue;
            }
            $estimate->{$key} = $value;
        }
    }

    public static function logoAbsolutePath(?string $relativePath): ?string
    {
        if (! filled($relativePath)) {
            return null;
        }

        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $publicPath = Storage::disk('public')->path($relativePath);
        if (is_file($publicPath)) {
            return $publicPath;
        }

        $legacy = storage_path('app/public/'.$relativePath);
        if (is_file($legacy)) {
            return $legacy;
        }

        return null;
    }
}
