<?php

namespace App\Filament\Resources\ContactInquiries\Tables;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Models\ContactInquiry;
use App\Support\LeadAssignees;
use App\Support\LeadContactLinks;
use App\Support\LeadsCsvExporter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ContactInquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.email')
                    ->label('Customer')
                    ->searchable()
                    ->placeholder('Guest')
                    ->toggleable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(22)
                    ->tooltip(fn (?string $state): ?string => filled($state) && strlen($state) > 22 ? $state : null),
                TextColumn::make('mobile')
                    ->searchable()
                    ->sortable()
                    ->wrap(false),
                TextColumn::make('service_interested')
                    ->label('Service')
                    ->searchable()
                    ->limit(20)
                    ->placeholder('—')
                    ->tooltip(fn (?string $state): ?string => filled($state) && strlen($state) > 20 ? $state : null),
                TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event_date')
                    ->label('Event Date')
                    ->date()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ContactInquiryStatus|string|null $state): string => $state instanceof ContactInquiryStatus
                        ? $state->label()
                        : (ContactInquiryStatus::tryFrom((string) $state)?->label() ?? (string) $state))
                    ->color(fn (ContactInquiryStatus|string|null $state): string => $state instanceof ContactInquiryStatus
                        ? $state->color()
                        : (ContactInquiryStatus::tryFrom((string) $state)?->color() ?? 'gray')),
                TextColumn::make('priority')
                    ->badge()
                    ->formatStateUsing(fn (ContactInquiryPriority|string|null $state): string => $state instanceof ContactInquiryPriority
                        ? $state->label()
                        : (ContactInquiryPriority::tryFrom((string) $state)?->label() ?? (string) $state))
                    ->color(fn (ContactInquiryPriority|string|null $state): string => $state instanceof ContactInquiryPriority
                        ? $state->color()
                        : (ContactInquiryPriority::tryFrom((string) $state)?->color() ?? 'gray')),
                TextColumn::make('follow_up_at')
                    ->label('Follow-up')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->tooltip(fn (ContactInquiry $record): ?string => $record->follow_up_at?->format('d M Y H:i')),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->tooltip(fn ($record): ?string => $record->created_at?->format('d M Y H:i')),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(28)
                    ->tooltip(fn (?string $state): ?string => filled($state) && strlen($state) > 28 ? $state : null),
                TextColumn::make('subject')
                    ->searchable()
                    ->limit(28)
                    ->tooltip(fn (?string $state): ?string => filled($state) && strlen($state) > 28 ? $state : null)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('event_location')
                    ->label('Event Location')
                    ->searchable()
                    ->limit(24)
                    ->tooltip(fn (?string $state): ?string => filled($state) && strlen($state) > 24 ? $state : null)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('assignee.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(ContactInquiryStatus::options()),
                SelectFilter::make('priority')
                    ->options(ContactInquiryPriority::options()),
                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'contact_page' => 'contact_page',
                        'slider' => 'slider',
                        'service_inquiry' => 'service_inquiry',
                        'package_inquiry' => 'package_inquiry',
                    ]),
                SelectFilter::make('assigned_to')
                    ->label('Assigned To')
                    ->relationship(
                        'assignee',
                        'name',
                        fn (Builder $query): Builder => LeadAssignees::constrain($query),
                    )
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('follow_up_at')
                    ->label('Follow-up')
                    ->placeholder('All')
                    ->trueLabel('Follow-up scheduled')
                    ->falseLabel('No follow-up scheduled')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('follow_up_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('follow_up_at'),
                    ),
                Filter::make('follow_up_range')
                    ->label('Follow-up Range')
                    ->schema([
                        DatePicker::make('follow_up_from')
                            ->label('From'),
                        DatePicker::make('follow_up_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['follow_up_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('follow_up_at', '>=', $date),
                            )
                            ->when(
                                $data['follow_up_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('follow_up_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View'),
                EditAction::make(),
                Action::make('call')
                    ->label('Call')
                    ->icon(Heroicon::OutlinedPhone)
                    ->color('gray')
                    ->url(fn (ContactInquiry $record): ?string => LeadContactLinks::callUrl($record->mobile))
                    ->visible(fn (ContactInquiry $record): bool => LeadContactLinks::callUrl($record->mobile) !== null),
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                    ->color('success')
                    ->url(fn (ContactInquiry $record): ?string => LeadContactLinks::whatsappUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (ContactInquiry $record): bool => LeadContactLinks::whatsappUrl($record) !== null),
            ])
            ->headerActions([
                Action::make('exportLeads')
                    ->label('Export')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->visible(fn (): bool => Auth::user()?->can('leads.view') ?? false)
                    ->action(function (HasTable $livewire) {
                        $records = $livewire->getFilteredTableQuery()
                            ?->with('assignee')
                            ->reorder()
                            ->orderByDesc('created_at')
                            ->get() ?? Collection::make();

                        return LeadsCsvExporter::download($records);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('exportSelected')
                        ->label('Export Selected')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->color('gray')
                        ->authorizeIndividualRecords('view')
                        ->visible(fn (): bool => Auth::user()?->can('leads.view') ?? false)
                        ->action(function (Collection $records) {
                            $records->loadMissing('assignee');

                            return LeadsCsvExporter::download(
                                $records,
                                'leads-selected-'.now()->format('Y-m-d-His').'.csv',
                            );
                        }),
                    BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->schema([
                            Select::make('status')
                                ->label('Status')
                                ->options(ContactInquiryStatus::options())
                                ->required()
                                ->native(false),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Update Status')
                        ->modalDescription('The selected Leads will be updated to the chosen status.')
                        ->deselectRecordsAfterCompletion()
                        ->authorizeIndividualRecords('update')
                        ->visible(fn (): bool => Auth::user()?->can('leads.update') ?? false)
                        ->action(function (Collection $records, array $data): void {
                            $status = ContactInquiryStatus::from($data['status']);

                            $records->each(function (ContactInquiry $record) use ($status): void {
                                $record->update(['status' => $status]);
                            });

                            Notification::make()
                                ->title('Status updated')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('updatePriority')
                        ->label('Update Priority')
                        ->icon(Heroicon::OutlinedFlag)
                        ->schema([
                            Select::make('priority')
                                ->label('Priority')
                                ->options(ContactInquiryPriority::options())
                                ->required()
                                ->native(false),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Update Priority')
                        ->modalDescription('The selected Leads will be updated to the chosen priority.')
                        ->deselectRecordsAfterCompletion()
                        ->authorizeIndividualRecords('update')
                        ->visible(fn (): bool => Auth::user()?->can('leads.update') ?? false)
                        ->action(function (Collection $records, array $data): void {
                            $priority = ContactInquiryPriority::from($data['priority']);

                            $records->each(function (ContactInquiry $record) use ($priority): void {
                                $record->update(['priority' => $priority]);
                            });

                            Notification::make()
                                ->title('Priority updated')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('assignLeads')
                        ->label('Assign Leads')
                        ->icon(Heroicon::OutlinedUserPlus)
                        ->schema([
                            Select::make('assigned_to')
                                ->label('Assign To')
                                ->options(fn (): array => LeadAssignees::options())
                                ->searchable()
                                ->nullable()
                                ->placeholder('Unassigned')
                                ->native(false),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Assign Leads')
                        ->modalDescription('The selected Leads will be assigned to the chosen user.')
                        ->deselectRecordsAfterCompletion()
                        ->authorizeIndividualRecords('update')
                        ->visible(fn (): bool => Auth::user()?->can('leads.update') ?? false)
                        ->action(function (Collection $records, array $data): void {
                            $assigneeId = filled($data['assigned_to'] ?? null)
                                ? (int) $data['assigned_to']
                                : null;

                            if (! LeadAssignees::isAssignable($assigneeId)) {
                                Notification::make()
                                    ->title('Invalid assignee')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $records->each(function (ContactInquiry $record) use ($assigneeId): void {
                                $record->update(['assigned_to' => $assigneeId]);
                            });

                            Notification::make()
                                ->title($assigneeId === null ? 'Leads unassigned' : 'Leads assigned')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('setFollowUp')
                        ->label('Set Follow-up')
                        ->icon(Heroicon::OutlinedCalendarDays)
                        ->schema([
                            DateTimePicker::make('follow_up_at')
                                ->label('Follow-up At')
                                ->required()
                                ->seconds(false)
                                ->native(false),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Set Follow-up')
                        ->modalDescription('The selected Leads will receive the chosen follow-up date and time.')
                        ->deselectRecordsAfterCompletion()
                        ->authorizeIndividualRecords('update')
                        ->visible(fn (): bool => Auth::user()?->can('leads.update') ?? false)
                        ->action(function (Collection $records, array $data): void {
                            $followUpAt = $data['follow_up_at'];

                            $records->each(function (ContactInquiry $record) use ($followUpAt): void {
                                $record->update(['follow_up_at' => $followUpAt]);
                            });

                            Notification::make()
                                ->title('Follow-up set')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('clearFollowUp')
                        ->label('Clear Follow-up')
                        ->icon(Heroicon::OutlinedCalendar)
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Clear Follow-up')
                        ->modalDescription('The selected Leads will have their follow-up date and time cleared.')
                        ->deselectRecordsAfterCompletion()
                        ->authorizeIndividualRecords('update')
                        ->visible(fn (): bool => Auth::user()?->can('leads.update') ?? false)
                        ->action(function (Collection $records): void {
                            $records->each(function (ContactInquiry $record): void {
                                $record->update(['follow_up_at' => null]);
                            });

                            Notification::make()
                                ->title('Follow-up cleared')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No enquiries yet')
            ->emptyStateDescription('Contact form submissions will appear here.')
            ->emptyStateIcon(Heroicon::OutlinedInboxArrowDown);
    }
}
