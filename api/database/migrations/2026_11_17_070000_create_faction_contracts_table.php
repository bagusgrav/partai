<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faction_contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('politician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('politician_name');
            $table->string('title');
            $table->text('description');
            $table->enum('status', [
                'BELUM_DIMULAI',
                'SEDANG_BERJALAN',
                'TERPENUHI',
                'GAGAL',
            ])->default('BELUM_DIMULAI');
            $table->string('evidence_url')->nullable();
            $table->date('deadline')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faction_contracts');
    }
};
