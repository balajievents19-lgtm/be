<?php

namespace App\Enums;

enum RedirectStatusCode: int
{
    case Permanent = 301;
    case Temporary = 302;

    public function label(): string
    {
        return match ($this) {
            self::Permanent => 'Permanent (301)',
            self::Temporary => 'Temporary (302)',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $code) => [$code->value => $code->label()])
            ->all();
    }
}
