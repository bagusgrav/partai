<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->string('visibility')->default('members');
            $table->string('status')->default('published');
            $table->string('request_id')->unique();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('edited_at')->nullable();
            $table->softDeletes();
            $table->index(['visibility', 'status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('post_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason');
            $table->string('status')->default('open');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['post_id', 'status']);
            $table->index('reporter_id');
        });

        Schema::create('post_moderation_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('actor_id')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['post_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_moderation_logs');
        Schema::dropIfExists('post_reports');
        Schema::dropIfExists('posts');
    }
};
