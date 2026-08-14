<?php

namespace App\Filament\Pages\Contact;

use App\Filament\Clusters\ContactCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;

class ManageContactFormSettings extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'contact';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = ContactCluster::class;

    protected static ?string $navigationLabel = 'Contact Form';

    protected static ?string $title = 'Contact Form';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static ?string $slug = 'contact-form';

    protected function previewPath(): string
    {
        return '/contact';
    }

    protected function formFields(): array
    {
        return [
            Placeholder::make('contact_form_help')
                ->label('How this works')
                ->content('When a visitor submits the contact form, the enquiry appears in your Enquiries inbox. Use the button above to review new enquiries.'),
            TextInput::make('email')
                ->label('Enquiries Email')
                ->email()
                ->maxLength(255)
                ->helperText('Where new enquiries are sent.'),
            TextInput::make('phone')
                ->label('Contact Phone')
                ->tel()
                ->maxLength(50),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('goToInquiries')
                ->label('View Enquiries')
                ->icon(Heroicon::OutlinedInboxArrowDown)
                ->url(ContactInquiryResource::getUrl('index')),
            ...parent::getHeaderActions(),
        ];
    }
}
