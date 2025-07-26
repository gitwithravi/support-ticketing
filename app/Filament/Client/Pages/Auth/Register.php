<?php

namespace App\Filament\Client\Pages\Auth;

use App\Models\Client;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getUniqueIdFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function getUniqueIdFormComponent(): Component
    {
        return TextInput::make('unique_id')
            ->label('Employee/Registration ID')
            ->required()
            ->maxLength(255)
            ->unique(Client::class);
    }

    protected function getUserData(): array
    {
        $data = parent::getUserData();
        
        // Set default timezone and locale
        $data['timezone'] = 'Asia/Calcutta';
        $data['locale'] = 'en';
        $data['is_active'] = true;
        
        return $data;
    }

    protected function handleRegistration(array $data): Model
    {
        return $this->getUserModel()::create($data);
    }
}