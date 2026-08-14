<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // General
            $table->string('company_name');
            $table->string('company_tagline')->nullable();
            $table->text('company_description')->nullable();

            // Brand
            $table->string('logo')->nullable();
            $table->string('dark_logo')->nullable();
            $table->string('footer_logo')->nullable();
            $table->string('favicon')->nullable();

            // Contact
            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('support_email')->nullable();
            $table->text('address')->nullable();
            $table->text('google_map_embed')->nullable();

            // Social
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('twitter')->nullable();

            // Business
            $table->text('working_hours')->nullable();
            $table->text('holiday_text')->nullable();
            $table->string('emergency_contact')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('opengraph_image')->nullable();
            $table->string('robots')->default('index, follow');
            $table->string('canonical_url')->nullable();
            $table->string('google_analytics_id')->nullable();
            $table->string('google_search_console_verification')->nullable();
            $table->string('facebook_pixel_id')->nullable();

            // Theme
            $table->string('primary_color', 20)->default('#f15b22');
            $table->string('secondary_color', 20)->default('#0e1123');
            $table->string('theme_mode', 20)->default('light');

            // Footer
            $table->text('footer_about')->nullable();
            $table->string('copyright_text')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
