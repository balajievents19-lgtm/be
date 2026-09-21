<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        if (Setting::query()->exists()) {
            return;
        }

        Setting::query()->create([
            'company_name' => 'Balaji Royal Events',
            'company_tagline' => 'Trusted wedding & event management in Rajasthan',
            'company_description' => 'Balaji Royal Events is a wedding and event management company serving celebrations in Rajasthan, including Jhunjhunu and nearby towns. We help families plan décor, catering, entertainment, and hospitality for their special day.',

            'phone' => '+91-9462577065',
            'alternate_phone' => null,
            'whatsapp' => '+91-9462577065',
            'email' => 'balajievents19@gmail.com',
            'support_email' => 'balajievents19@gmail.com',
            'address' => 'Shop No.15, Road No.3, Opposite : Jamuna Resort Jhunjhunu (Rajasthan)',
            'google_map_embed' => null,

            'facebook' => null,
            'instagram' => null,
            'youtube' => null,
            'linkedin' => null,
            'twitter' => null,

            'working_hours' => "Monday – Saturday: 10:00 AM – 7:00 PM\nSunday: By appointment",
            'holiday_text' => 'Closed on major public holidays. Please call ahead to confirm.',
            'emergency_contact' => '+91-9462577065',

            'meta_title' => 'Balaji Royal Events | Wedding & Event Management in Rajasthan',
            'meta_description' => 'Balaji Royal Events provides wedding planning, catering, décor, photography, DJ and entertainment services in Jhunjhunu, Mandawa, and across Rajasthan.',
            'meta_keywords' => 'Balaji Royal Events, wedding planner, event management, Jhunjhunu, Mandawa, Rajasthan',
            'robots' => 'index, follow',
            'canonical_url' => 'http://localhost:3001',
            'google_analytics_id' => null,
            'google_search_console_verification' => null,
            'facebook_pixel_id' => null,

            'primary_color' => '#f15b22',
            'secondary_color' => '#0e1123',
            'theme_mode' => 'light',

            'footer_about' => 'Balaji Royal Events — wedding and event management in Rajasthan.',
            'copyright_text' => 'Copyright © '.date('Y').' - Balaji Royal Events | All Rights Reserved',
        ]);
    }
}
