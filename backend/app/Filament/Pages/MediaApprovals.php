<?php

namespace App\Filament\Pages;

use App\Enums\ContentModerationStatus;
use App\Models\ExternalMedia;
use App\Models\GalleryItem;
use App\Support\Rbac\AdminUserSecurity;
use App\Support\Staff\ContentModeration;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MediaApprovals extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Media Approvals';

    protected static ?string $title = 'Media Approvals';

    protected string $view = 'filament.pages.media-approvals';

    public static function canAccess(): bool
    {
        return AdminUserSecurity::isSuperAdmin(Auth::user());
    }

    public static function getNavigationBadge(): ?string
    {
        $count = GalleryItem::query()->needsReview()->count();

        return $count > 0 ? (string) $count : null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                GalleryItem::query()
                    ->with(['category', 'creator', 'services'])
                    ->needsReview()
                    ->latest()
            )
            ->columns([
                TextColumn::make('title')->searchable()->wrap(),
                TextColumn::make('creator.name')->label('Creator'),
                TextColumn::make('services.name')->label('Service')->badge()->limitList(2),
                TextColumn::make('category.name')->label('Category'),
                TextColumn::make('media_type')->label('Media type')->badge(),
                TextColumn::make('created_at')->dateTime()->label('Created'),
                TextColumn::make('moderation_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => ContentModerationStatus::tryFrom((string) $state)?->label() ?? (string) $state),
                TextColumn::make('brand_review_required')
                    ->label('Brand flag')
                    ->formatStateUsing(fn ($state): string => $state ? 'Needs brand review' : '—'),
            ])
            ->recordActions([
                Action::make('view')
                    ->url(fn (GalleryItem $record): string => \App\Filament\Resources\GalleryItems\GalleryItemResource::getUrl('edit', ['record' => $record])),
                Action::make('edit')
                    ->url(fn (GalleryItem $record): string => \App\Filament\Resources\GalleryItems\GalleryItemResource::getUrl('edit', ['record' => $record])),
                Action::make('approve')
                    ->label('Approve & Publish')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (GalleryItem $record): void {
                        ContentModeration::approve(Auth::user(), $record);
                        Notification::make()->title('Approved and published')->success()->send();
                    }),
                Action::make('reject')
                    ->color('danger')
                    ->form([
                        Textarea::make('notes')
                            ->label('Rejection reason')
                            ->required()
                            ->maxLength(500)
                            ->rows(3),
                    ])
                    ->action(function (GalleryItem $record, array $data): void {
                        ContentModeration::reject(Auth::user(), $record, $data['notes'] ?? null);
                        Notification::make()->title('Rejected')->danger()->send();
                    }),
            ]);
    }

    /**
     * @return list<ExternalMedia>
     */
    public function pendingExternalMedia(): array
    {
        return ExternalMedia::query()
            ->with(['category', 'service', 'creator'])
            ->needsReview()
            ->latest()
            ->limit(50)
            ->get()
            ->all();
    }

    public function approveExternal(int $id): void
    {
        $record = ExternalMedia::query()->findOrFail($id);
        ContentModeration::approve(Auth::user(), $record);
        Notification::make()->title('Approved')->success()->send();
    }

    public function rejectExternal(int $id): void
    {
        $record = ExternalMedia::query()->findOrFail($id);
        ContentModeration::reject(Auth::user(), $record);
        Notification::make()->title('Rejected')->danger()->send();
    }
}
