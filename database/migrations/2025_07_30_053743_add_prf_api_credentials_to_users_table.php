<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('prf_api_access_key')
                ->nullable()
                ->after('is_active')
                ->comment('Encrypted PRF API access key for this user');

            $table->text('prf_api_access_secret')
                ->nullable()
                ->after('prf_api_access_key')
                ->comment('Encrypted PRF API access secret for this user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['prf_api_access_key', 'prf_api_access_secret']);
        });
    }
};
