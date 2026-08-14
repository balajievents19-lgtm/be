<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->timestamp('publish_at')->nullable()->after('sort_order');
            $table->timestamp('unpublish_at')->nullable()->after('publish_at');
            $table->index(['publish_at', 'unpublish_at'], 'hero_slides_publish_window_idx');
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->timestamp('publish_at')->nullable()->after('sort_order');
            $table->timestamp('unpublish_at')->nullable()->after('publish_at');
            $table->index(['publish_at', 'unpublish_at'], 'services_publish_window_idx');
        });

        Schema::table('gallery_items', function (Blueprint $table): void {
            $table->timestamp('publish_at')->nullable()->after('sort_order');
            $table->timestamp('unpublish_at')->nullable()->after('publish_at');
            $table->index(['publish_at', 'unpublish_at'], 'gallery_items_publish_window_idx');
        });

        Schema::table('testimonials', function (Blueprint $table): void {
            $table->timestamp('publish_at')->nullable()->after('sort_order');
            $table->timestamp('unpublish_at')->nullable()->after('publish_at');
            $table->index(['publish_at', 'unpublish_at'], 'testimonials_publish_window_idx');
        });

        Schema::table('faqs', function (Blueprint $table): void {
            $table->timestamp('publish_at')->nullable()->after('sort_order');
            $table->timestamp('unpublish_at')->nullable()->after('publish_at');
            $table->index(['publish_at', 'unpublish_at'], 'faqs_publish_window_idx');
        });

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->timestamp('unpublish_at')->nullable()->after('published_at');
            $table->index(['published_at', 'unpublish_at'], 'blog_posts_publish_window_idx');
        });

        Schema::table('event_overviews', function (Blueprint $table): void {
            $table->timestamp('publish_at')->nullable()->after('sort_order');
            $table->timestamp('unpublish_at')->nullable()->after('publish_at');
            $table->index(['publish_at', 'unpublish_at'], 'event_overviews_publish_window_idx');
        });
    }

    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->dropIndex('hero_slides_publish_window_idx');
            $table->dropColumn(['publish_at', 'unpublish_at']);
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->dropIndex('services_publish_window_idx');
            $table->dropColumn(['publish_at', 'unpublish_at']);
        });

        Schema::table('gallery_items', function (Blueprint $table): void {
            $table->dropIndex('gallery_items_publish_window_idx');
            $table->dropColumn(['publish_at', 'unpublish_at']);
        });

        Schema::table('testimonials', function (Blueprint $table): void {
            $table->dropIndex('testimonials_publish_window_idx');
            $table->dropColumn(['publish_at', 'unpublish_at']);
        });

        Schema::table('faqs', function (Blueprint $table): void {
            $table->dropIndex('faqs_publish_window_idx');
            $table->dropColumn(['publish_at', 'unpublish_at']);
        });

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->dropIndex('blog_posts_publish_window_idx');
            $table->dropColumn(['unpublish_at']);
        });

        Schema::table('event_overviews', function (Blueprint $table): void {
            $table->dropIndex('event_overviews_publish_window_idx');
            $table->dropColumn(['publish_at', 'unpublish_at']);
        });
    }
};
