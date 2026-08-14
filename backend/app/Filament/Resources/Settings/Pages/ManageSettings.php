<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class ManageSettings extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected static ?string $title = 'Website Settings';

    public function getBreadcrumb(): string
    {
        return 'Settings';
    }

    public function mount(int|string $record = '1'): void
    {
        $this->record = Setting::singleton();

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    protected function resolveRecord(int|string $key): Model
    {
        return Setting::singleton();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('index');
    }
}
