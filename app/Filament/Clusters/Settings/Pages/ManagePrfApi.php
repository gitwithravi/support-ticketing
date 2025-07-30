<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Settings\PrfApiSettings;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManagePrfApi extends SettingsPage
{
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'PRF API';

    protected static ?string $slug = 'prf-api';

    protected static ?string $title = 'PRF API Settings';

    protected ?string $heading = 'PRF API Settings';

    protected static string $settings = PrfApiSettings::class;

    protected static ?string $cluster = Settings::class;

    public static function canAccess(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('API Configuration'))
                    ->description(__('Configure the shared PRF API endpoint. Individual users need to configure their own access credentials in their profile.'))
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('api_endpoint')
                                    ->label(__('API Endpoint'))
                                    ->url()
                                    ->maxLength(255)
                                    ->required()
                                    ->placeholder('https://api.example.com')
                                    ->helperText(__('The base URL for the PRF API service. This is shared across all users.')),
                            ]),
                    ]),
            ]);
    }
}
