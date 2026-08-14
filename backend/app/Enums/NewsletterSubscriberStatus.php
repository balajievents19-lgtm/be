<?php

namespace App\Enums;

enum NewsletterSubscriberStatus: string
{
    case Active = 'active';
    case Unsubscribed = 'unsubscribed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Unsubscribed => 'Unsubscribed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Unsubscribed => 'gray',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}
