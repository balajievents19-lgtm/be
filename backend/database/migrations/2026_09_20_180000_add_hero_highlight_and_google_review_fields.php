<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive CMS fields only. Does not rewrite existing slide copy or company_name.
     */
    public function up(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->string('title_highlight')->nullable()->after('title');
            $table->string('secondary_button_text')->nullable()->after('button_url');
            $table->string('secondary_button_url')->nullable()->after('secondary_button_text');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->string('google_place_id')->nullable()->after('google_map_embed');
            $table->string('google_reviews_url')->nullable()->after('google_place_id');
        });
    }

    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropColumn([
                'title_highlight',
                'secondary_button_text',
                'secondary_button_url',
            ]);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'google_place_id',
                'google_reviews_url',
            ]);
        });
    }
};
