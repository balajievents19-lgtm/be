<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = $this->defaults();

        $setting = Setting::query()->first();

        if ($setting === null) {
            Setting::query()->create($defaults);

            return;
        }

        $fill = [];

        foreach ($defaults as $column => $value) {
            if ($this->isBlank($setting->getAttribute($column))) {
                $fill[$column] = $value;
            }
        }

        if ($fill === []) {
            return;
        }

        $setting->fill($fill)->save();
    }

    /**
     * Brand contact/SEO defaults only. Canonical URL, socials, logos, maps,
     * analytics, and CMS content are left unset until configured.
     *
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        return [
            'company_name' => 'Balaji Royal Events',
            'company_tagline' => 'Trusted wedding & event management in Rajasthan',
            'company_description' => 'Balaji Royal Events is a wedding and event management company serving celebrations in Rajasthan, including Jhunjhunu and nearby towns. We help families plan décor, catering, entertainment, and hospitality for their special day.',

            'phone' => '+91-9462577065',
            'whatsapp' => '+91-9462577065',
            'email' => 'balajievents19@gmail.com',
            'support_email' => 'balajievents19@gmail.com',
            'address' => 'Shop No.15, Road No.3, Opposite : Jamuna Resort Jhunjhunu (Rajasthan)',

            'working_hours' => "Monday – Saturday: 10:00 AM – 7:00 PM\nSunday: By appointment",
            'holiday_text' => 'Closed on major public holidays. Please call ahead to confirm.',
            'emergency_contact' => '+91-9462577065',

            'meta_title' => 'Balaji Royal Events | Wedding & Event Management in Rajasthan',
            'meta_description' => 'Balaji Royal Events provides wedding planning, catering, décor, photography, DJ and entertainment services in Jhunjhunu, Mandawa, and across Rajasthan.',
            'meta_keywords' => 'Balaji Royal Events, wedding planner, event management, Jhunjhunu, Mandawa, Rajasthan',
            'robots' => 'index, follow',

            'primary_color' => '#f15b22',
            'secondary_color' => '#0e1123',
            'theme_mode' => 'light',

            'footer_about' => 'Balaji Royal Events — wedding and event management in Rajasthan.',
            'copyright_text' => 'Copyright © '.date('Y').' - Balaji Royal Events | All Rights Reserved',
        ];
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || $value === '';
    }
}
