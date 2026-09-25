<?php

namespace App\Filament\Resources\StaffPosts;

use App\Filament\Resources\GalleryItems\Schemas\GalleryItemForm;
use App\Filament\Resources\GalleryItems\Tables\GalleryItemsTable;
use App\Filament\Resources\StaffPosts\Pages\CreateStaffPost;
use App\Filament\Resources\StaffPosts\Pages\EditStaffPost;
use App\Filament\Resources\StaffPosts\Pages\ListStaffPosts;
use App\Models\GalleryItem;
use App\Support\Staff\StaffContentAccess;
use App\Support\Staff\StaffEloquentScope;
use App\Support\Staff\StaffPanelAccess;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StaffPostResource extends Resource
{
    protected static ?string $model = GalleryItem::class;

    protected static ?string $slug = 'staff-posts';

    protected static ?string $navigationLabel = 'My Posts';

    protected static ?string $modelLabel = 'Post';

    protected static ?string $pluralModelLabel = 'My Posts';

    protected static ?int $navigationSort = -10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'title';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null
            && StaffPanelAccess::isRestrictedStaff($user)
            && $user->can('gallery.view')
            && StaffContentAccess::hasAssignedContentScope($user);
    }

    public static function form(Schema $schema): Schema
    {
        return GalleryItemForm::configure($schema, staffMode: true);
    }

    public static function table(Table $table): Table
    {
        return GalleryItemsTable::configure($table, staffMode: true);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaffPosts::route('/'),
            'create' => CreateStaffPost::route('/create'),
            'edit' => EditStaffPost::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        return $user ? StaffEloquentScope::galleryItems($query, $user) : $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    /**
     * @return array<NavigationItem>
     */
    public static function getNavigationItems(): array
    {
        if (! static::canAccess()) {
            return [];
        }

        return [
            NavigationItem::make('My Posts')
                ->group(null)
                ->icon(Heroicon::OutlinedDocumentText)
                ->sort(-10)
                ->url(static::getUrl())
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.staff-posts.index')
                    || request()->routeIs('filament.admin.resources.staff-posts.edit')),
            NavigationItem::make('Create Post')
                ->group(null)
                ->icon(Heroicon::OutlinedPlus)
                ->sort(-9)
                ->url(static::getUrl('create'))
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.staff-posts.create')),
        ];
    }
}
