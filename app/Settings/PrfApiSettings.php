<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PrfApiSettings extends Settings
{
    public string $api_endpoint;

    public static function group(): string
    {
        return 'prf_api';
    }
}
