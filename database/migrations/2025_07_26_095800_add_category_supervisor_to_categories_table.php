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
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignUlid('category_supervisor_id')
                ->nullable()
                ->after('sort_order')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Category supervisor user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['category_supervisor_id']);
            $table->dropColumn('category_supervisor_id');
        });
    }
};
