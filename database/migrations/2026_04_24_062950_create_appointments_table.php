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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id(); // This acts as your Appointment ID/Ticket Number
            $table->string('appoint_no', 50)->unique(); // Optional: if you want A-001 style
            $table->foreignId('applicant_id')->constrained();
            $table->foreignId('purpose_id')->constrained();
            $table->foreignId('time_slot_id')->constrained('time_availabilities');
            $table->date('appointment_date');
            $table->string('status', 50)->default('pending'); // pending, called, finished, etc.
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
