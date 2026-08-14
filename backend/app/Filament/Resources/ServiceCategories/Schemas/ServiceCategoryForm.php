<?php

namespace App\Filament\Resources\ServiceCategories\Schemas;

use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Category')->columnSpanFull()->tabs([
                Tab::make('General')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('name')->required()->maxLength(255)->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                                if (filled($get('slug'))) {
                                    return;
                                }
                                $set('slug', Str::slug((string) $state));
                            }),
                        TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true)->alphaDash(),
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                        TextInput::make('icon')->maxLength(100),
                    ]),
                ]),
                Tab::make('Media')->schema([
                    Section::make()->columns(2)->schema([
                        FileUpload::make('image')->image()->disk('public')->directory('service-categories')->visibility('public')->imageEditor()->maxSize(4096),
                        FileUpload::make('opengraph_image')->label('Social Image')->image()->disk('public')->directory('service-categories/seo')->visibility('public')->maxSize(2048),
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
                        ...WebsitePublishFields::statusAndSort(),
                        Toggle::make('is_visible')->label('Visible')->default(true)->inline(false),
                        ...WebsitePublishFields::schedule(),
                    ]),
                    Section::make('History')->columns(2)->schema(WebsitePublishFields::audit())->collapsed(),
                ]),
            ]),
        ]);
    }
}
