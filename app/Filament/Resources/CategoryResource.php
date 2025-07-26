<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers\SubCategoriesRelationManager;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Ticket Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\Textarea::make('description')
                                    ->label(__('Description'))
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                                
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\ColorPicker::make('color')
                                            ->label(__('Color'))
                                            ->default('#6366f1'),
                                        
                                        Forms\Components\Select::make('icon')
                                            ->label(__('Icon'))
                                            ->options([
                                                'heroicon-o-folder' => 'Folder',
                                                'heroicon-o-computer-desktop' => 'Computer',
                                                'heroicon-o-wrench-screwdriver' => 'Tools',
                                                'heroicon-o-light-bulb' => 'Light Bulb',
                                                'heroicon-o-wifi' => 'WiFi',
                                                'heroicon-o-academic-cap' => 'Academic',
                                                'heroicon-o-building-office' => 'Building',
                                                'heroicon-o-home' => 'Home',
                                                'heroicon-o-cog-6-tooth' => 'Settings',
                                                'heroicon-o-exclamation-triangle' => 'Warning',
                                            ])
                                            ->default('heroicon-o-folder')
                                            ->searchable(),
                                    ]),
                                
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label(__('Active'))
                                            ->default(true),
                                        
                                        Forms\Components\TextInput::make('sort_order')
                                            ->label(__('Sort Order'))
                                            ->numeric()
                                            ->default(0),
                                    ]),
                            ]),
                    ])->columnSpan(['lg' => 2]),
                
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Metadata'))
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (Category $record): ?string => $record->created_at?->diffForHumans()),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label(__('Updated at'))
                                    ->content(fn (Category $record): ?string => $record->updated_at?->diffForHumans()),
                            ])->hiddenOn(['create']),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label(__('Color')),
                
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                
                Tables\Columns\IconColumn::make('icon')
                    ->label(__('Icon')),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('Sort Order'))
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('sub_categories_count')
                    ->label(__('Sub Categories'))
                    ->counts('subCategories'),
                
                Tables\Columns\TextColumn::make('tickets_count')
                    ->label(__('Tickets'))
                    ->counts('tickets'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->trueLabel(__('Active categories'))
                    ->falseLabel(__('Inactive categories'))
                    ->native(false),
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
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            SubCategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}