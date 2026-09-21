<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('General')
                            ->schema([
                                Section::make('Company')
                                    ->columns(2)
                                    ->schema(self::aboutFields()),
                                Section::make('Header')
                                    ->columns(2)
                                    ->schema(self::headerFields()),
                                Section::make('Contact')
                                    ->columns(2)
                                    ->schema(self::contactFields()),
                                Section::make('Social')
                                    ->columns(2)
                                    ->schema(self::socialFields()),
                                Section::make('Business')
                                    ->columns(2)
                                    ->schema(self::businessFields()),
                            ]),

                        Tab::make('Media')
                            ->schema([
                                Section::make('Brand')
                                    ->columns(2)
                                    ->schema(self::mediaFields()),
                            ]),

                        Tab::make('SEO')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema(self::seoFields()),
                            ]),

                        Tab::make('Publish')
                            ->schema([
                                Section::make('Theme')
                                    ->columns(3)
                                    ->schema(self::themeFields()),
                                Section::make('Footer')
                                    ->schema(self::footerFields()),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<int, mixed>
     */
    public static function aboutFields(): array
    {
        return [
            ...self::companyFields(),
            FileUpload::make('about_image')
                ->label('About Image')
                ->image()
                ->disk('public')
                ->directory('settings/about')
                ->visibility('public')
                ->imageEditor()
                ->maxSize(5120)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function companyFields(): array
    {
        return [
            TextInput::make('company_name')
                ->required()
                ->maxLength(255),
            TextInput::make('company_tagline')
                ->maxLength(255),
            Textarea::make('company_description')
                ->rows(4)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function contactFields(): array
    {
        return [
            TextInput::make('phone')->tel()->maxLength(50),
            TextInput::make('alternate_phone')->label('Alternate Phone')->tel()->maxLength(50),
            TextInput::make('whatsapp')->label('WhatsApp')->tel()->maxLength(50),
            TextInput::make('email')->email()->maxLength(255),
            TextInput::make('support_email')->label('Support Email')->email()->maxLength(255),
            Textarea::make('address')->rows(3)->columnSpanFull(),
            Textarea::make('google_map_embed')
                ->label('Google Map Embed')
                ->rows(4)
                ->helperText('Paste the Google Maps iframe embed HTML.')
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function socialFields(): array
    {
        return [
            TextInput::make('facebook')->url()->maxLength(255),
            TextInput::make('instagram')->url()->maxLength(255),
            TextInput::make('youtube')->label('YouTube')->url()->maxLength(255),
            TextInput::make('linkedin')->label('LinkedIn')->url()->maxLength(255),
            TextInput::make('twitter')->label('Twitter (X)')->url()->maxLength(255),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function businessFields(): array
    {
        return [
            Textarea::make('working_hours')->label('Working Hours')->rows(3)->columnSpanFull(),
            Textarea::make('holiday_text')->label('Holiday Text')->rows(3)->columnSpanFull(),
            TextInput::make('emergency_contact')->label('Emergency Contact')->maxLength(100),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function mediaFields(): array
    {
        return [
            FileUpload::make('logo')
                ->image()
                ->disk('public')
                ->directory('settings/brand')
                ->visibility('public')
                ->imageEditor()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
                ->maxSize(2048),
            FileUpload::make('dark_logo')
                ->label('Dark Logo')
                ->image()
                ->disk('public')
                ->directory('settings/brand')
                ->visibility('public')
                ->imageEditor()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
                ->maxSize(2048),
            FileUpload::make('footer_logo')
                ->label('Footer Logo')
                ->image()
                ->disk('public')
                ->directory('settings/brand')
                ->visibility('public')
                ->imageEditor()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
                ->maxSize(2048),
            FileUpload::make('favicon')
                ->image()
                ->disk('public')
                ->directory('settings/brand')
                ->visibility('public')
                ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/jpeg', 'image/svg+xml', 'image/webp'])
                ->maxSize(512),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function seoFields(): array
    {
        return [
            TextInput::make('meta_title')->label('Meta Title')->maxLength(255)->columnSpanFull(),
            Textarea::make('meta_description')->label('Meta Description')->rows(3)->columnSpanFull(),
            Textarea::make('meta_keywords')->label('Meta Keywords')->rows(2)->columnSpanFull(),
            FileUpload::make('opengraph_image')
                ->label('OpenGraph Image')
                ->image()
                ->disk('public')
                ->directory('settings/seo')
                ->visibility('public')
                ->imageEditor()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                ->maxSize(2048),
            TextInput::make('robots')->default('index, follow')->maxLength(100),
            TextInput::make('canonical_url')->label('Canonical URL')->url()->maxLength(255)->columnSpanFull(),
            TextInput::make('google_analytics_id')->label('Google Analytics ID')->maxLength(100),
            TextInput::make('google_search_console_verification')
                ->label('Google Search Console Verification')
                ->maxLength(255),
            TextInput::make('facebook_pixel_id')->label('Facebook Pixel ID')->maxLength(100),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function themeFields(): array
    {
        return [
            ColorPicker::make('primary_color')->required(),
            ColorPicker::make('secondary_color')->required(),
            Select::make('theme_mode')
                ->options([
                    'light' => 'Light',
                    'dark' => 'Dark',
                    'system' => 'System',
                ])
                ->default('light')
                ->required()
                ->native(false),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function footerFields(): array
    {
        return [
            Toggle::make('footer_enabled')
                ->label('Enable Footer')
                ->default(true),
            Toggle::make('footer_newsletter_enabled')
                ->label('Enable Newsletter')
                ->default(true),
            Toggle::make('footer_social_enabled')
                ->label('Enable Social Links')
                ->default(true),
            Textarea::make('footer_about')->label('Footer About')->rows(4),
            TextInput::make('copyright_text')->label('Copyright Text')->maxLength(255),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function headerFields(): array
    {
        return [
            Toggle::make('header_enabled')
                ->label('Enable Header')
                ->default(true),
            Toggle::make('sticky_header_enabled')
                ->label('Sticky Header')
                ->default(true),
            Toggle::make('top_bar_enabled')
                ->label('Enable Top Bar')
                ->default(true),
            TextInput::make('top_bar_text')
                ->label('Top Bar Text')
                ->maxLength(255),
            TextInput::make('header_cta_label')
                ->label('Header CTA Label')
                ->maxLength(100),
            TextInput::make('header_cta_url')
                ->label('Header CTA URL')
                ->maxLength(255),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function aboutVisionFields(): array
    {
        return [
            Textarea::make('about_vision')
                ->label('Our Vision')
                ->rows(5)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function aboutMissionFields(): array
    {
        return [
            Textarea::make('about_mission')
                ->label('Our Mission')
                ->rows(5)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function aboutJourneyFields(): array
    {
        return [
            Textarea::make('about_journey')
                ->label('Our Journey')
                ->rows(5)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function heroSearchFields(): array
    {
        return [
            Toggle::make('hero_search_enabled')
                ->label('Show enquiry form on homepage')
                ->default(true),
            TextInput::make('hero_search_placeholder')
                ->label('Enquiry form hint')
                ->maxLength(255),
            TextInput::make('hero_search_button_label')
                ->label('Enquiry button label')
                ->placeholder('Get a Free Quote')
                ->helperText('Shown on the homepage enquiry panel. Do not use “Search Now” unless the form actually searches.')
                ->maxLength(100),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function mobileHeaderFields(): array
    {
        return [
            Toggle::make('mobile_header_enabled')
                ->label('Enable Mobile Header')
                ->default(true),
            Select::make('mobile_menu_style')
                ->label('Mobile Menu Style')
                ->options([
                    'drawer' => 'Drawer (slides in from the side)',
                    'fullscreen' => 'Fullscreen',
                ])
                ->default('drawer')
                ->native(false),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function homepageSeoFields(): array
    {
        return [
            TextInput::make('homepage_seo_title')
                ->label('Homepage SEO Title')
                ->maxLength(255)
                ->columnSpanFull(),
            Textarea::make('homepage_seo_description')
                ->label('Homepage SEO Description')
                ->rows(3)
                ->columnSpanFull(),
            Textarea::make('homepage_seo_keywords')
                ->label('Homepage SEO Keywords')
                ->rows(2)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function robotsExtraFields(): array
    {
        return [
            TextInput::make('robots')
                ->label('Robots Directive')
                ->default('index, follow')
                ->maxLength(100),
            Textarea::make('robots_txt_extra')
                ->label('Extra robots.txt Rules')
                ->rows(4)
                ->helperText('Optional. Extra lines added to the generated robots.txt file.')
                ->columnSpanFull(),
        ];
    }
}
