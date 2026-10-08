<?php

namespace App\Filament\Resources\ClientReviews\Pages;

use App\Enums\TestimonialType;
use App\Filament\Resources\ClientReviews\ClientReviewResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClientReview extends CreateRecord
{
    protected static string $resource = ClientReviewResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = TestimonialType::ClientSays->value;

        return $data;
    }
}
