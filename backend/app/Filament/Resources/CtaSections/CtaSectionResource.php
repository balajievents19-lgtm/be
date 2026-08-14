<?php

namespace App\Filament\Resources\CtaSections;

use App\Filament\Clusters\HomeCluster;
use App\Filament\Resources\CtaSections\Pages\CreateCtaSection;
use App\Filament\Resources\CtaSections\Pages\EditCtaSection;
use App\Filament\Resources\CtaSections\Pages\ListCtaSections;
use App\Filament\Resources\CtaSections\Schemas\CtaSectionForm;
use App\Filament\Resources\CtaSections\Tables\CtaSectionsTable;
use App\Models\CtaSection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CtaSectionResource extends Resource
{
    protected static ?string $model = CtaSection::class;

    protected static ?string $cluster = HomeCluster::class;

    protected static ?string $navigationLabel = 'CTA Sections';

    protected static ?string $modelLabel = 'CTA Section';

    protected static ?string $pluralModelLabel = 'CTA Sections';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 8;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return CtaSectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CtaSectionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCtaSections::route('/'),
            'create' => CreateCtaSection::route('/create'),
            'edit' => EditCtaSection::route('/{record}/edit'),
        ];
    }
}
