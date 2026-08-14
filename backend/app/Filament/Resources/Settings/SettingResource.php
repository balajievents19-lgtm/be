<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages\ManageSettings;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use App\Models\Setting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationLabel = 'Brand';

    protected static ?string $modelLabel = 'Website Settings';

    protected static ?string $pluralModelLabel = 'Website Settings';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $slug = 'settings';

    /** Brand / Header / Footer / SEO pages cover settings for owners. */
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return SettingForm::configure($schema);
    }

    public static function canCreate(): bool
    {
        return Setting::query()->count() === 0;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSettings::route('/'),
        ];
    }
}
