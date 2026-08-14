<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 30);
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('service_interested')->nullable();
            $table->date('event_date')->nullable();
            $table->string('budget')->nullable();
            $table->string('status')->default('new');
            $table->string('priority')->default('medium');
            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['priority', 'status']);
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_inquiries');
    }
};
