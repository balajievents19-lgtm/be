<?php

namespace App\Models;

use App\Models\Concerns\Publication\HasPublicationWindow;
use App\Services\Seo\SeoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Faq extends Model
{
    use HasPublicationWindow;
    use SoftDeletes;

    protected $fillable = [
        'faq_category_id',
        'question',
        'slug',
        'answer',
        'featured',
        'homepage_featured',
        'sort_order',
        'status',
        'publish_at',
        'unpublish_at',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'homepage_featured' => 'boolean',
            'status' => 'boolean',
            'sort_order' => 'integer',
            'publish_at' => 'datetime',
            'unpublish_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Faq $faq): void {
            if (blank($faq->slug) && filled($faq->question)) {
                $faq->slug = static::uniqueSlugFrom($faq->question, $faq->id);
            }
        });
    }

    public static function uniqueSlugFrom(string $question, ?int $ignoreId = null): string
    {
        $base = Str::slug(Str::limit($question, 80, '')) ?: 'faq';
        $slug = $base;
        $counter = 1;

        while (
            static::query()
                ->withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true)->withinPublicationWindow();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeHomepage(Builder $query): Builder
    {
        return $query->where('homepage_featured', true);
    }

    public function plainAnswer(): string
    {
        return trim(html_entity_decode(strip_tags((string) $this->answer)));
    }

    /**
     * Google FAQPage JSON-LD schema for rich results.
     *
     * @param  Collection<int, Faq>|iterable<Faq>  $faqs
     * @return array<string, mixed>
     */
    public static function faqPageSchema(iterable $faqs): array
    {
        return app(SeoService::class)->schema()->faqPage($faqs);
    }
}
