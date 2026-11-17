<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_reputations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('mastery_scores')->default('{"hak_konstitusional":0,"alasan_bernegara":0,"keuangan_negara":0,"hukum":0,"politik_bernegara":0}');
            $table->unsignedTinyInteger('overall_mastery_level')->default(1);
            $table->unsignedBigInteger('participation_score')->default(0);
            $table->json('eligible_roles')->default('{"panel_audit":false,"panel_etik":false,"bakal_caleg":false}');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_reputations');
    }
};
