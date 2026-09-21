<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    /**
     * Canonical homepage section keys and labels (frontend names).
     *
     * @var array<string, array{name: string, active: bool, sort: int}>
     */
    public const DEFAULTS = [
        'slider' => ['name' => 'Slider', 'active' => true, 'sort' => 1],
        'about' => ['name' => 'About', 'active' => false, 'sort' => 2],
        'services' => ['name' => 'Services', 'active' => true, 'sort' => 3],
        'gallery' => ['name' => 'Gallery', 'active' => true, 'sort' => 4],
        'packages' => ['name' => 'Packages', 'active' => false, 'sort' => 5],
        'testimonials' => ['name' => 'Testimonials', 'active' => true, 'sort' => 6],
        'blog' => ['name' => 'Blog', 'active' => true, 'sort' => 7],
        'faq' => ['name' => 'FAQ', 'active' => false, 'sort' => 8],
        'contact' => ['name' => 'Contact', 'active' => true, 'sort' => 9],
    ];

    protected $fillable = [
        'section_key',
        'section_name',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public static function ensureDefaults(): void
    {
        foreach (self::DEFAULTS as $key => $meta) {
            static::query()->firstOrCreate(
                ['section_key' => $key],
                [
                    'section_name' => $meta['name'],
                    'is_active' => $meta['active'],
                    'sort_order' => $meta['sort'],
                ],
            );
        }
    }

    /**
     * @return array<string, bool>
     */
    public static function visibilityMap(): array
    {
        static::ensureDefaults();

        $stored = static::query()
            ->ordered()
            ->get(['section_key', 'is_active'])
            ->mapWithKeys(fn (self $section): array => [
                $section->section_key => (bool) $section->is_active,
            ])
            ->all();

        $defaults = [];
        foreach (self::DEFAULTS as $key => $meta) {
            $defaults[$key] = $meta['active'];
        }

        return array_merge($defaults, $stored);
    }
}
