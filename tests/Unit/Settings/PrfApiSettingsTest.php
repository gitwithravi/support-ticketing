<?php

namespace Tests\Unit\Settings;

use App\Enums\Users\UserType;
use App\Models\User;
use App\Services\PrfApiService;
use App\Settings\PrfApiSettings;
use Tests\TestCase;

class PrfApiSettingsTest extends TestCase
{
    public function test_prf_api_settings_can_be_instantiated(): void
    {
        $settings = app(PrfApiSettings::class);

        $this->assertInstanceOf(PrfApiSettings::class, $settings);
    }

    public function test_prf_api_settings_has_correct_group(): void
    {
        $this->assertEquals('prf_api', PrfApiSettings::group());
    }

    public function test_prf_api_settings_uses_testing_values(): void
    {
        // Run the settings migration first
        $this->artisan('migrate', ['--path' => 'database/settings']);

        // Seed the settings with testing values
        $settings = app(PrfApiSettings::class);
        $settings->api_endpoint = env('API_ENDPOINT', 'https://test-api.example.com');
        $settings->save();

        // Verify the settings are using testing values
        $this->assertEquals('https://test-api.example.com', $settings->api_endpoint);
    }

    public function test_prf_api_service_uses_user_credentials(): void
    {
        // Run the settings migration first
        $this->artisan('migrate', ['--path' => 'database/settings']);

        // Seed the settings with testing values
        $settings = app(PrfApiSettings::class);
        $settings->api_endpoint = 'https://test-api.example.com';
        $settings->save();

        // Create a test user with PRF API credentials
        $user = User::factory()->create([
            'user_type' => UserType::ADMIN,
            'prf_api_access_key' => 'test-access-key',
            'prf_api_access_secret' => 'test-access-secret',
        ]);

        // Test that the service can be instantiated with user credentials
        $service = new PrfApiService($user);
        $this->assertInstanceOf(PrfApiService::class, $service);
    }

    public function test_user_has_prf_api_credentials(): void
    {
        $userWithCredentials = User::factory()->create([
            'prf_api_access_key' => 'test-key',
            'prf_api_access_secret' => 'test-secret',
        ]);

        $userWithoutCredentials = User::factory()->create([
            'prf_api_access_key' => null,
            'prf_api_access_secret' => null,
        ]);

        $this->assertTrue($userWithCredentials->hasPrfApiCredentials());
        $this->assertFalse($userWithoutCredentials->hasPrfApiCredentials());
    }

    public function test_user_get_prf_api_credentials(): void
    {
        $user = User::factory()->create([
            'prf_api_access_key' => 'test-key',
            'prf_api_access_secret' => 'test-secret',
        ]);

        $credentials = $user->getPrfApiCredentials();

        $this->assertEquals('test-key', $credentials['access_key']);
        $this->assertEquals('test-secret', $credentials['access_secret']);
    }
}
