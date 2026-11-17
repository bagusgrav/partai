<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('economic_indicator_observations', function (Blueprint $table) {
            $table->string('region_code', 4)->default('9999');
            $table->string('region_name')->default('Nasional (Indonesia)');
            $table->dropUnique('economic_indicator_observations_indicator_name_period_year_period_month_unique');
            $table->unique(
                ['indicator_name', 'period_year', 'period_month', 'region_code'],
                'economic_indicator_observations_region_period_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('economic_indicator_observations', function (Blueprint $table) {
            $table->dropUnique('economic_indicator_observations_region_period_unique');
            $table->dropColumn(['region_code', 'region_name']);
            $table->unique(['indicator_name', 'period_year', 'period_month']);
        });
    }
};
