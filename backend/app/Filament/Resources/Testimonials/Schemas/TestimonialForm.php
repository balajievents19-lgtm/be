<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use App\Enums\TestimonialType;
use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Testimonial')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('General')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Select::make('type')
                                            ->options(TestimonialType::options())
                                            ->default(TestimonialType::ClientSays->value)
                                            ->required()
                                            ->live()
                                            ->native(false),
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Textarea::make('quote')
                                            ->rows(4)
                                            ->columnSpanFull()
                                            ->visible(fn (Get $get): bool => $get('type') === TestimonialType::ClientSays->value)
                                            ->required(fn (Get $get): bool => $get('type') === TestimonialType::ClientSays->value),
                                        Textarea::make('body')
                                            ->label('Story')
                                            ->rows(4)
                                            ->columnSpanFull()
                                            ->visible(fn (Get $get): bool => $get('type') === TestimonialType::SuccessStory->value)
                                            ->required(fn (Get $get): bool => $get('type') === TestimonialType::SuccessStory->value),
                                        WebsitePublishFields::previewPlaceholder('/'),
                                    ]),
                            ]),

                        Tab::make('Media')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        FileUpload::make('avatar')
                                            ->label('Avatar')
                                            ->image()
                                            ->disk('public')
                                            ->directory('testimonials/avatars')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(2048)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                            ->visible(fn (Get $get): bool => $get('type') === TestimonialType::ClientSays->value),
                                        FileUpload::make('image')
                                            ->label('Story Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('testimonials/stories')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(5120)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                            ->visible(fn (Get $get): bool => $get('type') === TestimonialType::SuccessStory->value),
                                    ]),
                            ]),

                        Tab::make('Publish')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        ...WebsitePublishFields::statusAndSort(withHomepage: true),
                                        Toggle::make('featured')
                                            ->label('Featured')
                                            ->default(false)
                                            ->inline(false),
                                        TextInput::make('rating')
                                            ->label('Star Rating')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(5)
                                            ->helperText('Optional rating from 1 to 5 stars.'),
                                        TextInput::make('video_url')
                                            ->label('Video Link')
                                            ->url()
                                            ->maxLength(500)
                                            ->columnSpanFull()
                                            ->helperText('Optional YouTube or Vimeo link.'),
                                        ...WebsitePublishFields::schedule(),
                                    ]),
                                Section::make('History')
                                    ->columns(2)
                                    ->schema(WebsitePublishFields::audit())
                                    ->collapsed(),
                            ]),
                    ]),
            ]);
    }
}
