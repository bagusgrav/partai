<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('civic_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('domain')->index();
            $table->unsignedTinyInteger('mastery_level');
            $table->text('prompt');
            $table->json('options');
            $table->string('correct_option', 1);
            $table->text('explanation');
            $table->string('source_citation');
            $table->string('source_url')->nullable();
            $table->string('review_status')->default('published');
            $table->unsignedTinyInteger('mastery_points')->default(5);
            $table->timestamps();
            $table->index(['review_status', 'domain', 'mastery_level']);
        });

        Schema::create('user_question_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('civic_question_id')->constrained('civic_questions')->cascadeOnDelete();
            $table->unsignedInteger('repetitions')->default(0);
            $table->unsignedInteger('interval_days')->default(0);
            $table->decimal('ease_factor', 4, 2)->default(2.50);
            $table->boolean('mastery_awarded')->default(false);
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamp('next_review_at')->index();
            $table->timestamps();
            $table->unique(['user_id', 'civic_question_id']);
        });

        Schema::create('civic_answer_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('request_id')->unique();
            $table->char('payload_hash', 64);
            $table->json('response');
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('civic_answer_requests');
        Schema::dropIfExists('user_question_schedules');
        Schema::dropIfExists('civic_questions');
    }
};
