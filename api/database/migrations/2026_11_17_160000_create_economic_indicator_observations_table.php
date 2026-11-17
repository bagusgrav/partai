<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('economic_indicator_observations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('indicator_name');
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month')->nullable();
            $table->decimal('value', 16, 4);
            $table->string('unit');
            $table->string('source');
            $table->timestamps();
            $table->unique(['indicator_name', 'period_year', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('economic_indicator_observations');
    }
};
