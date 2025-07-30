<?php

namespace App\Filament\Client\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('unique_id')
                            ->label('Unique ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\Select::make('locale')
                            ->options([
                                'en' => 'English',
                                'es' => 'Spanish',
                                'fr' => 'French',
                                'de' => 'German',
                            ])
                            ->default('en'),

                        Forms\Components\Select::make('timezone')
                            ->options(collect(timezone_identifiers_list())->mapWithKeys(fn ($timezone) => [$timezone => $timezone]))
                            ->searchable()
                            ->default('UTC'),
                    ]),

                Forms\Components\Section::make('Update Password')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->password()
                            ->currentPassword()
                            ->requiredWith('password'),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->minLength(8)
                            ->same('passwordConfirmation')
                            ->dehydrated(fn ($state): bool => filled($state)),

                        Forms\Components\TextInput::make('passwordConfirmation')
                            ->password()
                            ->requiredWith('password')
                            ->dehydrated(false),
                    ])
                    ->columns(1)
                    ->hidden(fn (): bool => $this->getUser()->password === null),
            ]);
    }
}
