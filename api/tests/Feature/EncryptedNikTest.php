<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EncryptedNikTest extends TestCase
{
    use RefreshDatabase;

    public function test_nik_is_encrypted_at_rest_and_indexed_by_a_hash(): void
    {
        $nik = '3201010101010001';
        $user = User::factory()->create([
            'role' => 'member',
            'nik' => $nik,
        ]);
        $storedNik = DB::table('users')->where('id', $user->id)->value('nik');

        $this->assertNotSame($nik, $storedNik);
        $this->assertSame($nik, $user->fresh()->nik);
        $this->assertSame(
            hash('sha256', $nik),
            DB::table('users')->where('id', $user->id)->value('nik_hash'),
        );
    }
}
