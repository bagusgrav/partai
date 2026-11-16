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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
            $table->timestamps();
            $table->index(['status', 'starts_at', 'ends_at']);
        });

        Schema::create('vote_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vote_id')->constrained('votes')->cascadeOnDelete();
            $table->string('option_text');
            $table->integer('votes_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_options');
        Schema::dropIfExists('votes');
    }
};
