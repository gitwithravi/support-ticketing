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
            $table->string('room_no')->nullable()->after('building_id')->comment('Room number where maintenance is required');
            $table->text('ticket_description')->after('room_no')->comment('Detailed description of the issue/request');
            $table->string('user_status')->default('open')->after('ticket_description')->comment('Status from user perspective: open, close');
            $table->string('cat_supervisor_status')->default('open')->after('user_status')->comment('Status from category supervisor perspective: open, close');
            $table->string('build_supervisor_status')->default('open')->after('cat_supervisor_status')->comment('Status from building supervisor perspective: open, close');
            $table->foreignUlid('verified_by')->nullable()->after('build_supervisor_status')->constrained('users')->nullOnDelete()->comment('User who verified/closed the ticket');
            $table->datetime('ticket_closing_date')->nullable()->after('verified_by')->comment('Date when ticket was closed');
            $table->timestamp('verification_timestamp')->nullable()->after('ticket_closing_date');
            $table->string('verification_status')->nullable()->after('verification_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'room_no',
                'ticket_description',
                'user_status',
                'cat_supervisor_status',
                'build_supervisor_status',
                'verified_by',
                'ticket_closing_date',
            ]);
        });
    }
};
