<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_agency_scorecards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('agency_code')->index();
            $table->string('agency_name');
            $table->string('agency_category')->index();
            $table->string('province_name')->nullable()->index();
            $table->string('region_code')->nullable()->index();
            $table->unsignedSmallInteger('period_year')->index();
            $table->decimal('rb_index_score', 5, 2);
            $table->string('sakip_grade', 5);
            $table->decimal('merit_system_score', 5, 2);
            $table->decimal('ombudsman_compliance_score', 5, 2);
            $table->unsignedInteger('resolved_complaints_count')->default(0);
            $table->unsignedInteger('open_complaints_count')->default(0);
            $table->decimal('bureaucracy_integrity_score', 5, 2)->index();
            $table->string('performance_tier')->index();
            $table->string('source_citation');
            $table->timestamps();
            $table->unique(['agency_code', 'period_year']);
        });

        Schema::create('bureaucracy_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ticket_code')->unique();
            $table->string('reporter_token_hash', 64);
            $table->uuid('request_id')->unique();
            $table->string('payload_hash', 64);
            $table->foreignUuid('agency_id')->nullable()->constrained('public_agency_scorecards')->nullOnDelete();
            $table->string('agency_name');
            $table->string('region_code')->nullable()->index();
            $table->string('province_name')->nullable()->index();
            $table->string('category')->index();
            $table->string('title', 180);
            $table->text('chronology');
            $table->date('incident_date');
            $table->string('asset_identifier')->nullable();
            $table->string('evidence_url')->nullable();
            $table->string('evidence_hash', 64)->nullable();
            $table->string('verification_status')->default('submitted')->index();
            $table->text('public_resolution_note')->nullable();
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bureaucracy_reports');
        Schema::dropIfExists('public_agency_scorecards');
    }
};
