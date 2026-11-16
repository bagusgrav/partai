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
        Schema::create('essay_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('item_id');
            $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            $table->text('answer_text');
            $table->enum('status', ['pending', 'reviewed'])->default('pending');
            $table->integer('score')->nullable();
            $table->foreignUuid('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('feedback')->nullable();
            $table->uuid('request_id')->unique();
            $table->uuid('review_request_id')->nullable()->unique();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('essay_submissions');
    }
};
