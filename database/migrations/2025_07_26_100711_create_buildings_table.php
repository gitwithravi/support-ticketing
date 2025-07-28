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
        Schema::create('buildings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('code')->unique()->comment('Building code/identifier (e.g., ADMIN, ENG, LIB)');
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->string('building_type')->default('academic_block')->comment('academic_block, boys_hostel, girls_hostel, staff_quarters, parking, others');
            $table->integer('floors')->default(1);
            $table->integer('total_rooms')->nullable();
            $table->year('construction_year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignUlid('building_supervisor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Building supervisor user');
            $table->json('contact_info')->nullable()->comment('Emergency contacts, facility manager info, etc.');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();

            // Add indexes for performance
            $table->index('building_type');
            $table->index('is_active');
            $table->index('building_supervisor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
