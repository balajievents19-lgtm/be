<?php

use App\Support\Brand;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additional CMS tables for brand label normalization.
     */
    public function up(): void
    {
        $this->rewriteTable('event_types', ['name']);
        $this->rewriteTable('external_media', ['title', 'description']);
        $this->rewriteTable('navigation_items', ['label']);
        $this->rewriteTable('blog_categories', ['name', 'description', 'seo_title', 'seo_description']);
        $this->rewriteTable('faq_categories', ['name', 'description']);
        $this->rewriteTable('team_members', ['name', 'role', 'bio']);
        $this->rewriteTable('office_locations', ['name', 'address', 'city']);
        $this->rewriteTable('gallery_videos', ['title', 'caption', 'description']);
    }

    public function down(): void
    {
        // Brand rename is not reversed.
    }

    /**
     * @param  list<string>  $columns
     */
    private function rewriteTable(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $existing = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($table, $column)
        ));

        if ($existing === []) {
            return;
        }

        $rows = DB::table($table)->select(array_merge(['id'], $existing))->get();

        foreach ($rows as $row) {
            $updates = [];
            foreach ($existing as $column) {
                $value = $row->{$column};
                if (! is_string($value) || $value === '') {
                    continue;
                }
                $rewritten = Brand::rewrite($value);
                if ($rewritten !== null && $rewritten !== $value) {
                    $updates[$column] = $rewritten;
                }
            }

            if ($updates !== []) {
                DB::table($table)->where('id', $row->id)->update($updates);
            }
        }
    }
};
