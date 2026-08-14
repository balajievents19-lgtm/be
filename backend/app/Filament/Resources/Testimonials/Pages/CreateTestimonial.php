<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Enums\TestimonialType;
use App\Filament\Resources\Testimonials\TestimonialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTestimonial extends CreateRecord
{
    protected static string $resource = TestimonialResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = TestimonialType::SuccessStory->value;

        return $data;
    }
}
