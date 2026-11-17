<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('region_code', 4)->nullable()->index();
            $table->string('province_name')->nullable()->index();
        });

        Schema::table('faction_contracts', function (Blueprint $table) {
            $table->string('region_code', 4)->nullable()->index();
            $table->string('province_name')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('faction_contracts', function (Blueprint $table) {
            $table->dropIndex(['region_code']);
            $table->dropIndex(['province_name']);
            $table->dropColumn(['region_code', 'province_name']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['region_code']);
            $table->dropIndex(['province_name']);
            $table->dropColumn(['region_code', 'province_name']);
        });
    }
};
