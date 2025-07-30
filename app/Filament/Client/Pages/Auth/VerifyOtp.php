<?php

namespace App\Filament\Client\Pages\Auth;

use App\Notifications\OtpVerification;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class VerifyOtp extends Page implements HasActions, HasForms
{
    use InteractsWithActions, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string $view = 'filament.client.auth.verify-otp';

    protected static string $routePath = '/verify-otp';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public array $code = ['', '', '', '', '', ''];

    public int $secondsRemaining = 0;

    protected static string $layout = 'filament-panels::components.layout.base';

    public function mount(): void
    {
        if (Auth::guard('client')->check()) {
            $client = Auth::guard('client')->user();

            if ($client->hasVerifiedEmail()) {
                redirect()->intended('/client');
            }
        } else {
            redirect('/client/auth/login');
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('otp')
                    ->label('Verification Code')
                    ->placeholder('Enter 6-digit code')
                    ->required()
                    ->maxLength(6)
                    ->minLength(6)
                    ->numeric()
                    ->autocomplete('one-time-code')
                    ->autofocus()
                    ->extraInputAttributes([
                        'class' => 'text-center text-lg font-mono tracking-wider',
                    ]),
            ])
            ->statePath('data');
    }

    public function verify(): void
    {
        $otp = implode('', $this->code);

        if (strlen($otp) !== 6) {
            $this->addError('code', 'Please enter all 6 digits.');

            return;
        }

        $client = Auth::guard('client')->user();

        if (! $client) {
            $this->addError('code', 'Session expired. Please login again.');

            return;
        }

        if (! $client->verifyOtp($otp)) {
            $this->addError('code', 'Invalid or expired verification code.');
            $this->code = ['', '', '', '', '', ''];

            return;
        }

        $client->markEmailAsVerified();

        Notification::make()
            ->title('Email verified successfully!')
            ->success()
            ->send();

        redirect()->intended('/client');
    }

    public function resend(): void
    {
        if ($this->secondsRemaining > 0) {
            return;
        }

        $client = Auth::guard('client')->user();

        if (! $client) {
            $this->addError('code', 'Session expired. Please login again.');

            return;
        }

        $key = 'resend-otp:'.$client->id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('code', "Too many attempts. Please wait {$seconds} seconds before requesting another code.");

            return;
        }

        RateLimiter::hit($key, 300); // 5 minutes
        $this->secondsRemaining = 60; // 1 minute countdown

        $otp = $client->generateOtp();
        $client->notify(new OtpVerification($otp));

        Notification::make()
            ->title('Verification code sent!')
            ->body('A new verification code has been sent to your email.')
            ->success()
            ->send();

        // Start countdown
        $this->dispatch('start-countdown');
    }

    public function decrementCountdown(): void
    {
        if ($this->secondsRemaining > 0) {
            $this->secondsRemaining--;
        }
    }

    public function getFormActions(): array
    {
        return [
            Action::make('verify')
                ->label('Verify Email')
                ->size('lg')
                ->color('primary')
                ->submit('verify'),
        ];
    }

    public function resendAction(): Action
    {
        return Action::make('resend')
            ->label('Resend Code')
            ->color('gray')
            ->outlined()
            ->size('lg')
            ->action('resendOtp');
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->label('Back to Login')
            ->url('/client/login')
            ->color('gray')
            ->outlined()
            ->size('lg');
    }

    public function getTitle(): string
    {
        return 'Verify Your Email';
    }

    public function getHeading(): string
    {
        return 'Email Verification';
    }

    public function getSubheading(): string
    {
        $email = Auth::guard('client')->user()?->email ?? '';

        return "We've sent a 6-digit verification code to {$email}. Please enter it below to complete your registration.";
    }
}
