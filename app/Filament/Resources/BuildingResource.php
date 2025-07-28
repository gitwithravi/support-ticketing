<?php

namespace App\Filament\Resources;

use App\Enums\Buildings\BuildingType;
use App\Enums\Users\UserType;
use App\Filament\Resources\BuildingResource\Pages;
use App\Models\Building;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Facility Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Basic Information'))
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('Building Name'))
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('e.g., Engineering Building'),

                                        Forms\Components\TextInput::make('code')
                                            ->label(__('Building Code'))
                                            ->required()
                                            ->maxLength(10)
                                            ->unique(ignoreRecord: true)
                                            ->placeholder('e.g., ENG, ADMIN, LIB')
                                            ->alphaNum()
                                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, $set) => $set('code', strtoupper($state))),
                                    ]),

                                Forms\Components\Textarea::make('description')
                                    ->label(__('Description'))
                                    ->maxLength(65535)
                                    ->placeholder('Brief description of the building purpose and facilities')
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('address')
                                    ->label(__('Address'))
                                    ->maxLength(65535)
                                    ->placeholder('Physical address of the building')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make(__('Building Details'))
                            ->schema([
                                Forms\Components\Select::make('building_type')
                                    ->label(__('Building Type'))
                                    ->options(BuildingType::options())
                                    ->default(BuildingType::ACADEMIC_BLOCK->value)
                                    ->required()
                                    ->searchable(),

                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('floors')
                                            ->label(__('Number of Floors'))
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->maxValue(50),

                                        Forms\Components\TextInput::make('total_rooms')
                                            ->label(__('Total Rooms'))
                                            ->numeric()
                                            ->minValue(0)
                                            ->placeholder('Optional'),

                                        Forms\Components\TextInput::make('construction_year')
                                            ->label(__('Construction Year'))
                                            ->numeric()
                                            ->minValue(1800)
                                            ->maxValue(date('Y') + 5)
                                            ->placeholder('e.g., 1995'),
                                    ]),
                            ]),

                        Forms\Components\Section::make(__('Location'))
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('latitude')
                                            ->label(__('Latitude'))
                                            ->numeric()
                                            ->step(0.00000001)
                                            ->placeholder('e.g., 40.7128'),

                                        Forms\Components\TextInput::make('longitude')
                                            ->label(__('Longitude'))
                                            ->numeric()
                                            ->step(0.00000001)
                                            ->placeholder('e.g., -74.0060'),
                                    ]),
                            ])
                            ->collapsible(),

                        Forms\Components\Section::make(__('Contact Information'))
                            ->schema([
                                Forms\Components\KeyValue::make('contact_info')
                                    ->label(__('Contact Details'))
                                    ->keyLabel(__('Contact Type'))
                                    ->valueLabel(__('Information'))
                                    ->default([
                                        'Emergency Contact' => '',
                                        'Facility Manager' => '',
                                        'Phone' => '',
                                        'Email' => '',
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->collapsible(),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Supervisor Assignment'))
                            ->schema([
                                Forms\Components\Select::make('building_supervisor_id')
                                    ->label(__('Building Supervisor'))
                                    ->relationship(
                                        name: 'supervisor',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) => $query->where('user_type', UserType::BUILDING_SUPERVISOR->value)
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->placeholder(__('Select a building supervisor'))
                                    ->helperText(__('Only users with Building Supervisor role are shown'))
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('Name'))
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->label(__('Email'))
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Select::make('user_type')
                                            ->label(__('User Type'))
                                            ->options([UserType::BUILDING_SUPERVISOR->value => UserType::BUILDING_SUPERVISOR->getLabel()])
                                            ->default(UserType::BUILDING_SUPERVISOR->value)
                                            ->required()
                                            ->disabled(),
                                        Forms\Components\Toggle::make('is_active')
                                            ->label(__('Active'))
                                            ->default(true),
                                    ])
                                    ->createOptionModalHeading(__('Create Building Supervisor')),
                            ]),

                        Forms\Components\Section::make(__('Status'))
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true)
                                    ->helperText(__('Inactive buildings will not appear in ticket creation forms')),
                            ]),

                        Forms\Components\Section::make(__('Metadata'))
                            ->schema([
                                Forms\Components\Placeholder::make('supervisor.name')
                                    ->label(__('Current Supervisor'))
                                    ->content(fn (?Building $record): string => $record?->supervisor?->name ?? __('No supervisor assigned')),

                                Forms\Components\Placeholder::make('tickets_count')
                                    ->label(__('Total Tickets'))
                                    ->content(fn (?Building $record): string => $record ? $record->tickets()->count().' tickets' : '0 tickets'),

                                Forms\Components\Placeholder::make('age')
                                    ->label(__('Building Age'))
                                    ->content(fn (?Building $record): string => $record?->age ? $record->age.' years old' : __('Unknown')),

                                Forms\Components\Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (?Building $record): ?string => $record?->created_at?->diffForHumans()),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label(__('Updated at'))
                                    ->content(fn (?Building $record): ?string => $record?->updated_at?->diffForHumans()),
                            ])->hiddenOn(['create']),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('Code'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (Building $record): ?string => $record->description),

                Tables\Columns\TextColumn::make('building_type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (BuildingType $state): string => $state->getColor())
                    ->icon(fn (BuildingType $state): string => $state->getIcon())
                    ->sortable(),

                Tables\Columns\TextColumn::make('supervisor.name')
                    ->label(__('Supervisor'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('No supervisor'))
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('floors')
                    ->label(__('Floors'))
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tickets_count')
                    ->label(__('Tickets'))
                    ->counts('tickets')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('construction_year')
                    ->label(__('Built'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('building_type')
                    ->label(__('Building Type'))
                    ->options(BuildingType::options())
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('building_supervisor_id')
                    ->label(__('Supervisor'))
                    ->relationship(
                        name: 'supervisor',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->where('user_type', UserType::BUILDING_SUPERVISOR->value)
                    )
                    ->searchable()
                    ->preload()
                    ->placeholder(__('All supervisors')),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->trueLabel(__('Active buildings'))
                    ->falseLabel(__('Inactive buildings'))
                    ->native(false),

                Tables\Filters\Filter::make('unassigned')
                    ->label(__('Unassigned Buildings'))
                    ->query(fn ($query) => $query->whereNull('building_supervisor_id'))
                    ->toggle(),

                Tables\Filters\Filter::make('has_location')
                    ->label(__('Has GPS Coordinates'))
                    ->query(fn ($query) => $query->whereNotNull('latitude')->whereNotNull('longitude'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('building_type')
                    ->label(__('Building Type'))
                    ->collapsible(),

                Tables\Grouping\Group::make('supervisor.name')
                    ->label(__('Supervisor'))
                    ->collapsible(),
            ])
            ->defaultSort('code');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuildings::route('/'),
            'create' => Pages\CreateBuilding::route('/create'),
            'edit' => Pages\EditBuilding::route('/{record}/edit'),
        ];
    }
}
