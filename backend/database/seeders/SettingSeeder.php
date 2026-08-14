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
            'company_name' => 'Balaji Events',
            'company_tagline' => 'Trusted wedding & event management in Rajasthan',
            'company_description' => 'Balaji Events is a trusted wedding management company in India. We provide many different services in Rajasthan and promise to set the perfect pitch for bride and groom to celebrate their special day in a grand way.',

            'phone' => '+91-9462577065',
            'alternate_phone' => '+91-8058780290',
            'whatsapp' => '+91-9462577065',
            'email' => 'balajievents19@gmail.com',
            'support_email' => 'balajievents19@gmail.com',
            'address' => 'Shop No.15, Road No.3, Opposite : Jamuna Resort Jhunjhunu (Rajasthan)',
            'google_map_embed' => null,

            'facebook' => 'https://facebook.com/',
            'instagram' => 'https://instagram.com/',
            'youtube' => 'https://youtube.com/',
            'linkedin' => 'https://linkedin.com/',
            'twitter' => 'https://x.com/',

            'working_hours' => "Monday – Saturday: 10:00 AM – 7:00 PM\nSunday: By appointment",
            'holiday_text' => 'Closed on major public holidays. Please call ahead to confirm.',
            'emergency_contact' => '+91-9462577065',

            'meta_title' => 'Balaji Events | Wedding & Event Management in Rajasthan',
            'meta_description' => 'Balaji Events provides wedding planning, catering, décor, photography, DJ and entertainment services across Rajasthan.',
            'meta_keywords' => 'Balaji Events, wedding planner, event management, Jhunjhunu, Rajasthan',
            'robots' => 'index, follow',
            'canonical_url' => 'http://localhost:3000',
            'google_analytics_id' => null,
            'google_search_console_verification' => null,
            'facebook_pixel_id' => null,

            'primary_color' => '#f15b22',
            'secondary_color' => '#0e1123',
            'theme_mode' => 'light',

            'footer_about' => 'Balaji Events — trusted wedding and event management in Rajasthan.',
            'copyright_text' => 'Copyright © '.date('Y').' - BalajiEvents | All Rights Reserved',
        ]);
    }
}
