<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')
                ->constrained('blog_categories')
                ->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('alt_text')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('homepage_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->boolean('status')->default(true);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('opengraph_image')->nullable();
            $table->string('schema_type')->default('BlogPosting');
            $table->unsignedInteger('reading_time')->nullable();
            $table->string('author')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['homepage_featured', 'status']);
            $table->index(['featured', 'status']);
            $table->index(['blog_category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
