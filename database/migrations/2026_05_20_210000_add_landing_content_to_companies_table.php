<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('landing_headline')->nullable()->after('branding_logo');
            $table->string('landing_badge_image')->nullable()->after('landing_headline');
            $table->longText('landing_body')->nullable()->after('landing_badge_image');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['landing_headline', 'landing_badge_image', 'landing_body']);
        });
    }
};
