<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('region_welfare_indices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('region_code', 4);
            $table->string('region_name');
            $table->string('province_name');
            $table->unsignedSmallInteger('period_year');
            $table->decimal('poverty_rate', 5, 2);
            $table->decimal('gini_ratio', 4, 3);
            $table->decimal('open_unemployment_rate', 5, 2);
            $table->decimal('stunting_rate', 5, 2);
            $table->unsignedInteger('disaster_events_count')->default(0);
            $table->unsignedBigInteger('affected_population')->default(0);
            $table->decimal('disaster_risk_score', 5, 2);
            $table->decimal('ikk_score', 5, 2)->index();
            $table->string('vulnerability_tier')->index();
            $table->string('bps_source_citation');
            $table->string('bnpb_source_citation');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['region_code', 'period_year']);
            $table->index('period_year');
            $table->index(['province_name', 'period_year']);
        });

        Schema::create('welfare_ikk_ingest_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('payload_hash', 64);
            $table->json('response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('welfare_ikk_ingest_requests');
        Schema::dropIfExists('region_welfare_indices');
    }
};
