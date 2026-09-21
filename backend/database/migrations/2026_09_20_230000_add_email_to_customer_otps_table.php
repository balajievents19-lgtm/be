<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_otps')) {
            return;
        }

        Schema::table('customer_otps', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_otps', 'email')) {
                $table->string('email')->nullable()->after('phone');
                $table->index(['purpose', 'email']);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_otps') || ! Schema::hasColumn('customer_otps', 'email')) {
            return;
        }

        Schema::table('customer_otps', function (Blueprint $table) {
            $table->dropIndex(['purpose', 'email']);
            $table->dropColumn('email');
        });
    }
};
