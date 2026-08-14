<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->boolean('header_enabled')->default(true)->after('theme_mode');
            $table->boolean('top_bar_enabled')->default(true)->after('header_enabled');
            $table->boolean('sticky_header_enabled')->default(true)->after('top_bar_enabled');
            $table->string('top_bar_text')->nullable()->after('sticky_header_enabled');
            $table->string('header_cta_label')->nullable()->after('top_bar_text');
            $table->string('header_cta_url')->nullable()->after('header_cta_label');
            $table->boolean('footer_enabled')->default(true)->after('copyright_text');
            $table->boolean('footer_newsletter_enabled')->default(true)->after('footer_enabled');
            $table->boolean('footer_social_enabled')->default(true)->after('footer_newsletter_enabled');
        });

        Schema::create('navigation_items', function (Blueprint $table): void {
            $table->id();
            $table->string('label');
            $table->string('url');
            $table->string('target', 10)->default('_self');
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('navigation_items')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->boolean('show_on_header')->default(true);
            $table->boolean('show_on_footer')->default(false);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('unpublish_at')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_visible', 'sort_order'], 'nav_items_visible_sort_idx');
            $table->index(['show_on_header', 'status', 'is_visible', 'sort_order'], 'nav_items_header_visible_idx');
            $table->index(['show_on_footer', 'status', 'is_visible', 'sort_order'], 'nav_items_footer_visible_idx');
            $table->index(['publish_at', 'unpublish_at'], 'nav_items_publish_window_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_items');

        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn([
                'header_enabled',
                'top_bar_enabled',
                'sticky_header_enabled',
                'top_bar_text',
                'header_cta_label',
                'header_cta_url',
                'footer_enabled',
                'footer_newsletter_enabled',
                'footer_social_enabled',
            ]);
        });
    }
};
