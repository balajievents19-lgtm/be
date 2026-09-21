<?php

namespace App\Filament\Resources\ContactInquiries\Schemas;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactInquiryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('customer.email')
                            ->label('Customer account')
                            ->placeholder('Guest / not linked'),
                        TextEntry::make('name')
                            ->placeholder('—'),
                        TextEntry::make('mobile')
                            ->placeholder('—'),
                        TextEntry::make('email')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Event')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('service_interested')
                            ->label('Service')
                            ->placeholder('—'),
                        TextEntry::make('event_date')
                            ->label('Event Date')
                            ->date()
                            ->placeholder('—'),
                        TextEntry::make('event_location')
                            ->label('Event Location')
                            ->placeholder('—'),
                        TextEntry::make('budget')
                            ->placeholder('—'),
                    ]),

                Section::make('Enquiry')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('subject')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('message')
                            ->placeholder('—')
                            ->columnSpanFull()
                            ->prose(),
                        TextEntry::make('source')
                            ->badge()
                            ->placeholder('—'),
                    ]),

                Section::make('Lead')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (ContactInquiryStatus|string|null $state): string => $state instanceof ContactInquiryStatus
                                ? $state->label()
                                : (ContactInquiryStatus::tryFrom((string) $state)?->label() ?? (string) ($state ?? '—')))
                            ->color(fn (ContactInquiryStatus|string|null $state): string => $state instanceof ContactInquiryStatus
                                ? $state->color()
                                : (ContactInquiryStatus::tryFrom((string) $state)?->color() ?? 'gray')),
                        TextEntry::make('priority')
                            ->badge()
                            ->formatStateUsing(fn (ContactInquiryPriority|string|null $state): string => $state instanceof ContactInquiryPriority
                                ? $state->label()
                                : (ContactInquiryPriority::tryFrom((string) $state)?->label() ?? (string) ($state ?? '—')))
                            ->color(fn (ContactInquiryPriority|string|null $state): string => $state instanceof ContactInquiryPriority
                                ? $state->color()
                                : (ContactInquiryPriority::tryFrom((string) $state)?->color() ?? 'gray')),
                        TextEntry::make('assignee.name')
                            ->label('Assigned To')
                            ->placeholder('Unassigned'),
                        TextEntry::make('follow_up_at')
                            ->label('Follow-up At')
                            ->dateTime('d M Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label('Received')
                            ->dateTime('d M Y H:i')
                            ->placeholder('—'),
                    ]),

                Section::make('Admin')
                    ->schema([
                        TextEntry::make('admin_notes')
                            ->label('Admin Notes')
                            ->placeholder('—')
                            ->columnSpanFull()
                            ->prose(),
                    ]),
            ]);
    }
}
