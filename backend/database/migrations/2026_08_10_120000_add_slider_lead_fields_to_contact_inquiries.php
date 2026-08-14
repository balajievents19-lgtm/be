<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_inquiries', function (Blueprint $table): void {
            $table->string('event_location')->nullable()->after('event_date');
            $table->string('source', 64)->nullable()->after('budget');
        });
    }

    public function down(): void
    {
        Schema::table('contact_inquiries', function (Blueprint $table): void {
            $table->dropColumn(['event_location', 'source']);
        });
    }
};
