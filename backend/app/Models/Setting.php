<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStorageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LogicException;

class Setting extends Model
{
    use HasPublicStorageUrl;

    protected $fillable = [
        'company_name',
        'company_tagline',
        'company_description',
        'about_image',
        'about_vision',
        'about_mission',
        'about_journey',
        'logo',
        'dark_logo',
        'footer_logo',
        'favicon',
        'phone',
        'alternate_phone',
        'whatsapp',
        'email',
        'support_email',
        'address',
        'google_map_embed',
        'facebook',
        'instagram',
        'youtube',
        'linkedin',
        'twitter',
        'working_hours',
        'holiday_text',
        'emergency_contact',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'homepage_seo_title',
        'homepage_seo_description',
        'homepage_seo_keywords',
        'opengraph_image',
        'robots',
        'robots_txt_extra',
        'sitemap_enabled',
        'canonical_url',
        'google_analytics_id',
        'google_search_console_verification',
        'facebook_pixel_id',
        'primary_color',
        'secondary_color',
        'theme_mode',
        'header_enabled',
        'top_bar_enabled',
        'sticky_header_enabled',
        'top_bar_text',
        'header_cta_label',
        'header_cta_url',
        'mobile_header_enabled',
        'mobile_menu_style',
        'hero_search_enabled',
        'hero_search_placeholder',
        'hero_search_button_label',
        'footer_about',
        'copyright_text',
        'footer_enabled',
        'footer_newsletter_enabled',
        'footer_social_enabled',
    ];

    protected function casts(): array
    {
        return [
            'header_enabled' => 'boolean',
            'top_bar_enabled' => 'boolean',
            'sticky_header_enabled' => 'boolean',
            'mobile_header_enabled' => 'boolean',
            'hero_search_enabled' => 'boolean',
            'sitemap_enabled' => 'boolean',
            'footer_enabled' => 'boolean',
            'footer_newsletter_enabled' => 'boolean',
            'footer_social_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (): void {
            if (static::query()->exists()) {
                throw new LogicException('Only one website settings record is allowed.');
            }
        });
    }

    public static function singleton(): self
    {
        $setting = static::query()->first();

        if ($setting) {
            return $setting;
        }

        return static::query()->create([
            'company_name' => 'Balaji Events',
            'primary_color' => '#f15b22',
            'secondary_color' => '#0e1123',
            'theme_mode' => 'light',
            'robots' => 'index, follow',
        ]);
    }

    public static function current(): self
    {
        $setting = static::query()->first();

        if (! $setting) {
            throw (new ModelNotFoundException)->setModel(static::class);
        }

        return $setting;
    }
}
