<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Rollback Content Studio tables only. Does not touch gallery_items or external_media.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::dropIfExists('studio_publication_logs');
            Schema::dropIfExists('studio_platform_connections');
            Schema::dropIfExists('studio_content_publications');
            Schema::dropIfExists('studio_content_service');
            Schema::dropIfExists('studio_content_media');
            Schema::dropIfExists('studio_contents');
        });
    }

    public function down(): void
    {
        // Irreversible rollback of Content Studio.
    }
};
