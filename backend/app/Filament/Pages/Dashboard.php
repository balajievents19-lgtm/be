<?php

namespace App\Filament\Pages;

use App\Services\Maintenance\WebsiteCleanupService;
use App\Support\Admin\LeadDashboardMetrics;
use App\Support\Rbac\AdminModules;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Throwable;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Home';

    protected static ?int $navigationSort = -2;

    public function getTitle(): string|Htmlable
    {
        return 'Home';
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('cleanAndOptimize')
                ->label('Clean & Optimize Website')
                ->icon(Heroicon::OutlinedSparkles)
                ->color('gray')
                ->visible(fn (): bool => Auth::user()?->can(AdminModules::permission('gallery', 'update')) === true)
                ->requiresConfirmation()
                ->modalHeading('Clean & Optimize Website')
                ->modalDescription('This operation safely removes only unnecessary temporary/orphan files and clears safe application caches. Referenced media, gallery originals, logos, system assets, and database records are not deleted. Files that cannot be verified as unused are skipped.')
                ->modalSubmitActionLabel('Clean & Optimize')
                ->action(function (): void {
                    try {
                        $result = app(WebsiteCleanupService::class)->run();
                        $notification = Notification::make()
                            ->title($result->notificationTitle())
                            ->body($result->notificationBody());

                        if ($result->failed) {
                            $notification->danger()->persistent()->send();

                            return;
                        }

                        $notification->success()->send();
                    } catch (Throwable $e) {
                        report($e);
                        Notification::make()
                            ->title('Clean & Optimize failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('period')
                            ->label('Period')
                            ->options(LeadDashboardMetrics::periodOptions())
                            ->default(LeadDashboardMetrics::PERIOD_30D)
                            ->native(false),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
