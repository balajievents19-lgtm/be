<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_service_accesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_access')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit_own')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'service_id']);
        });

        Schema::create('staff_gallery_category_accesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gallery_category_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_access')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit_own')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'gallery_category_id'], 'staff_gallery_category_access_unique');
        });

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        $this->addModerationColumns('gallery_items');
        $this->addModerationColumns('external_media');
        $this->addModerationColumns('blog_posts');

        Schema::table('external_media', function (Blueprint $table): void {
            $table->foreignId('gallery_category_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->after('gallery_category_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('external_media', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('service_id');
            $table->dropConstrainedForeignId('gallery_category_id');
        });

        $this->dropModerationColumns('blog_posts');
        $this->dropModerationColumns('external_media');
        $this->dropModerationColumns('gallery_items');

        Schema::dropIfExists('notifications');
        Schema::dropIfExists('staff_gallery_category_accesses');
        Schema::dropIfExists('staff_service_accesses');
    }

    private function addModerationColumns(string $table): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($table): void {
            $blueprint->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $blueprint->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $blueprint->string('moderation_status', 32)->default('published');
            $blueprint->boolean('brand_review_required')->default(false);
            $blueprint->text('moderation_notes')->nullable();
            $blueprint->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $blueprint->timestamp('reviewed_at')->nullable();
            $blueprint->index('moderation_status', $table.'_moderation_status_index');
        });
    }

    private function dropModerationColumns(string $table): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($table): void {
            $blueprint->dropIndex($table.'_moderation_status_index');
            $blueprint->dropConstrainedForeignId('reviewed_by');
            $blueprint->dropConstrainedForeignId('updated_by');
            $blueprint->dropConstrainedForeignId('created_by');
            $blueprint->dropColumn([
                'moderation_status',
                'brand_review_required',
                'moderation_notes',
                'reviewed_at',
            ]);
        });
    }
};
