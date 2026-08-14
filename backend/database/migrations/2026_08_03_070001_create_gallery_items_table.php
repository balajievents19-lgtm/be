<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_category_id')
                ->constrained('gallery_categories')
                ->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image');
            $table->string('thumbnail')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('vimeo_url')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('homepage_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('opengraph_image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
            $table->index(['homepage_featured', 'status']);
            $table->index(['featured', 'status']);
            $table->index(['gallery_category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
    }
};
