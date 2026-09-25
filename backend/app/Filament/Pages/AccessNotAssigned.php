<?php

namespace App\Filament\Pages;

use App\Support\Staff\StaffContentAccess;
use App\Support\Staff\StaffPanelAccess;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class AccessNotAssigned extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static ?string $navigationLabel = 'Access Not Assigned';

    protected static ?string $title = 'Access Not Assigned';

    protected static ?string $slug = 'staff-access';

    protected static ?int $navigationSort = -8;

    protected string $view = 'filament.pages.access-not-assigned';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return StaffPanelAccess::isRestrictedStaff($user)
            && ! StaffContentAccess::hasAssignedContentScope($user);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
