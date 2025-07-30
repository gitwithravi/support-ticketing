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
        Schema::table('buildings', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['building_supervisor_id']);

            // Drop the index
            $table->dropIndex(['building_supervisor_id']);

            // Drop the column
            $table->dropColumn('building_supervisor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            // Re-add the column
            $table->foreignUlid('building_supervisor_id')
                ->nullable()
                ->after('is_active')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Building supervisor user');

            // Re-add the index
            $table->index('building_supervisor_id');
        });
    }
};
