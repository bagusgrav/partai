<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['guest', 'member', 'admin'])->default('guest');
            $table->string('nik')->nullable()->unique();
            $table->boolean('is_verified')->default(false);
            $table->uuid('registration_request_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nik']);
            $table->dropUnique(['registration_request_id']);
            $table->dropColumn(['role', 'nik', 'is_verified', 'registration_request_id']);
        });
    }
};
