<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_login_and_receive_a_sanctum_token(): void
    {
        $user = User::factory()->create([
            'role' => 'member',
            'email' => 'login@example.test',
            'password' => 'password-anggota',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password-anggota',
        ])->assertOk()->assertJsonStructure(['token']);

        $this->withToken($response->json('token'))
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_guest_cannot_login_and_logout_revokes_the_current_token(): void
    {
        $guest = User::factory()->create(['role' => 'guest']);
        $this->postJson('/api/auth/login', [
            'email' => $guest->email,
            'password' => 'password',
        ])->assertUnprocessable();

        $member = User::factory()->create(['role' => 'member']);
        $token = $member->createToken('test-session');

        $this->withToken($token->plainTextToken)
            ->postJson('/api/auth/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }
}
