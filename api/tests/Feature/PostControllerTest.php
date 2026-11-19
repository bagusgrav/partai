<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_feed_only_returns_published_public_posts(): void
    {
        $author = User::factory()->create();
        $public = $this->makePost($author, 'Teks publik', 'public');
        $this->makePost($author, 'Teks anggota', 'members');
        $this->makePost($author, 'Teks tersembunyi', 'public', 'hidden');

        $this->getJson('/api/posts')
            ->assertOk()
            ->assertJsonPath('data.0.id', $public->id)
            ->assertJsonMissingPath('data.0.status')
            ->assertJsonMissing(['body' => 'Teks anggota'])
            ->assertJsonMissing(['body' => 'Teks tersembunyi'])
            ->assertHeader('Cache-Control', 'max-age=30, public, s-maxage=30');

        $this->actingAs(User::factory()->create(['role' => 'member']), 'sanctum')
            ->getJson('/api/posts')
            ->assertOk()
            ->assertJsonMissing(['body' => 'Teks anggota']);
    }

    public function test_guest_cannot_access_member_feed(): void
    {
        $this->getJson('/api/posts/members')->assertUnauthorized();

        $guest = User::factory()->create(['role' => 'guest']);
        $this->actingAs($guest, 'sanctum')
            ->getJson('/api/posts/members')
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_read_member_posts_without_cache(): void
    {
        $author = User::factory()->create();
        $post = $this->makePost($author, 'Catatan anggota', 'members');
        $this->makePost($author, 'Catatan publik', 'public');
        $this->actingAs(User::factory()->create(['role' => 'member']), 'sanctum');

        $this->getJson('/api/posts/members')
            ->assertOk()
            ->assertJsonPath('data.0.id', $post->id)
            ->assertJsonMissing(['body' => 'Catatan publik'])
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_post_creation_requires_request_id_and_defaults_to_members(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'member']), 'sanctum');

        $this->postJson('/api/posts', ['body' => 'Teks singkat'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['request_id']);

        $this->postJson('/api/posts', [
            'request_id' => (string) Str::uuid(),
            'body' => 'Teks anggota',
        ])->assertCreated()
            ->assertJsonPath('data.visibility', 'members')
            ->assertJsonMissingPath('data.status')
            ->assertJsonMissingPath('data.request_id');
    }

    public function test_guest_cannot_create_a_post_even_with_a_sanctum_token(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'guest']), 'sanctum')
            ->postJson('/api/posts', [
                'request_id' => (string) Str::uuid(),
                'body' => 'Post guest',
                'visibility' => 'public',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_post_creation_replays_the_same_id_without_duplicate(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'member']), 'sanctum');
        $payload = [
            'request_id' => (string) Str::uuid(),
            'body' => 'Post yang sama',
            'visibility' => 'public',
        ];

        $this->postJson('/api/posts', $payload)->assertCreated();
        $this->postJson('/api/posts', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed')
            ->assertJsonPath('data.body', 'Post yang sama');

        $this->assertDatabaseCount('posts', 1);
    }

    public function test_reusing_request_id_with_a_different_payload_or_owner_conflicts(): void
    {
        $owner = User::factory()->create(['role' => 'member']);
        $other = User::factory()->create(['role' => 'member']);
        $requestId = (string) Str::uuid();
        $payload = ['request_id' => $requestId, 'body' => 'Post pertama'];

        $this->actingAs($owner, 'sanctum')->postJson('/api/posts', $payload)->assertCreated();
        $this->postJson('/api/posts', [...$payload, 'body' => 'Post lain'])->assertConflict();
        $this->actingAs($other, 'sanctum')->postJson('/api/posts', $payload)->assertConflict();

        $this->assertDatabaseCount('posts', 1);
    }

    public function test_only_owner_can_update_or_delete_a_post(): void
    {
        $owner = User::factory()->create(['role' => 'member']);
        $other = User::factory()->create(['role' => 'member']);
        $post = $this->makePost($owner, 'Teks awal');

        $this->actingAs($other, 'sanctum')
            ->patchJson("/api/posts/{$post->id}", ['body' => 'Teks bukan milik'])
            ->assertForbidden();
        $this->deleteJson("/api/posts/{$post->id}")->assertForbidden();

        $this->actingAs($owner, 'sanctum')
            ->patchJson("/api/posts/{$post->id}", ['body' => 'Teks diperbarui'])
            ->assertOk()
            ->assertJsonPath('data.body', 'Teks diperbarui');
        $this->deleteJson("/api/posts/{$post->id}")->assertOk();

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'status' => 'deleted']);
        $this->getJson('/api/posts')
            ->assertOk()
            ->assertJsonMissing(['id' => $post->id]);
    }

    public function test_user_can_report_a_post(): void
    {
        $post = $this->makePost(User::factory()->create(), 'Post untuk laporan');
        $this->actingAs(User::factory()->create(['role' => 'member']), 'sanctum')
            ->postJson("/api/posts/{$post->id}/report", ['reason' => 'Perlu ditinjau'])
            ->assertCreated()
            ->assertJsonPath('status', 'open');

        $this->assertDatabaseHas('post_reports', [
            'post_id' => $post->id,
            'reason' => 'Perlu ditinjau',
            'status' => 'open',
        ]);
    }

    public function test_post_creation_is_limited_to_ten_requests_per_minute(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'member']), 'sanctum');

        for ($index = 0; $index < 10; $index++) {
            $this->postJson('/api/posts', [
                'request_id' => (string) Str::uuid(),
                'body' => 'Post rate limit '.$index,
            ])->assertCreated();
        }

        $this->postJson('/api/posts', [
            'request_id' => (string) Str::uuid(),
            'body' => 'Post melebihi batas',
        ])->assertTooManyRequests();
    }

    public function test_post_updates_are_limited_to_ten_requests_per_minute(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        $post = $this->makePost($user, 'Post untuk pembaruan');
        $this->actingAs($user, 'sanctum');

        for ($index = 0; $index < 10; $index++) {
            $this->patchJson("/api/posts/{$post->id}", [
                'body' => 'Pembaruan '.$index,
            ])->assertOk();
        }

        $this->patchJson("/api/posts/{$post->id}", ['body' => 'Pembaruan dibatasi'])
            ->assertTooManyRequests();
    }

    public function test_post_deletions_are_limited_to_ten_requests_per_minute(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        $posts = collect(range(1, 11))
            ->map(fn (int $index): Post => $this->makePost($user, 'Post hapus '.$index));
        $this->actingAs($user, 'sanctum');

        foreach ($posts->take(10) as $post) {
            $this->deleteJson("/api/posts/{$post->id}")->assertOk();
        }

        $this->deleteJson('/api/posts/'.$posts->last()->id)->assertTooManyRequests();
    }

    public function test_post_reports_are_limited_to_five_requests_per_minute(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        $post = $this->makePost(User::factory()->create(), 'Post untuk dilaporkan');
        $this->actingAs($user, 'sanctum');

        for ($index = 0; $index < 5; $index++) {
            $this->postJson("/api/posts/{$post->id}/report", [
                'reason' => 'Alasan laporan '.$index,
            ])->assertCreated();
        }

        $this->postJson("/api/posts/{$post->id}/report", [
            'reason' => 'Laporan dibatasi',
        ])->assertTooManyRequests();
    }

    private function makePost(
        User $user,
        string $body,
        string $visibility = 'public',
        string $status = 'published',
    ): Post {
        return Post::query()->create([
            'user_id' => $user->id,
            'body' => $body,
            'visibility' => PostVisibility::from($visibility),
            'status' => PostStatus::from($status),
            'request_id' => (string) Str::uuid(),
        ]);
    }
}
