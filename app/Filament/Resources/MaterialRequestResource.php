<?php

namespace App\Filament\Resources;

use App\Enums\MaterialRequests\MaterialRequestStatus;
use App\Filament\Resources\MaterialRequestResource\Pages;
use App\Filament\Resources\MaterialRequestResource\RelationManagers;
use App\Models\MaterialRequest;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaterialRequestResource extends Resource
{
    protected static ?string $model = MaterialRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Material Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'request_reason';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Request Information')
                            ->schema([
                                Forms\Components\Select::make('ticket_id')
                                    ->label('Ticket')
                                    ->relationship(
                                        name: 'ticket',
                                        modifyQueryUsing: function ($query) {
                                            $currentUser = auth()->user();
                                            
                                            // Apply user-based filtering for ticket selection
                                            if ($currentUser && $currentUser->isBuildingSupervisor()) {
                                                $supervisedBuildingIds = $currentUser->supervisedBuildings()->pluck('id');
                                                $query->whereIn('building_id', $supervisedBuildingIds);
                                            } elseif ($currentUser && $currentUser->isCategorySupervisor()) {
                                                $supervisedCategoryIds = $currentUser->supervisedCategories()->pluck('id');
                                                if ($supervisedCategoryIds->isNotEmpty()) {
                                                    $query->whereIn('category_id', $supervisedCategoryIds);
                                                } else {
                                                    // If user doesn't supervise any categories, they shouldn't see any tickets
                                                    $query->whereRaw('1 = 0');
                                                }
                                            }
                                            
                                            return $query->with(['requester', 'building', 'category']);
                                        }
                                    )
                                    ->getOptionLabelFromRecordUsing(fn (Ticket $record) => self::formatTicketLabel($record))
                                    ->searchable(['ticket_id', 'subject', 'room_no'])
                                    ->preload()
                                    ->required(),

                                Forms\Components\Textarea::make('request_reason')
                                    ->label('Request Reason')
                                    ->required()
                                    ->rows(4)
                                    ->maxLength(65535)
                                    ->placeholder('Describe the reason for this material request')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Request Info')
                            ->schema([
                                Forms\Components\Placeholder::make('status')
                                    ->label('Status')
                                    ->inlineLabel()
                                    ->content(fn ($record) => $record?->status?->getLabel() ?? 'Created'),

                                Forms\Components\Placeholder::make('items_count')
                                    ->label('Total Items')
                                    ->inlineLabel()
                                    ->content(fn ($record) => $record?->items?->count() ?? 0),
                                Forms\Components\Placeholder::make('created_by')
                                    ->label('Created By')
                                    ->inlineLabel()
                                    ->content(fn ($record) => $record?->createdBy?->name ?? 'Current User'),

                                Forms\Components\Placeholder::make('processed_by')
                                    ->label('Processed By')
                                    ->inlineLabel()
                                    ->content(fn ($record) => $record?->processedBy?->name ?? 'Not processed yet'),
                            ]),

                        
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $currentUser = auth()->user();
                
                // Apply user-based filtering
                if ($currentUser && $currentUser->isBuildingSupervisor()) {
                    // Building supervisors see only material requests from buildings they supervise
                    $supervisedBuildingIds = $currentUser->supervisedBuildings()->pluck('id');
                    $query->whereHas('ticket', function ($ticketQuery) use ($supervisedBuildingIds) {
                        $ticketQuery->whereIn('building_id', $supervisedBuildingIds);
                    });
                } elseif ($currentUser && $currentUser->isCategorySupervisor()) {
                    // Category supervisors see only material requests from categories they supervise
                    $supervisedCategoryIds = $currentUser->supervisedCategories()->pluck('id');
                    if ($supervisedCategoryIds->isNotEmpty()) {
                        $query->whereHas('ticket', function ($ticketQuery) use ($supervisedCategoryIds) {
                            $ticketQuery->whereIn('category_id', $supervisedCategoryIds);
                        });
                    } else {
                        // If user doesn't supervise any categories, they shouldn't see any material requests
                        $query->whereRaw('1 = 0');
                    }
                }
                
                return $query->with([
                    'ticket.building',
                    'ticket.category', 
                    'ticket.subCategory',
                    'createdBy',
                    'processedBy'
                ]);
            })
            ->columns([
                Tables\Columns\TextColumn::make('ticket.ticket_id')
                    ->label('Ticket ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('request_reason')
                    ->label('Request Reason')
                    ->limit(40)
                    ->searchable()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 40 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('ticket.building.name')
                    ->label('Building')
                    ->searchable()
                    ->sortable()
                    ->placeholder('No Building')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ticket.category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->placeholder('No Category')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ticket.subCategory.name')
                    ->label('Sub Category')
                    ->searchable()
                    ->sortable()
                    ->placeholder('No Sub Category')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ticket.subject')
                    ->label('Ticket Subject')
                    ->limit(30)
                    ->searchable()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('processedBy.name')
                    ->label('Processed By')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Not processed')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ticket.building_id')
                    ->label('Building')
                    ->relationship('ticket.building', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('ticket.category_id')
                    ->label('Category')
                    ->relationship('ticket.category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('status')
                    ->options(MaterialRequestStatus::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('created_by')
                    ->label('Created By')
                    ->relationship('createdBy', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('processed_by')
                    ->label('Processed By')
                    ->relationship('processedBy', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaterialRequests::route('/'),
            'create' => Pages\CreateMaterialRequest::route('/create'),
            'view' => Pages\ViewMaterialRequest::route('/{record}'),
            'edit' => Pages\EditMaterialRequest::route('/{record}/edit'),
        ];
    }

    protected static function formatTicketLabel(Ticket $ticket): string
    {
        $parts = [
            $ticket->ticket_id,
            $ticket->requester?->name ?? 'No Requester',
            $ticket->building?->name ?? 'No Building',
            $ticket->room_no ?? 'No Room',
            $ticket->category?->name ?? 'No Category',
        ];

        return implode(' - ', $parts);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $currentUser = auth()->user();

        // Apply user-based filtering for all queries
        if ($currentUser && $currentUser->isBuildingSupervisor()) {
            $supervisedBuildingIds = $currentUser->supervisedBuildings()->pluck('id');
            $query->whereHas('ticket', function ($ticketQuery) use ($supervisedBuildingIds) {
                $ticketQuery->whereIn('building_id', $supervisedBuildingIds);
            });
        } elseif ($currentUser && $currentUser->isCategorySupervisor()) {
            $supervisedCategoryIds = $currentUser->supervisedCategories()->pluck('id');
            if ($supervisedCategoryIds->isNotEmpty()) {
                $query->whereHas('ticket', function ($ticketQuery) use ($supervisedCategoryIds) {
                    $ticketQuery->whereIn('category_id', $supervisedCategoryIds);
                });
            } else {
                // If user doesn't supervise any categories, they shouldn't see any material requests
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }
}
