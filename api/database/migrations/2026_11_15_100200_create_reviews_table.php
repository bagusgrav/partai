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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('item_id');
            $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            $table->boolean('correct');
            $table->integer('quality');
            $table->uuid('request_id')->unique();
            $table->timestamp('reviewed_at')->useCurrent();
            $table->timestamps();
            $table->index(['user_id', 'reviewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
