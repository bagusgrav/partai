<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(
                ['notifiable_type', 'notifiable_id', 'created_at'],
                'notifications_notifiable_created_at_idx',
            );
            $table->index(
                ['notifiable_type', 'notifiable_id', 'read_at'],
                'notifications_notifiable_read_at_idx',
            );
        });

        Schema::table('faction_contracts', function (Blueprint $table) {
            $table->index('created_at', 'faction_contracts_created_at_idx');
            $table->index(
                ['status', 'created_at'],
                'faction_contracts_status_created_at_idx',
            );
            $table->index('politician_id', 'faction_contracts_politician_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_notifiable_created_at_idx');
            $table->dropIndex('notifications_notifiable_read_at_idx');
        });

        Schema::table('faction_contracts', function (Blueprint $table) {
            $table->dropIndex('faction_contracts_created_at_idx');
            $table->dropIndex('faction_contracts_status_created_at_idx');
            $table->dropIndex('faction_contracts_politician_id_idx');
        });
    }
};
