<?php

namespace Tests\Unit\Settings;

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
        $settings->access_key = env('ACCESS_KEY', 'test-access-key');
        $settings->access_secret = env('ACCESS_SECRET', 'test-access-secret');
        $settings->save();

        // Verify the settings are using testing values
        $this->assertEquals('https://test-api.example.com', $settings->api_endpoint);
        $this->assertEquals('test-access-key', $settings->access_key);
        $this->assertEquals('test-access-secret', $settings->access_secret);
    }

    public function test_prf_api_service_uses_testing_settings(): void
    {
        // Run the settings migration first
        $this->artisan('migrate', ['--path' => 'database/settings']);
        
        // Seed the settings with testing values
        $settings = app(PrfApiSettings::class);
        $settings->api_endpoint = 'https://test-api.example.com';
        $settings->access_key = 'test-access-key';
        $settings->access_secret = 'test-access-secret';
        $settings->save();

        // Test that the service can be instantiated without errors
        $service = app(\App\Services\PrfApiService::class);
        $this->assertInstanceOf(\App\Services\PrfApiService::class, $service);
    }
}