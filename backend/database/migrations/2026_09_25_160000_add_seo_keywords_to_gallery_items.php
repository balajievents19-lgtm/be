<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('gallery_items', 'seo_keywords')) {
                $table->string('seo_keywords', 255)->nullable()->after('seo_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table): void {
            if (Schema::hasColumn('gallery_items', 'seo_keywords')) {
                $table->dropColumn('seo_keywords');
            }
        });
    }
};
