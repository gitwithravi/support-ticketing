<?php

namespace App\Filament\Client\Pages\Auth;

use App\Models\Client;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class GoogleRegister extends SimplePage
{
    use InteractsWithFormActions;

    protected static string $layout = 'filament-panels::components.layout.simple';

    protected static string $view = 'filament.client.auth.google-register';

    public ?array $data = [];

    protected ?string $googleUserName = null;

    protected ?string $googleUserEmail = null;

    public function mount(): void
    {
        // Check if Google user data exists in session
        $googleUser = Session::get('google_user');

        logger('GoogleRegister mount called', [
            'has_google_user' => ! is_null($googleUser),
            'google_user' => $googleUser,
        ]);

        if (! $googleUser) {
            logger('No Google user data found, redirecting to login');
            $this->redirect('/client/login');

            return;
        }

        $this->googleUserName = $googleUser['name'];
        $this->googleUserEmail = $googleUser['email'];

        // Pre-fill form with Google data
        $this->form->fill([
            'name' => $googleUser['name'],
            'email' => $googleUser['email'],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Complete Your Registration')
                    ->description('Please provide your unique ID to complete your Google registration.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->default($this->googleUserName)
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->default($this->googleUserEmail)
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('unique_id')
                            ->label('Employee/Registration ID')
                            ->required()
                            ->maxLength(255)
                            ->unique('clients', 'unique_id')
                            ->autofocus(),
                    ]),
            ])
            ->statePath('data');
    }

    public function register(): void
    {
        $googleUser = Session::get('google_user');

        if (! $googleUser) {
            throw ValidationException::withMessages([
                'error' => 'Registration session expired. Please try again.',
            ]);
        }

        $data = $this->form->getState();

        // Create new client with Google data
        logger('Creating new Google user', [
            'name' => $googleUser['name'],
            'email' => $googleUser['email'],
            'unique_id' => $data['unique_id'],
            'google_id' => $googleUser['id'],
        ]);

        $client = Client::create([
            'name' => $googleUser['name'],
            'email' => $googleUser['email'],
            'unique_id' => $data['unique_id'],
            'google_id' => $googleUser['id'],
            'provider' => 'google',
            'email_verified_at' => Carbon::now(),
            'is_active' => true,
            'timezone' => 'Asia/Calcutta',
            'locale' => 'en',
            'password' => null,
        ]);

        logger('Google user created successfully', [
            'client_id' => $client->id,
            'has_password' => $client->hasPassword(),
            'has_google_account' => $client->hasGoogleAccount(),
        ]);

        // Clear session data
        Session::forget('google_user');

        // Regenerate session first
        request()->session()->regenerate();

        // Login the new user using Laravel client guard
        Auth::guard('client')->login($client, false);

        logger('New Google user registered and logged in', [
            'client_id' => $client->id,
            'auth_check' => Auth::guard('client')->check(),
            'auth_id' => Auth::guard('client')->id(),
        ]);

        Notification::make()
            ->title('Registration successful!')
            ->success()
            ->send();

        // Ensure session is saved
        session()->save();

        $this->redirect('/client');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('register')
                ->label('Complete Registration')
                ->submit('register'),
        ];
    }

    public function getTitle(): string
    {
        return 'Complete Google Registration';
    }

    public function getHeading(): string
    {
        return 'Complete Google Registration';
    }

    public function hasLogo(): bool
    {
        return true;
    }
}
