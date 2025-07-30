<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            logger('Google OAuth callback', [
                'google_id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
            ]);

            logger('About to check for existing clients');

            // Check if user already exists with this Google ID
            $client = Client::where('google_id', $googleUser->getId())->first();

            logger('Checked for existing Google user', [
                'found_google_user' => ! is_null($client),
                'client_id' => $client?->id,
            ]);

            if ($client) {
                // Login existing user
                logger('Logging in existing Google user', [
                    'client_id' => $client->id,
                    'client_active' => $client->is_active,
                    'client_verified' => $client->hasVerifiedEmail(),
                    'client_provider' => $client->provider,
                ]);

                // First regenerate session BEFORE login to avoid destroying auth
                request()->session()->regenerate();

                // Login using Laravel's client guard
                Auth::guard('client')->login($client, false);

                logger('After Laravel client guard login', [
                    'auth_check' => Auth::guard('client')->check(),
                    'user_id' => Auth::guard('client')->id(),
                    'session_id' => session()->getId(),
                ]);

                // Save session to ensure it persists
                session()->save();

                logger('Before redirect, final check', [
                    'auth_check' => Auth::guard('client')->check(),
                    'user_id' => Auth::guard('client')->id(),
                    'session_id' => session()->getId(),
                ]);

                return redirect('/client');
            }

            // Check if user exists with same email but different provider
            $existingClient = Client::where('email', $googleUser->getEmail())->first();

            logger('Checking existing client by email', [
                'email' => $googleUser->getEmail(),
                'existing_client_found' => ! is_null($existingClient),
                'existing_client_id' => $existingClient?->id,
                'existing_client_provider' => $existingClient?->provider,
                'existing_client_google_id' => $existingClient?->google_id,
                'is_local_provider' => $existingClient?->provider === 'local',
            ]);

            if ($existingClient) {
                // Handle existing client with same email
                if ($existingClient->google_id === $googleUser->getId()) {
                    // User already has this Google account linked, just login
                    logger('User already has Google account linked, logging in', [
                        'client_id' => $existingClient->id,
                    ]);

                    Filament::auth()->login($existingClient, false);
                    request()->session()->regenerate();
                    session()->save();

                    return redirect('/client');
                } elseif ($existingClient->google_id && $existingClient->google_id !== $googleUser->getId()) {
                    // User has a different Google account linked
                    logger('User has different Google account linked');

                    return redirect('/client/login')->withErrors([
                        'email' => 'This email is linked to a different Google account.',
                    ]);
                } else {
                    // User has local account, link Google account
                    logger('User exists with local account, linking Google account', [
                        'client_id' => $existingClient->id,
                        'google_id' => $googleUser->getId(),
                    ]);

                    // Update the existing client with Google data (keep original provider)
                    $updateResult = $existingClient->update([
                        'google_id' => $googleUser->getId(),
                        // Keep original provider so user can still login with password
                        'email_verified_at' => Carbon::now(), // Auto-verify email since it's from Google
                    ]);

                    logger('Database update result', [
                        'update_successful' => $updateResult,
                        'client_google_id_after_update' => $existingClient->fresh()->google_id,
                    ]);

                    // Regenerate session first
                    request()->session()->regenerate();

                    // Login the updated user with Laravel client guard
                    Auth::guard('client')->login($existingClient, false);

                    logger('After client guard login for linked account', [
                        'client_id' => $existingClient->id,
                        'auth_check' => Auth::guard('client')->check(),
                        'auth_id' => Auth::guard('client')->id(),
                        'session_id' => session()->getId(),
                    ]);

                    // Save session explicitly before redirect
                    session()->save();

                    logger('Before redirect - final auth status', [
                        'auth_check' => Auth::guard('client')->check(),
                        'session_id' => session()->getId(),
                    ]);

                    return redirect('/client');
                }
            }

            // Store Google user data in session for registration
            $googleUserData = [
                'id' => $googleUser->getId(),
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
            ];

            Session::put('google_user', $googleUserData);
            logger('Storing Google user data in session', $googleUserData);

            // Redirect to complete registration with unique_id
            logger('Redirecting to Google registration page');

            return redirect()->route('client.auth.google.register');

        } catch (\Exception $e) {
            logger('Google OAuth error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect('/client/login')->withErrors([
                'error' => 'Authentication failed. Please try again.',
            ]);
        }
    }
}
