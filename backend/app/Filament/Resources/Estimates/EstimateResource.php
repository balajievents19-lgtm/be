<?php

namespace App\Filament\Resources\Estimates;

use App\Filament\Resources\Estimates\Pages\CreateEstimate;
use App\Filament\Resources\Estimates\Pages\EditEstimate;
use App\Filament\Resources\Estimates\Pages\ListEstimates;
use App\Filament\Resources\Estimates\Pages\ViewEstimateCosting;
use App\Filament\Resources\Estimates\Schemas\EstimateForm;
use App\Filament\Resources\Estimates\Tables\EstimatesTable;
use App\Models\Estimate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class EstimateResource extends Resource
{
    protected static ?string $model = Estimate::class;

    protected static ?string $navigationLabel = 'Estimates';

    protected static ?string $modelLabel = 'Estimate';

    protected static ?string $pluralModelLabel = 'Estimates';

    protected static string|UnitEnum|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'number';

    public static function form(Schema $schema): Schema
    {
        return EstimateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EstimatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEstimates::route('/'),
            'create' => CreateEstimate::route('/create'),
            'edit' => EditEstimate::route('/{record}/edit'),
            'costing' => ViewEstimateCosting::route('/{record}/costing'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
