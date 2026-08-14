<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->text('about_vision')->nullable()->after('about_image');
            $table->text('about_mission')->nullable()->after('about_vision');
            $table->text('about_journey')->nullable()->after('about_mission');
            $table->boolean('mobile_header_enabled')->default(true)->after('header_cta_url');
            $table->string('mobile_menu_style')->nullable()->default('drawer')->after('mobile_header_enabled');
            $table->boolean('hero_search_enabled')->default(true)->after('mobile_menu_style');
            $table->string('hero_search_placeholder')->nullable()->after('hero_search_enabled');
            $table->string('hero_search_button_label')->nullable()->after('hero_search_placeholder');
            $table->string('homepage_seo_title')->nullable()->after('meta_keywords');
            $table->text('homepage_seo_description')->nullable()->after('homepage_seo_title');
            $table->text('homepage_seo_keywords')->nullable()->after('homepage_seo_description');
            $table->text('robots_txt_extra')->nullable()->after('robots');
            $table->boolean('sitemap_enabled')->default(true)->after('robots_txt_extra');
        });

        Schema::create('team_members', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('role')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_instagram')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('unpublish_at')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'sort_order'], 'team_members_status_sort_idx');
            $table->index(['publish_at', 'unpublish_at'], 'team_members_publish_idx');
        });

        Schema::create('statistics', function (Blueprint $table): void {
            $table->id();
            $table->string('label');
            $table->string('value');
            $table->string('icon')->nullable();
            $table->string('suffix')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->boolean('show_on_homepage')->default(true);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('unpublish_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'sort_order'], 'statistics_status_sort_idx');
            $table->index(['publish_at', 'unpublish_at'], 'statistics_publish_idx');
        });

        Schema::create('cta_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('body')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('secondary_button_text')->nullable();
            $table->string('secondary_button_url')->nullable();
            $table->string('background_image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->boolean('show_on_homepage')->default(true);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('unpublish_at')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'sort_order'], 'cta_sections_status_sort_idx');
            $table->index(['publish_at', 'unpublish_at'], 'cta_sections_publish_idx');
        });

        Schema::create('office_locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('map_embed')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('unpublish_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'sort_order'], 'office_locations_status_sort_idx');
            $table->index(['publish_at', 'unpublish_at'], 'office_locations_publish_idx');
        });

        Schema::create('service_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('unpublish_at')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('opengraph_image')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'sort_order'], 'service_categories_status_sort_idx');
            $table->index(['publish_at', 'unpublish_at'], 'service_categories_publish_idx');
        });

        Schema::create('service_packages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('service_category_id')->nullable()->constrained('service_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('summary')->nullable();
            $table->text('description')->nullable();
            $table->string('price_label')->nullable();
            $table->decimal('price_amount', 12, 2)->nullable();
            $table->string('currency', 10)->default('INR');
            $table->json('features')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('unpublish_at')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'sort_order'], 'service_packages_status_sort_idx');
            $table->index(['publish_at', 'unpublish_at'], 'service_packages_publish_idx');
        });

        Schema::create('redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('from_path');
            $table->string('to_url');
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('status')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('unpublish_at')->nullable();
            $table->timestamps();
            $table->unique('from_path', 'redirects_from_path_uidx');
            $table->index(['status', 'sort_order'], 'redirects_status_sort_idx');
        });

        Schema::table('testimonials', function (Blueprint $table): void {
            $table->unsignedTinyInteger('rating')->nullable()->after('image');
            $table->string('video_url')->nullable()->after('rating');
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->foreignId('service_category_id')
                ->nullable()
                ->after('id')
                ->constrained('service_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('service_category_id');
        });

        Schema::table('testimonials', function (Blueprint $table): void {
            $table->dropColumn(['rating', 'video_url']);
        });

        Schema::dropIfExists('redirects');
        Schema::dropIfExists('service_packages');
        Schema::dropIfExists('service_categories');
        Schema::dropIfExists('office_locations');
        Schema::dropIfExists('cta_sections');
        Schema::dropIfExists('statistics');
        Schema::dropIfExists('team_members');

        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn([
                'about_vision',
                'about_mission',
                'about_journey',
                'mobile_header_enabled',
                'mobile_menu_style',
                'hero_search_enabled',
                'hero_search_placeholder',
                'hero_search_button_label',
                'homepage_seo_title',
                'homepage_seo_description',
                'homepage_seo_keywords',
                'robots_txt_extra',
                'sitemap_enabled',
            ]);
        });
    }
};
