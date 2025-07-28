<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketUserStatus;
use App\Enums\Users\UserType;
use App\Filament\Forms\Components\TicketComments;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Actions;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    public function getTitle(): string|Htmlable
    {
        return "Ticket #{$this->getRecord()->ticket_id}";
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
                         Section::make('Status Information')
                            ->schema([
                                Placeholder::make('user_status')
                                    ->label('User Status')
                                    //->inlineLabel()
                                    ->content(function (Ticket $record) {
                                        if (!$record->user_status) {
                                            return new HtmlString('<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">-</span>');
                                        }
                                        $color = $record->user_status->getColor();
                                        $label = $record->user_status->getLabel();
                                        $colorClasses = match($color) {
                                            'warning' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                                            'success' => 'bg-green-50 text-green-800 ring-green-600/20',
                                            'danger' => 'bg-red-50 text-red-800 ring-red-600/20',
                                            default => 'bg-gray-50 text-gray-800 ring-gray-600/20',
                                        };
                                        return new HtmlString("<span class=\"inline-flex items-center rounded-md {$colorClasses} px-2 py-1 text-xs font-medium ring-1 ring-inset\">{$label}</span>");
                                    }),

                                Placeholder::make('cat_supervisor_status')
                                    ->label('Category Supervisory Status')
                                    //->inlineLabel()
                                    ->content(function (Ticket $record) {
                                        if (!$record->cat_supervisor_status) {
                                            return new HtmlString('<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">-</span>');
                                        }
                                        $color = $record->cat_supervisor_status->getColor();
                                        $label = $record->cat_supervisor_status->getLabel();
                                        $colorClasses = match($color) {
                                            'warning' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                                            'success' => 'bg-green-50 text-green-800 ring-green-600/20',
                                            'danger' => 'bg-red-50 text-red-800 ring-red-600/20',
                                            default => 'bg-gray-50 text-gray-800 ring-gray-600/20',
                                        };
                                        return new HtmlString("<span class=\"inline-flex items-center rounded-md {$colorClasses} px-2 py-1 text-xs font-medium ring-1 ring-inset\">{$label}</span>");
                                    }),

                                Placeholder::make('build_supervisor_status')
                                    ->label('Building Supervisory Status')
                                    //->inlineLabel()
                                    ->content(function (Ticket $record) {
                                        if (!$record->build_supervisor_status) {
                                            return new HtmlString('<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">-</span>');
                                        }
                                        $color = $record->build_supervisor_status->getColor();
                                        $label = $record->build_supervisor_status->getLabel();
                                        $colorClasses = match($color) {
                                            'warning' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                                            'success' => 'bg-green-50 text-green-800 ring-green-600/20',
                                            'danger' => 'bg-red-50 text-red-800 ring-red-600/20',
                                            default => 'bg-gray-50 text-gray-800 ring-gray-600/20',
                                        };
                                        return new HtmlString("<span class=\"inline-flex items-center rounded-md {$colorClasses} px-2 py-1 text-xs font-medium ring-1 ring-inset\">{$label}</span>");
                                    }),

                                Placeholder::make('is_escalated')
                                    ->label('Escalated')
                                    //->inlineLabel()
                                    ->content(fn (Ticket $record): string => $record->is_escalated ? 'Yes' : 'No'),
                            ])
                            ->columns(2)
                            ->hidden(fn (Ticket $record): bool => !$record->user_status && !$record->cat_supervisor_status && !$record->build_supervisor_status && !$record->is_escalated),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Requester Information')
                            ->schema([
                                Placeholder::make('requester_name')
                                    ->label('Name')
                                    ->inlineLabel()
                                    ->content(fn (Ticket $record): string => $record->requester?->name ?? '-'),

                                Placeholder::make('requester_email')
                                    ->label('Email')
                                    ->inlineLabel()
                                    ->content(fn (Ticket $record): string => $record->requester?->email ?? '-'),

                                Placeholder::make('requester_unique_id')
                                    ->label('EMP/REG No')
                                    ->inlineLabel()
                                    ->content(fn (Ticket $record): string => $record->requester?->unique_id ?? '-'),
                            ]),

                        Section::make('Assignment & Details')
                            ->schema([
                                Placeholder::make('status')
                                    ->label('Status')
                                    ->inlineLabel()
                                    ->content(fn (Ticket $record): string => $record->status->getLabel()),

                                Placeholder::make('priority')
                                    ->label('Priority')
                                    ->inlineLabel()
                                    ->content(fn (Ticket $record): string => $record->priority->getLabel()),

                                Placeholder::make('type')
                                    ->label('Type')
                                    ->inlineLabel()
                                    ->content(fn (Ticket $record): string => $record->type->getLabel()),

                                Placeholder::make('assignee')
                                    ->label('Assignee')
                                    ->inlineLabel()
                                    ->content(fn (Ticket $record): string => $record->assignee?->name ?? 'Unassigned'),

                                // Placeholder::make('group')
                                //     ->label('Group')
                                //     ->inlineLabel()
                                //     ->content(fn (Ticket $record): string => $record->group?->name ?? '-'),
                            ]),

                        Section::make('Dates & Timeline')
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label('Created')
                                    ->inlineLabel()
                                    ->content(fn (Ticket $record): string => $record->created_at?->format('M j, Y g:i A') ?? '-'),

                                Placeholder::make('updated_at')
                                    ->label('Last Updated')
                                    ->inlineLabel()
                                    ->content(fn (Ticket $record): string => $record->updated_at?->format('M j, Y g:i A') ?? '-'),

                              
                            ]),

                       
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    protected function getHeaderActions(): array
    {
        $currentUser = auth()->user();
        $record = $this->getRecord();
        
        $actions = [
            Actions\Action::make('changeStatus')
                ->label('Change Status')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    Select::make('status')
                        ->label('New Status')
                        ->options(TicketStatus::class)
                        ->default(fn (Ticket $record) => $record->status)
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data, Ticket $record): void {
                    $record->update(['status' => $data['status']]);
                    
                    $this->refreshFormData([
                        'status'
                    ]);
                })
                ->modalHeading('Change Ticket Status')
                ->modalDescription('Select a new status for this ticket.')
                ->modalSubmitActionLabel('Update Status'),
        ];

        // Add supervisor close ticket action
        if ($currentUser && $currentUser->user_type && 
            in_array($currentUser->user_type, [UserType::CATEGORY_SUPERVISOR, UserType::BUILDING_SUPERVISOR])) {
            
            $isCategorySupervisor = $currentUser->user_type === UserType::CATEGORY_SUPERVISOR;
            $isBuildingSupervisor = $currentUser->user_type === UserType::BUILDING_SUPERVISOR;
            
            // Check if action can be performed
            $canPerformAction = $record->status === TicketStatus::CLOSED && 
                (($isCategorySupervisor && $record->cat_supervisor_status !== TicketUserStatus::CLOSE) ||
                 ($isBuildingSupervisor && $record->build_supervisor_status !== TicketUserStatus::CLOSE));
            
            $actions[] = Actions\Action::make('supervisorClose')
                ->label($isCategorySupervisor ? 'Close as Category Supervisor' : 'Close as Building Supervisor')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading($isCategorySupervisor ? 'Category Supervisor Closure' : 'Building Supervisor Closure')
                ->modalDescription('Are you sure you want to mark this ticket as closed from your supervisory perspective? This action confirms the ticket resolution.')
                ->modalSubmitActionLabel('Confirm Closure')
                ->action(function (Ticket $record) use ($isCategorySupervisor): void {
                    if ($isCategorySupervisor) {
                        $record->update(['cat_supervisor_status' => TicketUserStatus::CLOSE]);
                    } else {
                        $record->update(['build_supervisor_status' => TicketUserStatus::CLOSE]);
                    }
                    
                    $this->refreshFormData([
                        'cat_supervisor_status',
                        'build_supervisor_status'
                    ]);
                })
                ->disabled(!$canPerformAction)
                ->tooltip(function () use ($record, $canPerformAction, $isCategorySupervisor): ?string {
                    if (!$canPerformAction) {
                        if ($record->status !== TicketStatus::CLOSED) {
                            return 'Ticket must be closed first before supervisor action';
                        }
                        if ($isCategorySupervisor && $record->cat_supervisor_status === TicketUserStatus::CLOSE) {
                            return 'Category supervisor closure already completed';
                        }
                        if (!$isCategorySupervisor && $record->build_supervisor_status === TicketUserStatus::CLOSE) {
                            return 'Building supervisor closure already completed';
                        }
                    }
                    return null;
                });
        }

        $actions[] = Actions\EditAction::make()
            ->label('Edit Ticket')
            ->visible(fn () => auth()->user()?->isAdmin() ?? false);

        return $actions;
    }
}