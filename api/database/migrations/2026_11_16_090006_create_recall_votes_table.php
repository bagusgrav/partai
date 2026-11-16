<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recall_votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('recall_request_id')->constrained('recall_requests')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('vote');
            $table->uuid('request_id')->unique();
            $table->timestamps();
            $table->unique(['recall_request_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recall_votes');
    }
};
