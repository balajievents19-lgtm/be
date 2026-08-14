<?php

namespace App\Filament\Resources\ContactInquiries\Schemas;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Support\LeadAssignees;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ContactInquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Inquiry')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Customer')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('mobile')
                                            ->required()
                                            ->tel()
                                            ->maxLength(30),
                                        TextInput::make('email')
                                            ->email()
                                            ->maxLength(255),
                                        TextInput::make('company')
                                            ->maxLength(255),
                                    ]),
                            ]),

                        Tab::make('Inquiry')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('subject')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Textarea::make('message')
                                            ->required()
                                            ->rows(5)
                                            ->columnSpanFull(),
                                        TextInput::make('service_interested')
                                            ->label('Service Interested')
                                            ->maxLength(255),
                                        DatePicker::make('event_date')
                                            ->label('Event Date'),
                                        TextInput::make('event_location')
                                            ->label('Event Location')
                                            ->maxLength(255),
                                        TextInput::make('budget')
                                            ->maxLength(100),
                                        TextInput::make('source')
                                            ->maxLength(64)
                                            ->disabled()
                                            ->dehydrated(),
                                    ]),
                            ]),

                        Tab::make('Tracking')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Select::make('status')
                                            ->options(ContactInquiryStatus::options())
                                            ->required()
                                            ->native(false),
                                        Select::make('priority')
                                            ->options(ContactInquiryPriority::options())
                                            ->required()
                                            ->native(false),
                                        Select::make('assigned_to')
                                            ->label('Assigned To')
                                            ->relationship(
                                                'assignee',
                                                'name',
                                                fn (Builder $query): Builder => LeadAssignees::constrain($query),
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->placeholder('Unassigned'),
                                        DateTimePicker::make('follow_up_at')
                                            ->label('Follow-up At')
                                            ->seconds(false)
                                            ->native(false)
                                            ->nullable(),
                                        Textarea::make('admin_notes')
                                            ->label('Admin Notes')
                                            ->rows(5)
                                            ->columnSpanFull(),
                                        TextInput::make('ip_address')
                                            ->label('IP Address')
                                            ->disabled()
                                            ->dehydrated(false),
                                        Textarea::make('user_agent')
                                            ->label('User Agent')
                                            ->rows(2)
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
