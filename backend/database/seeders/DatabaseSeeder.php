<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->call([
                RBACSeeder::class,
                SettingSeeder::class,
                EventTypeSeeder::class,
                HomepageSectionSeeder::class,
            ]);

            $this->command?->warn('Production seed: demo CMS, demo leads, and default admin were skipped.');

            return;
        }

        $this->call([
            AdminUserSeeder::class,
            RBACSeeder::class,
            SettingSeeder::class,
            EventTypeSeeder::class,
            HomepageSectionSeeder::class,
            HeroSlideSeeder::class,
            ServiceSeeder::class,
            GallerySeeder::class,
            BlogSeeder::class,
            ContactInquirySeeder::class,
            FaqSeeder::class,
            WebsiteCmsDemoSeeder::class,
        ]);
    }
}
