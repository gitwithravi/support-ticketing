<?php

namespace App\Filament\Resources;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Enums\Tickets\TicketUserStatus;
use App\Filament\Forms\Components\TicketComments;
use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\Pages\EditTicket;
use App\Filament\Resources\TicketResource\RelationManagers\FieldsRelationManager;
use App\Models\Building;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\View;
use Illuminate\Support\HtmlString;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                View::make('filament.forms.components.ticket-duplicate-message')
                                    ->hidden(fn (?Ticket $record) => ! $record || ! $record->duplicate_of_ticket_id),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('priority')
                                            ->label(__('Priority'))
                                            ->options(TicketPriority::class)
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->default(TicketPriority::NORMAL),
                                        Forms\Components\Select::make('type')
                                            ->label(__('Type'))
                                            ->options(TicketType::class)
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Forms\Components\Select::make('status')
                                            ->label(__('Status'))
                                            ->options(TicketStatus::class)
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->hiddenOn(['create'])
                                            ->disabled(fn ($record) => $record->status === TicketStatus::CLOSED),
                                    ])->columns(3),
                                Forms\Components\TextInput::make('subject')
                                    ->label(__('Subject'))
                                    ->placeholder(__('Enter the subject of the ticket'))
                                    ->disabledOn(['edit'])
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('ticket_description')
                                    ->label(__('Description'))
                                    ->placeholder(__('Describe the ticket in detail'))
                                    ->required()
                                    ->rows(4)
                                    ->columnSpanFull(),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('building_id')
                                            ->label(__('Building'))
                                            ->options(Building::pluck('name', 'id'))
                                            ->searchable()
                                            ->preload(),
                                        Forms\Components\TextInput::make('room_no')
                                            ->label(__('Room Number'))
                                            ->maxLength(255),
                                        Forms\Components\Select::make('category_id')
                                            ->label(__('Category'))
                                            ->options(Category::pluck('name', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(fn ($state, callable $set) => $set('sub_category_id', null)),
                                        Forms\Components\Select::make('sub_category_id')
                                            ->label(__('Sub Category'))
                                            ->options(function (Forms\Get $get) {
                                                $categoryId = $get('category_id');
                                                if (!$categoryId) {
                                                    return [];
                                                }
                                                return SubCategory::where('category_id', $categoryId)->pluck('name', 'id');
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->disabled(fn (Forms\Get $get) => !$get('category_id')),
                                    ])->columns(2),
                            ]),
                        Livewire::make(FieldsRelationManager::class, fn (Ticket $record, EditTicket $livewire): array => [
                            'ownerRecord' => $record,
                            'pageClass' => $livewire::class,
                        ])->hidden(function (?Ticket $record) {
                            // If no record exists, it's the create page
                            if (! $record) {
                                return true;
                            }

                            return $record->fields->isEmpty();
                        }),
                        TicketComments::make()
                            ->hiddenOn(['create']),
                    ])->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\View::make('filament.infolists.components.requester')
                            ->hidden(fn (?Ticket $record) => ! $record || ! $record->requester()->exists()),
                        Forms\Components\Section::make(__('Associations'))
                            ->schema([
                                Forms\Components\Select::make('requester_id')
                                    ->label(__('Requester'))
                                    ->relationship(name: 'requester', titleAttribute: 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('The client who requested the ticket.')),
                                Forms\Components\Select::make('assignee_id')
                                    ->label(__('Assignee'))
                                    ->relationship(name: 'assignee', titleAttribute: 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('The agent assigned to the ticket.')),
                                Forms\Components\Select::make('group_id')
                                    ->label(__('Group'))
                                    ->relationship(name: 'group', titleAttribute: 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('The group assigned to the ticket.')),
                            ]),
                        Forms\Components\Section::make(__('Metadata'))
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (Ticket $record): ?string => $record->created_at?->diffForHumans()),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label(__('Updated at'))
                                    ->content(fn (Ticket $record): ?string => $record->updated_at?->diffForHumans()),

                                Forms\Components\Placeholder::make('user_status')
                                    ->label(__('User Status'))
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

                                Forms\Components\Placeholder::make('cat_supervisor_status')
                                    ->label(__('Category Supervisory Status'))
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

                                Forms\Components\Placeholder::make('build_supervisor_status')
                                    ->label(__('Building Supervisory Status'))
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
                            ])->hiddenOn(['create']),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_id')
                    ->label(__('Ticket ID'))
                    ->prefix('#')
                    ->copyable()
                    ->copyMessage(__('Ticket ID copied to clipboard'))
                    ->copyMessageDuration(1500)
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('requester.name')
                    ->label(__('Requester'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('building.name')
                    ->label(__('Building'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('room_no')
                    ->label(__('Room'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subCategory.name')
                    ->label(__('Sub Category'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('assignee.name')
                    ->label(__('Assignee'))
                    ->searchable()
                    ->sortable()
                    ->placeholder('Unassigned')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('group.name')
                    ->label(__('Group'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Last Updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('is_assigned_to_me')
                    ->label(__('Assigned to me'))
                    ->query(fn (Builder $query): Builder => $query->where('assignee_id', auth()->id())),
                SelectFilter::make('requester')
                    ->label(__('Requester'))
                    ->relationship('requester', 'name')
                    ->searchable(),
                SelectFilter::make('priority')
                    ->label(__('Priority'))
                    ->options(TicketPriority::class)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options(TicketType::class)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(TicketStatus::class)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('building_id')
                    ->label(__('Building'))
                    ->relationship('building', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category_id')
                    ->label(__('Category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('assignee')
                    ->label(__('Assignee'))
                    ->relationship('assignee', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('group')
                    ->label(__('Group'))
                    ->relationship('group', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('ticket_id', 'DESC');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
