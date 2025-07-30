<?php

use App\Enums\Users\UserType;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing PRF API credentials from settings to admin and supervisor users
        $existingCredentials = DB::table('settings')
            ->where('group', 'prf_api')
            ->whereIn('name', ['access_key', 'access_secret'])
            ->pluck('payload', 'name');

        if ($existingCredentials->isNotEmpty()) {
            $accessKey = json_decode($existingCredentials->get('access_key'), true);
            $accessSecret = json_decode($existingCredentials->get('access_secret'), true);

            if (! empty($accessKey) && ! empty($accessSecret)) {
                // Apply credentials to admin and supervisor users
                // Note: These will be encrypted by Laravel's encrypted cast automatically
                User::whereIn('user_type', [
                    UserType::ADMIN->value,
                    UserType::CATEGORY_SUPERVISOR->value,
                    UserType::BUILDING_SUPERVISOR->value,
                ])->update([
                    'prf_api_access_key' => $accessKey,
                    'prf_api_access_secret' => $accessSecret,
                ]);
            }

            // Remove old settings
            DB::table('settings')
                ->where('group', 'prf_api')
                ->whereIn('name', ['access_key', 'access_secret'])
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get a sample of existing credentials from admin users
        $sampleUser = User::where('user_type', UserType::ADMIN->value)
            ->whereNotNull('prf_api_access_key')
            ->whereNotNull('prf_api_access_secret')
            ->first();

        if ($sampleUser) {
            // Restore settings from sample user
            DB::table('settings')->insert([
                [
                    'group' => 'prf_api',
                    'name' => 'access_key',
                    'payload' => json_encode($sampleUser->prf_api_access_key),
                    'locked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'group' => 'prf_api',
                    'name' => 'access_secret',
                    'payload' => json_encode($sampleUser->prf_api_access_secret),
                    'locked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // Clear credentials from users
        User::update([
            'prf_api_access_key' => null,
            'prf_api_access_secret' => null,
        ]);
    }
};
