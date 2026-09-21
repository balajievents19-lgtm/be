<?php

use App\Models\HomepageSection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_key')->unique();
            $table->string('section_name');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        $rows = [];

        foreach (HomepageSection::DEFAULTS as $key => $meta) {
            $rows[] = [
                'section_key' => $key,
                'section_name' => $meta['name'],
                'is_active' => $meta['active'],
                'sort_order' => $meta['sort'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('homepage_sections')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }
};
