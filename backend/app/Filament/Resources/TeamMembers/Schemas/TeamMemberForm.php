<?php

namespace App\Filament\Resources\TeamMembers\Schemas;

use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Team Member')->columnSpanFull()->tabs([
                Tab::make('General')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('role')->label('Role / Title')->maxLength(255),
                        Textarea::make('bio')->rows(4)->columnSpanFull(),
                        TextInput::make('email')->email()->maxLength(255),
                        TextInput::make('phone')->maxLength(50),
                        TextInput::make('social_linkedin')->label('LinkedIn')->url()->maxLength(255),
                        TextInput::make('social_instagram')->label('Instagram')->url()->maxLength(255),
                    ]),
                ]),
                Tab::make('Media')->schema([
                    Section::make()->schema([
                        FileUpload::make('photo')->image()->disk('public')->directory('team')->visibility('public')->imageEditor()->maxSize(4096),
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
