<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing building-supervisor relationships to the pivot table
        $buildings = DB::table('buildings')
            ->whereNotNull('building_supervisor_id')
            ->get(['id', 'building_supervisor_id']);

        foreach ($buildings as $building) {
            DB::table('building_supervisor')->insert([
                'building_id' => $building->id,
                'user_id' => $building->building_supervisor_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the relationships back to the buildings table
        $relationships = DB::table('building_supervisor')->get();

        foreach ($relationships as $relationship) {
            // Only restore the first supervisor for each building (limitation of one-to-many)
            $existingSupervisor = DB::table('buildings')
                ->where('id', $relationship->building_id)
                ->value('building_supervisor_id');

            if (! $existingSupervisor) {
                DB::table('buildings')
                    ->where('id', $relationship->building_id)
                    ->update(['building_supervisor_id' => $relationship->user_id]);
            }
        }

        // Clear the pivot table
        DB::table('building_supervisor')->truncate();
    }
};
