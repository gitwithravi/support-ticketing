<?php

namespace App\Filament\Client\Resources\TicketResource\Pages;

use App\Actions\Tickets\UpdateTicketStatus;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketUserStatus;
use App\Filament\Client\Resources\TicketResource;
use App\Filament\Forms\Components\TicketComments;
use App\Models\Ticket;
use Filament\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecordTitle();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Ticket Information')
                            ->schema([
                                Placeholder::make('ticket_id')
                                    ->label('Ticket ID')
                                    ->content(fn (Ticket $record): string => "#{$record->ticket_id}"),

                                Placeholder::make('subject')
                                    ->label('Subject')
                                    ->content(fn (Ticket $record): string => $record->subject),

                                Textarea::make('ticket_description')
                                    ->label('Description')
                                    ->disabled()
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Section::make('Location & Category')
                            ->schema([
                                Placeholder::make('building')
                                    ->label('Building')
                                    ->content(fn (Ticket $record): string => $record->building?->name ?? '-'),

                                Placeholder::make('room_no')
                                    ->label('Room Number')
                                    ->content(fn (Ticket $record): string => $record->room_no ?? '-'),

                                Placeholder::make('category')
                                    ->label('Category')
                                    ->content(fn (Ticket $record): string => $record->category?->name ?? '-'),

                                Placeholder::make('sub_category')
                                    ->label('Sub Category')
                                    ->content(fn (Ticket $record): string => $record->subCategory?->name ?? '-'),
                            ])
                            ->columns(2),

                        TicketComments::make(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Requester Information')
                            ->schema([
                                Placeholder::make('requester_name')
                                    ->label('Name')
                                    ->content(fn (Ticket $record): string => $record->requester?->name ?? '-'),

                                Placeholder::make('requester_email')
                                    ->label('Email')
                                    ->content(fn (Ticket $record): string => $record->requester?->email ?? '-'),

                                Placeholder::make('requester_unique_id')
                                    ->label('Employee/Registration ID')
                                    ->content(fn (Ticket $record): string => $record->requester?->unique_id ?? '-'),
                            ]),

                        Section::make('Status & Details')
                            ->schema([
                                Placeholder::make('status')
                                    ->label('Status')
                                    ->content(fn (Ticket $record): string => $record->status->getLabel()),

                                Placeholder::make('priority')
                                    ->label('Priority')
                                    ->content(fn (Ticket $record): string => $record->priority->getLabel()),

                                Placeholder::make('type')
                                    ->label('Type')
                                    ->content(fn (Ticket $record): string => $record->type->getLabel()),

                                Placeholder::make('assignee')
                                    ->label('Assignee')
                                    ->content(fn (Ticket $record): string => $record->assignee?->name ?? 'Unassigned'),

                                Placeholder::make('group')
                                    ->label('Group')
                                    ->content(fn (Ticket $record): string => $record->group?->name ?? '-'),
                            ]),

                        Section::make('Dates & Timeline')
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label('Created')
                                    ->content(fn (Ticket $record): string => $record->created_at?->format('M j, Y g:i A') ?? '-'),

                                Placeholder::make('updated_at')
                                    ->label('Last Updated')
                                    ->content(fn (Ticket $record): string => $record->updated_at?->format('M j, Y g:i A') ?? '-'),

                                Placeholder::make('created_relative')
                                    ->label('Created (Relative)')
                                    ->content(fn (Ticket $record): string => $record->created_at?->diffForHumans() ?? '-'),

                                Placeholder::make('updated_relative')
                                    ->label('Updated (Relative)')
                                    ->content(fn (Ticket $record): string => $record->updated_at?->diffForHumans() ?? '-'),
                            ]),

                        Section::make('Additional Information')
                            ->schema([
                                Placeholder::make('is_escalated')
                                    ->label('Escalated')
                                    ->content(fn (Ticket $record): string => $record->is_escalated ? 'Yes' : 'No'),
                            ])
                            ->hidden(fn (Ticket $record): bool => !$record->is_escalated),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('closeTicket')
                ->label(__('Close ticket'))
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalDescription(__('Are you sure you would like to do this? Once the ticket is closed, it cannot be reopened.'))
                ->action(function (Ticket $record) {
                    app(UpdateTicketStatus::class)->handle(
                        $record,
                        TicketStatus::CLOSED,
                        ['reason' => 'The ticket was closed by requester.'],
                    );

                    // Set user_status to CLOSE when closed by client
                    $record->update(['user_status' => TicketUserStatus::CLOSE]);

                    $this->dispatch('ticket-closed');
                })
                ->hidden(fn (Ticket $record): bool => $record->status === TicketStatus::CLOSED),
        ];
    }
}
