<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->boolean('google_reviews_featured')->default(false)->after('homepage_featured');
            $table->index(['type', 'status', 'google_reviews_featured', 'sort_order'], 'testimonials_google_reviews_featured_idx');
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropIndex('testimonials_google_reviews_featured_idx');
            $table->dropColumn('google_reviews_featured');
        });
    }
};
