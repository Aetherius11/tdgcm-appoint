<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('hold_started_at')->nullable()->after('transaction_completed_at');
            $table->unsignedInteger('hold_duration_seconds')->default(0)->after('hold_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['hold_started_at', 'hold_duration_seconds']);
        });
    }
};
