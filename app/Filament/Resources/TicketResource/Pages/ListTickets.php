<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Actions\Tickets\UpdateTicketPriority;
use App\Actions\Tickets\UpdateTicketStatus;
use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Filament\Exports\TicketExporter;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Closure;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('escalate')
                ->label(__('Escalate ticket'))
                ->icon('heroicon-o-bars-arrow-up')
                ->color('gray')
                ->modalWidth('lg')
                ->modalHeading(__('Escalate ticket'))
                ->modalDescription(__('By escalating a ticket, it’s marked as urgent and handled with top priority. This can only be done once and isn’t available for all tickets.'))
                ->modalSubmitActionLabel(__('Escalate'))
                ->form([
                    TextInput::make('ticket_id')
                        ->label(__('Ticket ID'))
                        ->rules([
                            fn (): Closure => function (string $attribute, $value, Closure $fail) {
                                $ticket = Ticket::where('ticket_id', $value)->first();

                                if (! $ticket) {
                                    return $fail(__('Ticket not found.'));
                                }

                                if ($ticket->is_escalated) {
                                    return $fail(__('Ticket has already been escalated.'));
                                }
                            },
                        ])
                        ->required(),
                    Textarea::make('reason')
                        ->label(__('Reason'))
                        ->placeholder(__('Enter the reason for escalating the ticket'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    $ticket = Ticket::where('ticket_id', $data['ticket_id'])->first();

                    $updateTicketStatus = app(UpdateTicketStatus::class);
                    $updateTicketStatus->handle(
                        $ticket,
                        TicketStatus::OPEN,
                        ['reason' => $data['reason']],
                    );

                    $updateTicketPriority = app(UpdateTicketPriority::class);
                    $updateTicketPriority->handle(
                        $ticket,
                        TicketPriority::URGENT,
                    );

                    $ticket->update([
                        'is_escalated' => true,
                    ]);

                    Notification::make()
                        ->title(__('Success'))
                        ->body(__('The ticket has been successfully escalated.'))
                        ->success()
                        ->send();
                }),
            Actions\ExportAction::make()
                ->exporter(TicketExporter::class)
                ->icon('heroicon-o-arrow-down-tray'),
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        $ticketStatuses = TicketStatus::cases();
        $currentUser = auth()->user();

        // Helper function to get filtered query for current user
        $getFilteredQuery = function () use ($currentUser) {
            $query = Ticket::query();

            if ($currentUser) {
                if ($currentUser->isBuildingSupervisor()) {
                    // Building supervisors see only tickets from buildings they supervise
                    $supervisedBuildingIds = $currentUser->supervisedBuildings()->pluck('buildings.id');
                    $query->whereIn('building_id', $supervisedBuildingIds);
                } elseif ($currentUser->isAgent()) {
                    // Agents see only tickets assigned to them
                    $query->where('assignee_id', $currentUser->id);
                }
                // Note: Category supervisors and admin users see all tickets
            }

            return $query;
        };

        $tabs = [
            Tab::make()
                ->label(__('All'))
                ->badge($getFilteredQuery()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query),
        ];

        foreach ($ticketStatuses as $ticketStatus) {
            $tabs[] = Tab::make()
                ->label($ticketStatus->getLabel())
                ->badge($getFilteredQuery()->where('status', $ticketStatus->value)->count())
                ->badgeColor($ticketStatus->getColor())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $ticketStatus->value));
        }

        return $tabs;
    }
}
