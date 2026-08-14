<?php

namespace App\Enums;

enum NavigationLinkTarget: string
{
    case SameTab = '_self';
    case NewTab = '_blank';

    public function label(): string
    {
        return match ($this) {
            self::SameTab => 'Same tab',
            self::NewTab => 'New tab',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $target): array => [$target->value => $target->label()])
            ->all();
    }
}
