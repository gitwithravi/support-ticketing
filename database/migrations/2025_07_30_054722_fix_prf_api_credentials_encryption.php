<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix the PRF API credentials that were stored as plain text but need to be encrypted
        $users = DB::table('users')
            ->whereNotNull('prf_api_access_key')
            ->whereNotNull('prf_api_access_secret')
            ->get();

        foreach ($users as $user) {
            // Get the plain text values
            $accessKey = $user->prf_api_access_key;
            $accessSecret = $user->prf_api_access_secret;

            // Check if they're already encrypted by trying to decrypt
            try {
                Crypt::decrypt($accessKey);

                // If no exception, they're already encrypted, skip this user
                continue;
            } catch (\Exception $e) {
                // They're not encrypted, proceed with encryption
            }

            // Encrypt and update
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'prf_api_access_key' => Crypt::encrypt($accessKey),
                    'prf_api_access_secret' => Crypt::encrypt($accessSecret),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Decrypt the credentials back to plain text
        $users = DB::table('users')
            ->whereNotNull('prf_api_access_key')
            ->whereNotNull('prf_api_access_secret')
            ->get();

        foreach ($users as $user) {
            try {
                // Try to decrypt the values
                $accessKey = Crypt::decrypt($user->prf_api_access_key);
                $accessSecret = Crypt::decrypt($user->prf_api_access_secret);

                // Update with plain text values
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'prf_api_access_key' => $accessKey,
                        'prf_api_access_secret' => $accessSecret,
                    ]);
            } catch (\Exception $e) {
                // If decryption fails, they're probably already plain text
                continue;
            }
        }
    }
};
