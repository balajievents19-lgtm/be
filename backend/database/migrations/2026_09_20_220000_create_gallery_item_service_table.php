<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_item_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_item_id')
                ->constrained('gallery_items')
                ->cascadeOnDelete();
            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['gallery_item_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_item_service');
    }
};
