<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('economic_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('indicator_type');
            $table->string('title');
            $table->decimal('value', 16, 4);
            $table->string('unit');
            $table->string('period');
            $table->string('source');
            $table->string('trend')->nullable();
            $table->timestamps();
            $table->unique(['indicator_type', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('economic_indicators');
    }
};
