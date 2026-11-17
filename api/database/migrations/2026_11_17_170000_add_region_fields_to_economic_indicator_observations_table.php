<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = 'economic_indicator_observations';
        $hasRegionCode = Schema::hasColumn($tableName, 'region_code');
        $hasRegionName = Schema::hasColumn($tableName, 'region_name');
        $hasLegacyIndex = Schema::hasIndex(
            $tableName,
            'economic_indicator_observations_indicator_name_period_year_period_month_unique',
        );
        $hasRegionIndex = Schema::hasIndex($tableName, 'economic_indicator_observations_region_period_unique');

        Schema::table($tableName, function (Blueprint $table) use (
            $hasRegionCode,
            $hasRegionName,
            $hasLegacyIndex,
            $hasRegionIndex,
        ) {
            if (! $hasRegionCode) {
                $table->string('region_code', 4)->default('9999');
            }

            if (! $hasRegionName) {
                $table->string('region_name')->default('Nasional (Indonesia)');
            }

            if ($hasLegacyIndex) {
                $table->dropUnique('economic_indicator_observations_indicator_name_period_year_period_month_unique');
            }

            if (! $hasRegionIndex) {
                $table->unique(
                    ['indicator_name', 'period_year', 'period_month', 'region_code'],
                    'economic_indicator_observations_region_period_unique',
                );
            }
        });
    }

    public function down(): void
    {
        $tableName = 'economic_indicator_observations';
        $hasRegionIndex = Schema::hasIndex($tableName, 'economic_indicator_observations_region_period_unique');
        $hasLegacyIndex = Schema::hasIndex(
            $tableName,
            'economic_indicator_observations_indicator_name_period_year_period_month_unique',
        );
        $columnsToDrop = array_values(array_filter(
            ['region_code', 'region_name'],
            static fn (string $column): bool => Schema::hasColumn($tableName, $column),
        ));

        Schema::table($tableName, function (Blueprint $table) use (
            $hasRegionIndex,
            $hasLegacyIndex,
            $columnsToDrop,
        ) {
            if ($hasRegionIndex) {
                $table->dropUnique('economic_indicator_observations_region_period_unique');
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }

            if (! $hasLegacyIndex) {
                $table->unique(['indicator_name', 'period_year', 'period_month']);
            }
        });
    }
};
