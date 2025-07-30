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
        Schema::create('building_supervisor', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('building_id')
                ->constrained('buildings')
                ->cascadeOnDelete();
            $table->foreignUlid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamps();

            // Add indexes for performance
            $table->unique(['building_id', 'user_id']);
            $table->index('building_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_supervisor');
    }
};
