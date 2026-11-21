<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('party_settings', function (Blueprint $table) {
            $table->id();
            $table->string('party_name');
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7);
            $table->string('secondary_color', 7);
            $table->string('tagline');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_settings');
    }
};
