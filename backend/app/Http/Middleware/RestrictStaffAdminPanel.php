<?php

namespace App\Http\Middleware;

use App\Filament\Resources\StaffPosts\StaffPostResource;
use App\Support\Staff\StaffPanelAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Staff Member / specialist accounts may only use My Posts + Create Post in Admin.
 */
class RestrictStaffAdminPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! StaffPanelAccess::isRestrictedStaff($user)) {
            return $next($request);
        }

        $routeName = (string) $request->route()?->getName();

        if ($request->is('admin/staff-posts') || $request->is('admin/staff-posts/*') || $request->is('livewire/*')) {
            return $next($request);
        }

        if ($routeName !== '' && (
            str_starts_with($routeName, 'filament.admin.resources.staff-posts.')
            || str_starts_with($routeName, 'filament.admin.auth.')
            || str_starts_with($routeName, 'livewire.')
        )) {
            return $next($request);
        }

        if ($routeName === 'filament.admin.pages.dashboard' || $request->is('admin') || $request->is('admin/')) {
            return redirect()->to(StaffPostResource::getUrl());
        }

        abort(403);
    }
}
