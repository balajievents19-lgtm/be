<?php

namespace App\Http\Resources\Api;

use App\Models\Setting;
use App\Support\Media\ResponsiveImageBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Setting */
class SettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'company' => [
                'name' => $this->company_name,
                'tagline' => $this->company_tagline,
                'description' => $this->company_description,
            ],
            'about' => [
                'description' => $this->company_description,
                'image' => $this->imageUrl($this->about_image),
                'vision' => $this->about_vision,
                'mission' => $this->about_mission,
                'journey' => $this->about_journey,
            ],
            'brand' => [
                'logo' => $this->imageUrl($this->logo),
                'dark_logo' => $this->imageUrl($this->dark_logo),
                'footer_logo' => $this->imageUrl($this->footer_logo),
                'favicon' => $this->imageUrl($this->favicon),
                'responsive' => [
                    'logo' => app(ResponsiveImageBuilder::class)->build($this->logo),
                    'dark_logo' => app(ResponsiveImageBuilder::class)->build($this->dark_logo),
                    'footer_logo' => app(ResponsiveImageBuilder::class)->build($this->footer_logo),
                ],
            ],
            'contact' => [
                'phone' => $this->phone,
                'alternate_phone' => $this->alternate_phone,
                'whatsapp' => $this->whatsapp,
                'email' => $this->email,
                'support_email' => $this->support_email,
                'address' => $this->address,
                'google_map_embed' => $this->google_map_embed,
            ],
            'social' => [
                'facebook' => $this->facebook,
                'instagram' => $this->instagram,
                'youtube' => $this->youtube,
                'linkedin' => $this->linkedin,
                'twitter' => $this->twitter,
            ],
            'business' => [
                'working_hours' => $this->working_hours,
                'holiday_text' => $this->holiday_text,
                'emergency_contact' => $this->emergency_contact,
            ],
            'seo' => [
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords' => $this->meta_keywords,
                'homepage_title' => $this->homepage_seo_title,
                'homepage_description' => $this->homepage_seo_description,
                'homepage_keywords' => $this->homepage_seo_keywords,
                'opengraph_image' => $this->imageUrl($this->opengraph_image),
                'robots' => $this->robots,
                'robots_txt_extra' => $this->robots_txt_extra,
                'sitemap_enabled' => (bool) $this->sitemap_enabled,
                'canonical_url' => $this->canonical_url,
                'google_analytics_id' => $this->google_analytics_id,
                'google_search_console_verification' => $this->google_search_console_verification,
                'facebook_pixel_id' => $this->facebook_pixel_id,
            ],
            'theme' => [
                'primary_color' => $this->primary_color,
                'secondary_color' => $this->secondary_color,
                'theme_mode' => $this->theme_mode,
            ],
            'header' => [
                'enabled' => $this->header_enabled,
                'top_bar_enabled' => $this->top_bar_enabled,
                'sticky_enabled' => $this->sticky_header_enabled,
                'top_bar_text' => $this->top_bar_text,
                'cta_label' => $this->header_cta_label,
                'cta_url' => $this->header_cta_url,
                'mobile_enabled' => $this->mobile_header_enabled,
                'mobile_menu_style' => $this->mobile_menu_style,
            ],
            'hero_search' => [
                'enabled' => $this->hero_search_enabled,
                'placeholder' => $this->hero_search_placeholder,
                'button_label' => $this->hero_search_button_label,
            ],
            'footer' => [
                'about' => $this->footer_about,
                'copyright_text' => $this->copyright_text,
                'enabled' => $this->footer_enabled,
                'newsletter_enabled' => $this->footer_newsletter_enabled,
                'social_enabled' => $this->footer_social_enabled,
            ],
        ];
    }
}
