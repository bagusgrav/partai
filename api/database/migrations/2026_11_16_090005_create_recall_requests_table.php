<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recall_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('target_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->string('evidence_url')->nullable();
            $table->enum('status', ['pending', 'voting', 'approved', 'rejected'])->default('pending');
            $table->uuid('request_id')->unique();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recall_requests');
    }
};
