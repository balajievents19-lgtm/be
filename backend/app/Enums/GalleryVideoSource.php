<?php

namespace App\Enums;

enum GalleryVideoSource: string
{
    case Youtube = 'youtube';
    case Instagram = 'instagram';
    case Facebook = 'facebook';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Youtube => 'YouTube',
            self::Instagram => 'Instagram',
            self::Facebook => 'Facebook',
            self::Other => 'Other Link',
        };
    }

    public function urlLabel(): string
    {
        return match ($this) {
            self::Youtube => 'YouTube Video URL',
            self::Instagram => 'Instagram Reel/Post URL',
            self::Facebook => 'Facebook Video URL',
            self::Other => 'Video URL',
        };
    }

    public function urlHelper(): string
    {
        return match ($this) {
            self::Youtube => 'Example: https://www.youtube.com/watch?v=XXXXXXXX or https://youtu.be/XXXXXXXX',
            self::Instagram => 'Example: https://www.instagram.com/reel/XXXXXXXX/',
            self::Facebook => 'Example: https://www.facebook.com/.../',
            self::Other => 'HTTPS URL only. The video file is not downloaded to this server.',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $source) => [$source->value => $source->label()])
            ->all();
    }
}
