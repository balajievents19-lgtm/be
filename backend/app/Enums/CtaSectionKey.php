<?php

namespace App\Enums;

enum CtaSectionKey: string
{
    case HomeMid = 'home_mid';
    case HomeBottom = 'home_bottom';
    case AboutBanner = 'about_banner';
    case ServicesBanner = 'services_banner';
    case ContactBanner = 'contact_banner';

    public function label(): string
    {
        return match ($this) {
            self::HomeMid => 'Homepage Middle',
            self::HomeBottom => 'Homepage Bottom',
            self::AboutBanner => 'About Page Banner',
            self::ServicesBanner => 'Services Page Banner',
            self::ContactBanner => 'Contact Page Banner',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $key) => [$key->value => $key->label()])
            ->all();
    }
}
