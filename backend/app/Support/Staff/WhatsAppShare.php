<?php

namespace App\Support\Staff;

use App\Models\GalleryItem;
use Illuminate\Support\Str;

final class WhatsAppShare
{
    public static function message(GalleryItem $item): string
    {
        $item->loadMissing('category');
        $site = rtrim((string) (config('seo.site_url') ?: config('app.url')), '/');
        $categorySlug = $item->category?->slug;
        $url = $categorySlug
            ? $site.'/gallery/'.$categorySlug
            : $site.'/gallery';

        $description = trim(Str::limit(strip_tags((string) ($item->seo_description ?: $item->description ?: $item->caption)), 140));
        $hashtags = '#BalajiRoyalEvents';
        if (filled($item->category?->name)) {
            $hashtags .= ' #'.Str::studly(Str::slug($item->category->name, ''));
        }

        return trim($item->title."\n".$description."\n".$url."\n".$hashtags);
    }

    public static function url(GalleryItem $item): string
    {
        return 'https://wa.me/?text='.rawurlencode(self::message($item));
    }
}
