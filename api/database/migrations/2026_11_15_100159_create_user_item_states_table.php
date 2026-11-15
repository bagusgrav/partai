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
        Schema::create('user_item_states', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('item_id');
            $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            $table->integer('reps')->default(0);
            $table->integer('interval_d')->default(0);
            $table->float('ease')->default(2.5);
            $table->timestamp('next_review')->useCurrent();
            $table->timestamp('last_review')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'item_id']);
            $table->index(['user_id', 'next_review'], 'idx_user_due');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_item_states');
    }
};
