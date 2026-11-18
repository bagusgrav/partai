<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nik']);
            $table->string('nik', 512)->nullable()->change();
            $table->char('nik_hash', 64)->nullable()->unique();
        });

        DB::table('users')
            ->whereNotNull('nik')
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    try {
                        $plainNik = Crypt::decryptString($user->nik);
                        $encryptedNik = $user->nik;
                    } catch (DecryptException) {
                        $plainNik = $user->nik;
                        $encryptedNik = Crypt::encryptString($plainNik);
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'nik' => $encryptedNik,
                            'nik_hash' => hash('sha256', $plainNik),
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('users')
            ->whereNotNull('nik')
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['nik' => Crypt::decryptString($user->nik)]);
                }
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nik_hash']);
            $table->dropColumn('nik_hash');
            $table->string('nik', 255)->nullable()->change();
            $table->unique('nik');
        });
    }
};
