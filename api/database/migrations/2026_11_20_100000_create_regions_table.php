<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->string('id', 10)->primary();
            $table->string('parent_id', 10)->nullable();
            $table->string('name');
            $table->unsignedTinyInteger('level');
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('regions')->cascadeOnDelete();
            $table->index(['parent_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
