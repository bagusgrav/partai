<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GuestSessionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_request_creates_a_user_and_returns_a_valid_token(): void
    {
        $requestId = 'c95aa24c-a7e5-4aae-b865-2cb2b5951fd5';

        $response = $this->postJson('/api/auth/guest', [
            'request_id' => $requestId,
        ]);

        $response->assertCreated()->assertJsonStructure(['token']);
        $this->assertDatabaseCount('users', 1);

        $user = User::query()->where('guest_request_id', $requestId)->firstOrFail();
        $this->assertSame('Tamu', $user->name);
        $this->assertSame('guest', $user->role);
        $this->assertFalse($user->is_verified);
        $this->assertMatchesRegularExpression(
            '/^guest_[0-9a-f-]{36}@partai\.id$/',
            $user->email,
        );

        $this->withToken($response->json('token'))
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_reusing_request_id_reuses_the_guest_user(): void
    {
        $requestId = '29a27c38-04f7-4402-9b5b-9527118b0787';

        $this->postJson('/api/auth/guest', ['request_id' => $requestId])
            ->assertCreated();
        $this->postJson('/api/auth/guest', ['request_id' => $requestId])
            ->assertCreated();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_different_request_ids_create_independent_guest_users(): void
    {
        $firstRequestId = '7c914d41-7651-4f2c-9c52-ef9c54a37cba';
        $secondRequestId = 'dad6b965-bbc8-4efb-972f-96c8fd0a87e9';

        $firstResponse = $this->postJson('/api/auth/guest', [
            'request_id' => $firstRequestId,
        ])->assertCreated();
        $secondResponse = $this->postJson('/api/auth/guest', [
            'request_id' => $secondRequestId,
        ])->assertCreated();

        $firstUser = User::query()->where('guest_request_id', $firstRequestId)->firstOrFail();
        $secondUser = User::query()->where('guest_request_id', $secondRequestId)->firstOrFail();

        $this->assertNotSame($firstUser->id, $secondUser->id);
        $this->assertNotSame(
            $firstResponse->json('token'),
            $secondResponse->json('token'),
        );
        $this->assertDatabaseCount('users', 2);
    }

    public function test_missing_request_id_returns_a_validation_error(): void
    {
        $this->postJson('/api/auth/guest')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['request_id']);
    }
}
