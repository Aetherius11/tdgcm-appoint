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
        Schema::create('companies', function (Blueprint $table) {
            // id [pk, increment]
            $table->id();            
            // name varchar(100) [not null]
            $table->string('name', 100);            
            // branding_logo varchar(255) [null]
            $table->string('branding_logo', 255)->nullable();            
            // privacy_policy text [null]
            $table->text('privacy_policy')->nullable();            
            // is_active boolean [default: true]
            $table->boolean('is_active')->default(true);            
            // created_at & updated_at timestamps
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company');
    }
};
