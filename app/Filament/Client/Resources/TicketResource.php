<?php

namespace App\Filament\Client\Resources;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Filament\Client\Resources\TicketResource\Pages;
use App\Filament\Forms\Components\TicketComments;
use App\Filament\Resources\TicketResource\RelationManagers\FieldsRelationManager;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $recordTitleAttribute = 'subject';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static bool $isGloballySearchable = false;

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canView($record): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('requester_id', Auth::guard('client')->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Livewire::make(FieldsRelationManager::class, fn (Ticket $record, Pages\ViewTicket $livewire): array => [
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
                        Forms\Components\Section::make(__('Details'))
                            ->schema([
                                Forms\Components\Placeholder::make('priority')
                                    ->label(__('Priority'))
                                    ->content(fn (Ticket $record): ?string => $record->priority->getLabel()),

                                Forms\Components\Placeholder::make('type')
                                    ->label(__('Type'))
                                    ->content(fn (Ticket $record): ?string => $record->type->getLabel()),

                                Forms\Components\Placeholder::make('status')
                                    ->label(__('Status'))
                                    ->content(fn (Ticket $record): ?string => $record->status->getLabel()),
                                Forms\Components\Placeholder::make('assignee')
                                    ->label(__('Assignee'))
                                    ->content(fn (Ticket $record): ?string => $record->assignee ? $record->assignee->name : '-'),

                                Forms\Components\Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (Ticket $record): ?string => $record->created_at?->diffForHumans()),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label(__('Updated at'))
                                    ->content(fn (Ticket $record): ?string => $record->updated_at?->diffForHumans()),
                            ])
                            ->columns(1)
                            ->hiddenOn(['create']),
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
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(TicketStatus::class)
                    ->searchable()
                    ->preload(),

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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
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
        ];
    }
}
