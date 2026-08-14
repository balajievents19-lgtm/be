<?php

namespace App\Filament\Resources\Redirects\Schemas;

use App\Enums\RedirectStatusCode;
use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Redirect')->columnSpanFull()->tabs([
                Tab::make('General')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('from_path')->label('From Path')->required()->maxLength(255)->helperText('Example: /old-page'),
                        TextInput::make('to_url')->label('Send Visitors To')->required()->maxLength(500),
                        Select::make('status_code')->label('Redirect Type')->options(RedirectStatusCode::options())->default(301)->required()->native(false),
                        Textarea::make('notes')->label('Internal Notes')->rows(3)->columnSpanFull(),
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
