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
        Schema::table('appointments', function (Blueprint $table) {
            // Add served_at timestamp to track when service started
            $table->timestamp('served_at')->nullable()->after('checked_in_at');
            
            // Add served_by_user_id to track which staff member served the appointment
            $table->foreignId('served_by_user_id')->nullable()->after('served_at')->constrained('users');
            
            // Add window_number to track which window the appointment is assigned to
            $table->integer('window_number')->nullable()->after('served_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['served_by_user_id']);
            $table->dropColumn(['served_at', 'served_by_user_id', 'window_number']);
        });
    }
};