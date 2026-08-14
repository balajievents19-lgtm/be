<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->string('featured_image');
            $table->string('banner_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('featured')->default(false);
            $table->boolean('status')->default(true);
            $table->boolean('show_on_homepage')->default(false);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->string('opengraph_image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
            $table->index(['show_on_homepage', 'status']);
            $table->index(['featured', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
