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
        Schema::create('time_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purpose_id')->constrained()->cascadeOnDelete();
            $table->integer('day_of_week'); // 1 (Mon) to 7 (Sun)
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('max_slots')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_availabilities');
    }
};
