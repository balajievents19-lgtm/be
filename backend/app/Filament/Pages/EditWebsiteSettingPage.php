<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesAdminModule;
use App\Filament\Support\WebsitePublishFields;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Livewire\Attributes\Locked;
use Throwable;

/**
 * @property-read Schema $form
 */
abstract class EditWebsiteSettingPage extends Page
{
    use AuthorizesAdminModule;

    protected static bool $isDiscovered = false;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    #[Locked]
    public ?Setting $record = null;

    abstract protected function formFields(): array;

    protected function previewPath(): string
    {
        return '/';
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->record = Setting::singleton();
        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema($this->formFields()),
                Section::make('History')
                    ->columns(2)
                    ->schema(WebsitePublishFields::audit())
                    ->collapsed(),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function save(): void
    {
        abort_unless(static::canUpdateModule(), 403);

        try {
            $data = $this->form->getState();
            $this->record->fill($data);
            $this->record->save();
        } catch (Halt $exception) {
            return;
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Could not save')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            throw $exception;
        }

        Notification::make()
            ->title('Saved')
            ->success()
            ->send();
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('previewWebsite')
                ->label('Preview Website')
                ->url(WebsitePublishFields::previewUrl($this->previewPath()), shouldOpenInNewTab: true)
                ->color('gray'),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label('Save changes')
                        ->submit('save')
                        ->keyBindings(['mod+s'])
                        ->visible(fn (): bool => static::canUpdateModule()),
                ])->key('form-actions'),
            ]);
    }
}
