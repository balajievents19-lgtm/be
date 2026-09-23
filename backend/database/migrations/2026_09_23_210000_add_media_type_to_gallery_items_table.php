<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->string('media_type', 16)->default('image');
            $table->string('video_source', 32)->nullable();
            $table->string('video_url', 2048)->nullable();
            $table->index('media_type');
        });

        DB::table('gallery_items')->whereNull('media_type')->update(['media_type' => 'image']);
    }

    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropIndex(['media_type']);
            $table->dropColumn(['media_type', 'video_source', 'video_url']);
        });
    }
};
