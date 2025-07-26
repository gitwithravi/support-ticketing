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
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignUlid('category_id')
                ->nullable()
                ->after('group_id')
                ->constrained()
                ->nullOnDelete();
            
            $table->foreignUlid('sub_category_id')
                ->nullable()
                ->after('category_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            
            $table->dropForeign(['sub_category_id']);
            $table->dropColumn('sub_category_id');
        });
    }
};