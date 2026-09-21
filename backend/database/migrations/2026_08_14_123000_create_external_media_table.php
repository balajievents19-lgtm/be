<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_media', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('media_type', 32)->default('video'); // video | social_post | external
            $table->string('provider', 32)->default('other'); // youtube|instagram|facebook|google_drive|vimeo|other
            $table->string('url', 2048);
            $table->string('thumbnail')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('homepage_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('unpublish_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
            $table->index(['provider', 'media_type']);
            $table->index('homepage_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_media');
    }
};
