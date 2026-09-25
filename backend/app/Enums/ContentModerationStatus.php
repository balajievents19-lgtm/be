<?php

namespace App\Enums;

enum ContentModerationStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case BrandReview = 'brand_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Published = 'published';

    /**
     * @return list<string>
     */
    public static function publicValues(): array
    {
        return [
            self::Approved->value,
            self::Published->value,
        ];
    }

    /**
     * @return list<string>
     */
    public static function reviewQueueValues(): array
    {
        return [
            self::PendingReview->value,
            self::BrandReview->value,
            self::Draft->value,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingReview => 'Pending review',
            self::BrandReview => 'Brand review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Published => 'Published',
        };
    }
}
