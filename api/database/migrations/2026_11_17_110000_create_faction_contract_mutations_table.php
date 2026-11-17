<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faction_contract_mutations', function (Blueprint $table) {
            $table->uuid('request_id')->primary();
            $table->string('operation');
            $table->uuid('actor_id');
            $table->uuid('contract_id')->nullable()->index();
            $table->json('payload');
            $table->json('response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faction_contract_mutations');
    }
};
