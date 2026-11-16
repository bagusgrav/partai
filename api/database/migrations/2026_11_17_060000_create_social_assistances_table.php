<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_assistances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nik', 16)->unique();
            $table->string('name');
            $table->enum('program', ['PKH', 'BPNT', 'PIP']);
            $table->enum('status', ['DITERIMA', 'TIDAK_DITERIMA', 'PROSES']);
            $table->string('period');
            $table->string('region_code');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_assistances');
    }
};
