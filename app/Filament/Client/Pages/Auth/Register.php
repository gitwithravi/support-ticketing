<?php

namespace App\Filament\Client\Pages\Auth;

use App\Models\Client;
use App\Notifications\OtpVerification;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;

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
        $data['is_active'] = false; // Will be activated after OTP verification

        return $data;
    }

    protected function handleRegistration(array $data): Model
    {
        $client = $this->getUserModel()::create($data);
        
        // Generate and send OTP
        $otp = $client->generateOtp();
        $client->notify(new OtpVerification($otp));
        
        return $client;
    }

    protected function getRedirectUrl(): ?string
    {
        return '/client/verify-otp';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Create your account';
    }

    public function getView(): string
    {
        return 'filament.client.auth.register';
    }
}
