<?php

namespace App\Filament\Concerns;

use App\Support\Staff\StaffPanelAccess;
use Illuminate\Support\Facades\Auth;

trait DeniesRestrictedStaff
{
    public static function canAccess(): bool
    {
        if (StaffPanelAccess::isRestrictedStaff(Auth::user())) {
            return false;
        }

        return parent::canAccess();
    }
}
