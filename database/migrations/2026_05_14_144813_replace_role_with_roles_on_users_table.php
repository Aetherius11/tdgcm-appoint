<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('roles')->nullable()->after('email');
        });

        // Migrate existing single-role value into the new array column
        DB::table('users')->get()->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'roles' => json_encode([$user->role ?? 'staff']),
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('staff')->after('email');
        });

        DB::table('users')->get()->each(function ($user) {
            $roles = json_decode($user->roles ?? '["staff"]', true);
            DB::table('users')->where('id', $user->id)->update([
                'role' => $roles[0] ?? 'staff',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('roles');
        });
    }
};
