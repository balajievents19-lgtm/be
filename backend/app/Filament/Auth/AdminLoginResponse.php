<?php

namespace App\Filament\Auth;

use App\Support\Staff\AdminLanding;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class AdminLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        return redirect()->to(AdminLanding::url($request->user()));
    }
}
