<?php

namespace App\Filament\Resources\CtaSections\Schemas;

use App\Enums\CtaSectionKey;
use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CtaSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('CTA')->columnSpanFull()->tabs([
                Tab::make('General')->schema([
                    Section::make()->columns(2)->schema([
                        Select::make('key')->label('Section')->options(CtaSectionKey::options())->required()->native(false),
                        TextInput::make('title')->required()->maxLength(255),
                        TextInput::make('subtitle')->maxLength(255)->columnSpanFull(),
                        Textarea::make('body')->rows(3)->columnSpanFull(),
                        TextInput::make('button_text')->label('Button Label')->maxLength(100),
                        TextInput::make('button_url')->label('Button Link')->maxLength(255),
                        TextInput::make('secondary_button_text')->label('Second Button Label')->maxLength(100),
                        TextInput::make('secondary_button_url')->label('Second Button Link')->maxLength(255),
                    ]),
                ]),
                Tab::make('Media')->schema([
                    Section::make()->schema([
                        FileUpload::make('background_image')->label('Background Image')->image()->disk('public')->directory('cta')->visibility('public')->imageEditor()->maxSize(5120),
                    ]),
                ]),
                Tab::make('SEO')->schema([
                    Section::make()->schema([
                        TextInput::make('seo_title')->label('SEO Title')->maxLength(255),
                        Textarea::make('seo_description')->label('SEO Description')->rows(3),
                    ]),
                ]),
                Tab::make('Publish')->schema([
                    Section::make()->columns(2)->schema([
                        ...WebsitePublishFields::statusAndSort(withHomepage: true, homepageField: 'show_on_homepage'),
                        Toggle::make('is_visible')->label('Visible')->default(true)->inline(false),
                        ...WebsitePublishFields::schedule(),
                    ]),
                    Section::make('History')->columns(2)->schema(WebsitePublishFields::audit())->collapsed(),
                ]),
            ]),
        ]);
    }
}
