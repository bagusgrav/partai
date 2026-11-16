<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->integer('distance_m');
            $table->enum('status', ['pending', 'valid', 'rejected'])->default('pending');
            $table->uuid('request_id')->unique();
            $table->timestamps();
            $table->index(['event_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_claims');
    }
};
