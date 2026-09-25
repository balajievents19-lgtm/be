<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\GalleryCategory;
use App\Models\Service;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;

final class UserContentAccessFields
{
    /**
     * Loaded from current CMS records so new Services/Categories appear automatically.
     *
     * @return list<Section>
     */
    public static function sections(): array
    {
        $serviceSets = Service::query()->orderBy('sort_order')->orderBy('name')->get()
            ->map(fn (Service $service) => Fieldset::make($service->name)
                ->schema(self::permissionToggles('service_access', (int) $service->id))
                ->columns(3))
            ->all();

        $categorySets = GalleryCategory::query()->orderBy('sort_order')->orderBy('name')->get()
            ->map(fn (GalleryCategory $category) => Fieldset::make($category->name)
                ->schema(self::permissionToggles('category_access', (int) $category->id))
                ->columns(3))
            ->all();

        return [
            Section::make('Staff Access & Permissions')
                ->description('Assign existing Services and Gallery Categories. New CMS records appear here automatically with no access. Staff may receive View, Create, and Edit Own Content only. Delete, Publish, and Approve stay Super Admin only.')
                ->collapsed()
                ->schema([
                    Section::make('Services')
                        ->description('Tick View to assign a service. Staff can create only in assigned services and can edit only their own content.')
                        ->schema($serviceSets !== [] ? $serviceSets : [
                            Placeholder::make('no_services')->content('No services in the CMS yet.'),
                        ]),
                    Section::make('Gallery Categories')
                        ->description('Tick View to assign a gallery category. Staff can create only in assigned categories and can edit only their own content.')
                        ->schema($categorySets !== [] ? $categorySets : [
                            Placeholder::make('no_categories')->content('No gallery categories in the CMS yet.'),
                        ]),
                ]),
        ];
    }

    /**
     * @return list<Toggle>
     */
    private static function permissionToggles(string $prefix, int $id): array
    {
        $key = fn (string $flag): string => "{$prefix}.{$id}.{$flag}";

        return [
            Toggle::make($key('can_access'))->label('View')->default(false),
            Toggle::make($key('can_create'))->label('Create')->default(false),
            Toggle::make($key('can_edit_own'))->label('Edit Own Content')->default(false),
            Toggle::make($key('can_delete'))
                ->label('Delete')
                ->default(false)
                ->disabled()
                ->dehydrated(false)
                ->helperText('Super Admin only'),
            Toggle::make($key('can_publish'))
                ->label('Publish')
                ->default(false)
                ->disabled()
                ->dehydrated(false)
                ->helperText('Super Admin only'),
            Toggle::make($key('can_approve'))
                ->label('Approve')
                ->default(false)
                ->disabled()
                ->dehydrated(false)
                ->helperText('Super Admin only'),
        ];
    }
}
