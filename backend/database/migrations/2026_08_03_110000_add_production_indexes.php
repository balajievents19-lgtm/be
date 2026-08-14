<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_inquiries', function (Blueprint $table) {
            $table->index('mobile');
            $table->index('email');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index('deleted_at');
        });

        Schema::table('gallery_items', function (Blueprint $table) {
            $table->index('deleted_at');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->index('deleted_at');
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('contact_inquiries', function (Blueprint $table) {
            $table->dropIndex(['mobile']);
            $table->dropIndex(['email']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
    }
};
