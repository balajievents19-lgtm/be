<?php

namespace App\Filament\Resources\Statistics\Schemas;

use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class StatisticForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Statistic')->columnSpanFull()->tabs([
                Tab::make('General')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('label')->required()->maxLength(255),
                        TextInput::make('value')->required()->maxLength(100),
                        TextInput::make('suffix')->maxLength(50)->helperText('Example: +, %'),
                        TextInput::make('icon')->maxLength(100),
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
