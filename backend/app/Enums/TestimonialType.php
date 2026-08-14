<?php

namespace App\Enums;

enum TestimonialType: string
{
    case ClientSays = 'client_says';
    case SuccessStory = 'success_story';

    public function label(): string
    {
        return match ($this) {
            self::ClientSays => 'Client Says',
            self::SuccessStory => 'Success Story',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->all();
    }
}
