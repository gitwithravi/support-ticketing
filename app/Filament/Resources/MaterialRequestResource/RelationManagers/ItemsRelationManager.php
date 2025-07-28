<?php

namespace App\Filament\Resources\MaterialRequestResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'item_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('item_name')
                    ->label('Item Name')
                    ->required()
                    ->maxLength(1023)
                    ->placeholder('Enter the name of the material item')
                    ->columnSpanFull(),

                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Enter quantity'),

                        Forms\Components\TextInput::make('uom')
                            ->label('Unit of Measure')
                            ->maxLength(255)
                            ->placeholder('e.g., pcs, kg, meters')
                            ->helperText('Optional: Specify the unit of measurement'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item_name')
            ->columns([
                Tables\Columns\TextColumn::make('item_name')
                    ->label('Item Name')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('uom')
                    ->label('Unit')
                    ->searchable()
                    ->placeholder('N/A')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_uom')
                    ->label('Has Unit of Measure')
                    ->query(fn ($query) => $query->whereNotNull('uom')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Item')
                    ->createAnother(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No items added')
            ->emptyStateDescription('Add material items to this request using the button above.')
            ->emptyStateIcon('heroicon-o-cube');
    }
}
