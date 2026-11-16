<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attestations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('claim_id')->constrained('attendance_claims')->cascadeOnDelete();
            $table->foreignUuid('attester_id')->constrained('users')->cascadeOnDelete();
            $table->string('cluster_id');
            $table->enum('status', ['trusted', 'flagged'])->default('trusted');
            $table->uuid('request_id')->unique();
            $table->timestamps();
            $table->unique(['claim_id', 'attester_id']);
            $table->unique(['claim_id', 'cluster_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attestations');
    }
};
