<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_window_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id');
            $table->integer('window_id');
            $table->string('window_name')->nullable();
            $table->string('appoint_no')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('end_type')->nullable(); // 'completed' or 'transferred'
            $table->timestamps();

            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            $table->index(['window_id', 'called_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_window_logs');
    }
};
