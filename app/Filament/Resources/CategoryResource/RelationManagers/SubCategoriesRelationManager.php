<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubCategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'subCategories';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
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
                                'heroicon-o-document' => 'Document',
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
                            ->default('heroicon-o-document')
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label(__('Color')),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(50),

                Tables\Columns\IconColumn::make('icon')
                    ->label(__('Icon')),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('Sort Order'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('tickets_count')
                    ->label(__('Tickets'))
                    ->counts('tickets'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->trueLabel(__('Active sub-categories'))
                    ->falseLabel(__('Inactive sub-categories'))
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
