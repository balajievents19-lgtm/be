<?php

namespace App\Filament\Resources\Faqs\Schemas;

use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('FAQ')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('General')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Select::make('faq_category_id')
                                            ->label('Category')
                                            ->relationship(
                                                name: 'category',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query) => $query->ordered(),
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        TextInput::make('question')
                                            ->required()
                                            ->maxLength(500)
                                            ->columnSpanFull()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                                                if (filled($get('slug'))) {
                                                    return;
                                                }

                                                $set('slug', Str::slug(Str::limit((string) $state, 80, '')));
                                            }),
                                        TextInput::make('slug')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->alphaDash()
                                            ->helperText('Auto-generated from question; editable.'),
                                        WebsitePublishFields::previewPlaceholder('/faq'),
                                        RichEditor::make('answer')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                                'orderedList',
                                                'link',
                                                'undo',
                                                'redo',
                                            ]),
                                    ]),
                            ]),

                        Tab::make('SEO')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('SEO Title')
                                            ->maxLength(255),
                                        Textarea::make('seo_description')
                                            ->label('SEO Description')
                                            ->rows(3),
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
