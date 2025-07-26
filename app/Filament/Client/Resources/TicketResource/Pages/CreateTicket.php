<?php

namespace App\Filament\Client\Resources\TicketResource\Pages;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Filament\Client\Resources\TicketResource;
use App\Models\Building;
use App\Models\Category;
use App\Models\SubCategory;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Ticket Information')
                    ->schema([
                        Select::make('building_id')
                            ->label('Building')
                            ->options(Building::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('room_no')
                            ->label('Room Number')
                            ->required()
                            ->maxLength(255),

                        Select::make('category_id')
                            ->label('Category')
                            ->options(Category::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('sub_category_id', null)),

                        Select::make('sub_category_id')
                            ->label('Sub Category')
                            ->options(function (Get $get) {
                                $categoryId = $get('category_id');
                                if (!$categoryId) {
                                    return [];
                                }
                                return SubCategory::where('category_id', $categoryId)->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn (Get $get) => !$get('category_id')),

                        TextInput::make('subject')
                            ->label('Subject')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('ticket_description')
                            ->label('Description')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        Select::make('priority')
                            ->label('Priority')
                            ->options(TicketPriority::class)
                            ->default(TicketPriority::NORMAL)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requester_id'] = Auth::guard('client')->id();
        $data['status'] = TicketStatus::OPEN;
        $data['type'] = TicketType::TASK;
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}