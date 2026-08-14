<?php

namespace App\Filament\Resources\ServicePackages\Schemas;

use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServicePackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Package')->columnSpanFull()->tabs([
                Tab::make('General')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('name')->required()->maxLength(255)->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                                if (filled($get('slug'))) {
                                    return;
                                }
                                $set('slug', Str::slug((string) $state));
                            }),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)->alphaDash(),
                        Select::make('service_id')->label('Related Service')->relationship('service', 'name')->searchable()->preload()->native(false),
                        Select::make('service_category_id')->label('Category')->relationship('category', 'name')->searchable()->preload()->native(false),
                        TextInput::make('summary')->maxLength(255)->columnSpanFull(),
                        Textarea::make('description')->rows(4)->columnSpanFull(),
                        TextInput::make('price_label')->label('Price Label')->helperText('Example: Starting ₹49,999')->maxLength(100),
                        TextInput::make('price_amount')->label('Price Amount')->numeric(),
                        TextInput::make('currency')->default('INR')->maxLength(10),
                        TagsInput::make('features')->placeholder('Add feature')->columnSpanFull(),
                        Toggle::make('is_featured')->label('Featured Package')->inline(false),
                    ]),
                ]),
                Tab::make('Media')->schema([
                    Section::make()->schema([
                        FileUpload::make('image')->image()->disk('public')->directory('service-packages')->visibility('public')->imageEditor()->maxSize(4096),
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
