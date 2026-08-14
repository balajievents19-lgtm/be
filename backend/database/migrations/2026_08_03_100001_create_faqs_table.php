<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_category_id')
                ->constrained('faq_categories')
                ->cascadeOnDelete();
            $table->string('question');
            $table->string('slug')->unique();
            $table->longText('answer')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('homepage_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
            $table->index(['homepage_featured', 'status']);
            $table->index(['featured', 'status']);
            $table->index(['faq_category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
