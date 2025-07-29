<?php

namespace Database\Seeders;

use App\Settings\PrfApiSettings;
use Illuminate\Database\Seeder;

class PrfApiSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(PrfApiSettings::class);

        $settings->api_endpoint = env('API_ENDPOINT', 'xxxx');
        $settings->access_key = env('ACCESS_KEY', 'xxxx');
        $settings->access_secret = env('ACCESS_SECRET', 'xxxx');

        $settings->save();
    }
}