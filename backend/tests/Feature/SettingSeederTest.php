<?php

namespace Tests\Feature;

use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fills_blank_fields_on_an_existing_settings_row(): void
    {
        $setting = Setting::query()->create([
            'company_name' => 'Balaji Royal Events',
        ]);

        $this->seed(SettingSeeder::class);

        $setting->refresh();

        $this->assertSame('Balaji Royal Events', $setting->company_name);
        $this->assertSame('Trusted wedding & event management in Rajasthan', $setting->company_tagline);
        $this->assertSame('+91-9462577065', $setting->phone);
        $this->assertSame('+91-9462577065', $setting->whatsapp);
        $this->assertSame('balajievents19@gmail.com', $setting->email);
        $this->assertSame('balajievents19@gmail.com', $setting->support_email);
        $this->assertSame('Shop No.15, Road No.3, Opposite : Jamuna Resort Jhunjhunu (Rajasthan)', $setting->address);
        $this->assertSame("Monday – Saturday: 10:00 AM – 7:00 PM\nSunday: By appointment", $setting->working_hours);
        $this->assertSame('Closed on major public holidays. Please call ahead to confirm.', $setting->holiday_text);
        $this->assertSame('+91-9462577065', $setting->emergency_contact);
        $this->assertSame('Balaji Royal Events | Wedding & Event Management in Rajasthan', $setting->meta_title);
        $this->assertSame('index, follow', $setting->robots);
        $this->assertSame('#f15b22', $setting->primary_color);
        $this->assertSame('#0e1123', $setting->secondary_color);
        $this->assertSame('light', $setting->theme_mode);
        $this->assertSame('Balaji Royal Events — wedding and event management in Rajasthan.', $setting->footer_about);
        $this->assertNotNull($setting->copyright_text);
        $this->assertNull($setting->canonical_url);
        $this->assertNull($setting->facebook);
        $this->assertNull($setting->instagram);
        $this->assertNull($setting->logo);
        $this->assertNull($setting->google_place_id);
        $this->assertSame(1, Setting::query()->count());
    }

    public function test_it_does_not_overwrite_customized_values(): void
    {
        Setting::query()->create([
            'company_name' => 'Balaji Royal Events',
            'phone' => '+91-9999999999',
            'email' => 'custom@example.com',
            'company_tagline' => 'Custom tagline kept',
        ]);

        $this->seed(SettingSeeder::class);

        $setting = Setting::query()->first();

        $this->assertSame('+91-9999999999', $setting->phone);
        $this->assertSame('custom@example.com', $setting->email);
        $this->assertSame('Custom tagline kept', $setting->company_tagline);
        $this->assertSame('balajievents19@gmail.com', $setting->support_email);
        $this->assertSame('+91-9462577065', $setting->whatsapp);
    }

    public function test_it_is_idempotent_and_never_sets_localhost_canonical_url(): void
    {
        Setting::query()->create([
            'company_name' => 'Balaji Royal Events',
        ]);

        $this->seed(SettingSeeder::class);
        $first = Setting::query()->first()->toArray();

        $this->seed(SettingSeeder::class);
        $second = Setting::query()->first()->toArray();

        $this->assertSame($first, $second);
        $this->assertNull($second['canonical_url']);
        $this->assertSame(1, Setting::query()->count());
        $this->assertStringNotContainsString('localhost', (string) json_encode($second));
    }
}
