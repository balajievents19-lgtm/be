<?php

namespace App\Filament\Resources\StaffPosts\Pages;

use App\Filament\Resources\StaffPosts\StaffPostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStaffPosts extends ListRecords
{
    protected static string $resource = StaffPostResource::class;

    protected static ?string $title = 'My Posts';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Create Post'),
        ];
    }
}
