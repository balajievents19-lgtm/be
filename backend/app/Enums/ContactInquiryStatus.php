<?php

namespace App\Enums;

enum ContactInquiryStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case FollowUp = 'follow_up';
    case QuotationSent = 'quotation_sent';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::FollowUp => 'Follow Up',
            self::QuotationSent => 'Quotation Sent',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Contacted => 'primary',
            self::FollowUp => 'warning',
            self::QuotationSent => 'gray',
            self::Won => 'success',
            self::Lost => 'danger',
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
