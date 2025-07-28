<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use App\Models\Breakage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BreakagesRelationManager extends RelationManager
{
    protected static string $relationship = 'breakages';

    protected static ?string $recordTitleAttribute = 'breakage_description';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('breakage_description')
                    ->label('Breakage Description')
                    ->rows(4)
                    ->required()
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('responsible_reg_nos')
                    ->label('Responsible Registration Numbers')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Toggle::make('processed')
                    ->label('Processed')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('breakage_description')
            ->columns([
                Tables\Columns\TextColumn::make('breakage_description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('responsible_reg_nos')
                    ->label('Responsible Reg Nos')
                    ->searchable(),
                
                Tables\Columns\IconColumn::make('processed')
                    ->label('Processed')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
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
                Tables\Filters\TernaryFilter::make('processed')
                    ->label('Processed Status')
                    ->placeholder('All breakages')
                    ->trueLabel('Processed')
                    ->falseLabel('Not processed'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
            ->defaultSort('created_at', 'desc');
    }
}