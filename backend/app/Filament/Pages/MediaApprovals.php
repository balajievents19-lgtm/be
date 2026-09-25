<?php

namespace App\Filament\Pages;

use App\Enums\ContentModerationStatus;
use App\Models\ExternalMedia;
use App\Models\GalleryItem;
use App\Support\Rbac\AdminUserSecurity;
use App\Support\Staff\ContentModeration;
use BackedEnum;
use Filament\Actions\Action;
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
                TextColumn::make('category.name')->label('Category'),
                TextColumn::make('services.name')->label('Service')->badge()->limitList(2),
                TextColumn::make('creator.name')->label('Uploaded by'),
                TextColumn::make('created_at')->dateTime()->label('Date'),
                TextColumn::make('media_type')->label('Media type')->badge(),
                TextColumn::make('moderation_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => ContentModerationStatus::tryFrom((string) $state)?->label() ?? (string) $state),
                TextColumn::make('brand_review_required')
                    ->label('Moderation')
                    ->formatStateUsing(fn ($state): string => $state ? 'Brand review required' : 'Pending review'),
            ])
            ->recordActions([
                Action::make('view')
                    ->url(fn (GalleryItem $record): string => \App\Filament\Resources\GalleryItems\GalleryItemResource::getUrl('edit', ['record' => $record])),
                Action::make('approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (GalleryItem $record): void {
                        ContentModeration::approve(Auth::user(), $record);
                        Notification::make()->title('Approved')->success()->send();
                    }),
                Action::make('reject')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (GalleryItem $record): void {
                        ContentModeration::reject(Auth::user(), $record);
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
