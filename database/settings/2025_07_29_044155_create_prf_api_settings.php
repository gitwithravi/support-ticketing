<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('prf_api.api_endpoint', '');
        $this->migrator->add('prf_api.access_key', '');
        $this->migrator->add('prf_api.access_secret', '');
    }
};
