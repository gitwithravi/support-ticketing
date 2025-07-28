<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BreakageResource\Pages;
use App\Models\Breakage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BreakageResource extends Resource
{
    protected static ?string $model = Breakage::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Ticket Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Breakage Information')
                    ->schema([
                        Forms\Components\Select::make('ticket_id')
                            ->label('Ticket')
                            ->relationship('ticket', 'ticket_id')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\Textarea::make('breakage_description')
                            ->label('Breakage Description')
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('responsible_reg_nos')
                            ->label('Responsible Registration Numbers')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Toggle::make('processed')
                            ->label('Processed')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket.ticket_id')
                    ->label('Ticket ID')
                    ->searchable()
                    ->sortable(),
                
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
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBreakages::route('/'),
            'create' => Pages\CreateBreakage::route('/create'),
            'view' => Pages\ViewBreakage::route('/{record}'),
            'edit' => Pages\EditBreakage::route('/{record}/edit'),
        ];
    }
}