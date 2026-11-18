<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('party_finance_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('sequence')->unique();
            $table->uuid('request_id')->unique();
            $table->string('entry_type')->index();
            $table->string('channel')->index();
            $table->unsignedSmallInteger('fiscal_year')->index();
            $table->date('transaction_date')->index();
            $table->unsignedBigInteger('amount_idr');
            $table->string('donor_type')->index();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('contributor_display_name');
            $table->string('identity_hash')->nullable()->index();
            $table->string('masked_identity')->nullable();
            $table->boolean('legal_declaration_accepted')->default(false);
            $table->string('purpose_or_program', 220);
            $table->string('region_code')->nullable()->index();
            $table->string('province_name')->nullable()->index();
            $table->string('evidence_reference')->nullable();
            $table->string('compliance_status')->default('pending_audit')->index();
            $table->foreignUuid('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('audited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('audited_at')->nullable();
            $table->text('audit_note')->nullable();
            $table->foreignUuid('reversal_of_id')->nullable();
            $table->char('previous_hash', 64)->nullable();
            $table->char('hash', 64)->unique();
            $table->timestamps();

            $table->foreign('reversal_of_id')
                ->references('id')
                ->on('party_finance_entries')
                ->nullOnDelete();
        });

        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER party_finance_entries_prevent_delete
                BEFORE DELETE ON party_finance_entries
                BEGIN
                    SELECT RAISE(ABORT, 'party_finance_entries is append-only');
                END;
            SQL);
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER party_finance_entries_prevent_financial_update
                BEFORE UPDATE OF amount_idr, sequence, previous_hash, hash ON party_finance_entries
                BEGIN
                    SELECT RAISE(ABORT, 'party_finance_entries financial fields are immutable');
                END;
            SQL);
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS party_finance_entries_prevent_delete');
            DB::unprepared('DROP TRIGGER IF EXISTS party_finance_entries_prevent_financial_update');
        }

        Schema::dropIfExists('party_finance_entries');
    }
};
