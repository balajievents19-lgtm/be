<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'mobile_verified_at')) {
                $table->timestamp('mobile_verified_at')->nullable()->after('email_verified_at');
            }
            if (! Schema::hasColumn('customers', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
            if (! Schema::hasColumn('customers', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable();
            }
            if (! Schema::hasColumn('customers', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable();
            }
            if (! Schema::hasColumn('customers', 'pending_email')) {
                $table->string('pending_email')->nullable()->after('email');
            }
            if (! Schema::hasColumn('customers', 'pending_email_expires_at')) {
                $table->timestamp('pending_email_expires_at')->nullable();
            }
            if (! Schema::hasColumn('customers', 'pending_email_token_hash')) {
                $table->string('pending_email_token_hash')->nullable();
            }
        });

        $this->clearDuplicatePhones();

        $schema = Schema::getConnection()->getSchemaBuilder();
        $hasUnique = method_exists($schema, 'hasIndex')
            ? $schema->hasIndex('customers', 'customers_phone_unique')
            : false;

        if (! $hasUnique) {
            try {
                Schema::table('customers', function (Blueprint $table) {
                    $table->unique('phone');
                });
            } catch (\Throwable) {
                // Unique index already present.
            }
        }

        if (! Schema::hasTable('customer_otps')) {
            Schema::create('customer_otps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->string('purpose', 40);
                $table->string('phone', 20);
                $table->string('code_hash');
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->unsignedTinyInteger('max_attempts')->default(5);
                $table->string('channel', 20)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('expires_at');
                $table->timestamp('last_sent_at');
                $table->timestamp('consumed_at')->nullable();
                $table->timestamps();

                $table->index(['purpose', 'phone']);
                $table->index('expires_at');
            });
        }

        if (! Schema::hasTable('customer_sessions')) {
            Schema::create('customer_sessions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('laravel_session_id');
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 512)->nullable();
                $table->timestamp('last_activity_at');
                $table->timestamps();

                $table->index('customer_id');
                $table->index('laravel_session_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_sessions');
        Schema::dropIfExists('customer_otps');

        Schema::table('customers', function (Blueprint $table) {
            try {
                $table->dropUnique(['phone']);
            } catch (\Throwable) {
            }
            $drops = [];
            foreach ([
                'mobile_verified_at',
                'last_login_at',
                'last_login_ip',
                'password_changed_at',
                'pending_email',
                'pending_email_expires_at',
                'pending_email_token_hash',
            ] as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $drops[] = $column;
                }
            }
            if ($drops !== []) {
                $table->dropColumn($drops);
            }
        });
    }

    private function clearDuplicatePhones(): void
    {
        $duplicates = DB::table('customers')
            ->select('phone')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->groupBy('phone')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('phone');

        foreach ($duplicates as $phone) {
            $ids = DB::table('customers')->where('phone', $phone)->orderBy('id')->pluck('id');
            $keep = $ids->shift();
            if ($ids->isNotEmpty()) {
                DB::table('customers')->whereIn('id', $ids)->update(['phone' => null]);
            }
            unset($keep);
        }
    }
};
