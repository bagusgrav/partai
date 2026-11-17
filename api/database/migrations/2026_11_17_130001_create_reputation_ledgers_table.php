<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reputation_ledgers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('actor_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('sequence');
            $table->string('score_type');
            $table->string('domain')->nullable();
            $table->string('category')->nullable();
            $table->string('source_type');
            $table->string('source_id');
            $table->integer('delta');
            $table->text('reason');
            $table->uuid('request_id')->unique();
            $table->char('previous_hash', 64)->nullable();
            $table->char('hash', 64);
            $table->timestamps();
            $table->unique(['user_id', 'sequence']);
            $table->index(['user_id', 'score_type', 'domain']);
            $table->index(['score_type', 'category']);
        });

        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER reputation_ledgers_prevent_update
                BEFORE UPDATE ON reputation_ledgers
                BEGIN
                    SELECT RAISE(ABORT, 'reputation_ledgers is append-only');
                END;
            SQL);
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER reputation_ledgers_prevent_delete
                BEFORE DELETE ON reputation_ledgers
                BEGIN
                    SELECT RAISE(ABORT, 'reputation_ledgers is append-only');
                END;
            SQL);
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS reputation_ledgers_prevent_update');
            DB::unprepared('DROP TRIGGER IF EXISTS reputation_ledgers_prevent_delete');
        }

        Schema::dropIfExists('reputation_ledgers');
    }
};
