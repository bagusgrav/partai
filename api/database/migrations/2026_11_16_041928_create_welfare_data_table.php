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
        Schema::create('welfare_data', function (Blueprint $table) {
            $table->id();
            $table->string('region_code');
            $table->string('region_name');
            $table->string('indicator');
            $table->float('value');
            $table->string('unit');
            $table->integer('year');
            $table->string('source');
            $table->timestamps();
            $table->index(['region_code', 'indicator', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welfare_data');
    }
};
