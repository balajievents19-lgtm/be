<?php

namespace App\Filament\Resources\ContactInquiries;

use App\Enums\ContactInquiryStatus;
use App\Filament\Resources\ContactInquiries\Pages\CreateContactInquiry;
use App\Filament\Resources\ContactInquiries\Pages\EditContactInquiry;
use App\Filament\Resources\ContactInquiries\Pages\ListContactInquiries;
use App\Filament\Resources\ContactInquiries\Schemas\ContactInquiryForm;
use App\Filament\Resources\ContactInquiries\Schemas\ContactInquiryInfolist;
use App\Filament\Resources\ContactInquiries\Tables\ContactInquiriesTable;
use App\Models\ContactInquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ContactInquiryResource extends Resource
{
    protected static ?string $model = ContactInquiry::class;

    protected static ?string $navigationLabel = 'Leads';

    protected static ?string $modelLabel = 'Lead';

    protected static ?string $pluralModelLabel = 'Leads';

    protected static string|UnitEnum|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        $count = ContactInquiry::query()
            ->where('status', ContactInquiryStatus::New)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return ContactInquiryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContactInquiryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactInquiriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactInquiries::route('/'),
            'create' => CreateContactInquiry::route('/create'),
            'edit' => EditContactInquiry::route('/{record}/edit'),
        ];
    }
}
