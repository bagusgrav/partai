<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description');
            $table->decimal('location_lat', 10, 8);
            $table->decimal('location_lng', 11, 8);
            $table->integer('radius_m')->default(100);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->foreignUuid('coordinator_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->uuid('request_id')->unique();
            $table->timestamps();
            $table->index(['status', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
