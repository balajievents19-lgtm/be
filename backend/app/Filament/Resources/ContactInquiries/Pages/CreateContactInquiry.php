<?php

namespace App\Filament\Resources\ContactInquiries\Pages;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContactInquiry extends CreateRecord
{
    protected static string $resource = ContactInquiryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] ??= ContactInquiryStatus::New->value;
        $data['priority'] ??= ContactInquiryPriority::Medium->value;

        return $data;
    }
}
