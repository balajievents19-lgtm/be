<?php

namespace App\Filament\Pages\Home;

use App\Filament\Clusters\HomeCluster;
use App\Filament\Concerns\AuthorizesAdminModule;
use App\Filament\Support\WebsitePublishFields;
use App\Models\HomepageSection;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Throwable;

/**
 * @property-read Schema $form
 */
class ManageHomepageSections extends Page
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'home';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = HomeCluster::class;

    protected static ?string $navigationLabel = 'Homepage Sections';

    protected static ?string $title = 'Homepage Sections';

    protected static ?int $navigationSort = 0;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $slug = 'homepage-sections';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        HomepageSection::ensureDefaults();

        $this->form->fill(
            HomepageSection::query()
                ->ordered()
                ->get()
                ->mapWithKeys(fn (HomepageSection $section): array => [
                    $section->section_key => $section->is_active,
                ])
                ->all()
        );
    }

    public function form(Schema $schema): Schema
    {
        HomepageSection::ensureDefaults();

        $toggles = HomepageSection::query()
            ->ordered()
            ->get()
            ->map(fn (HomepageSection $section): Toggle => Toggle::make($section->section_key)
                ->label($section->section_name)
                ->inline(false))
            ->all();

        return $schema
            ->components([
                Section::make('Homepage Sections')
                    ->description('Turn a section off to hide it on the homepage. Content, dedicated pages, and admin modules stay in place.')
                    ->schema($toggles),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        abort_unless(static::canUpdateModule(), 403);

        try {
            $data = $this->form->getState();

            foreach (HomepageSection::query()->ordered()->get() as $section) {
                if (! array_key_exists($section->section_key, $data)) {
                    continue;
                }

                $section->is_active = (bool) $data[$section->section_key];
                $section->save();
            }
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
                ->url(WebsitePublishFields::previewUrl('/'), shouldOpenInNewTab: true)
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
