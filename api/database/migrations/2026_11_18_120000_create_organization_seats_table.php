<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_seats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('seat_code')->unique();
            $table->foreignUuid('parent_id')->nullable()->constrained('organization_seats')->nullOnDelete();
            $table->string('layer')->index();
            $table->string('title');
            $table->string('territory_level')->index();
            $table->string('region_code')->nullable()->index();
            $table->string('province_name')->nullable()->index();
            $table->text('answering_scope');
            $table->string('selection_method');
            $table->unsignedTinyInteger('required_mastery_level')->default(3);
            $table->string('required_domain')->nullable();
            $table->unsignedInteger('required_min_participation')->default(0);
            $table->unsignedSmallInteger('term_duration_months')->default(24);
            $table->unsignedTinyInteger('max_consecutive_terms')->default(2);
            $table->foreignUuid('holder_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('holder_display_name')->nullable();
            $table->date('term_starts_at')->nullable();
            $table->date('term_ends_at')->nullable();
            $table->string('status')->default('open')->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('seat_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('seat_id')->constrained('organization_seats')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('request_id')->unique();
            $table->text('statement');
            $table->unsignedTinyInteger('mastery_level_snapshot');
            $table->unsignedInteger('participation_score_snapshot');
            $table->string('status')->default('in_pool')->index();
            $table->timestamps();
            $table->unique(['seat_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seat_applications');
        Schema::dropIfExists('organization_seats');
    }
};
