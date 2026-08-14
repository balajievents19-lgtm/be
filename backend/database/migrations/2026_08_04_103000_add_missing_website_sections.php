<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('about_image')->nullable()->after('company_description');
        });

        Schema::create('event_overviews', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('caption')->nullable();
            $table->text('description')->nullable();
            $table->string('image');
            $table->string('link_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('featured')->default(false);
            $table->boolean('homepage_featured')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'homepage_featured', 'sort_order']);
            $table->index('deleted_at');
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32)->default('client_says');
            $table->string('name');
            $table->text('quote')->nullable();
            $table->text('body')->nullable();
            $table->string('avatar')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('featured')->default(false);
            $table->boolean('homepage_featured')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status', 'homepage_featured', 'sort_order']);
            $table->index('deleted_at');
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('status', 32)->default('active');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('event_overviews');

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('about_image');
        });
    }
};
