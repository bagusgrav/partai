<?php

namespace Tests\Feature;

use App\Filament\Resources\NotificationResource;
use App\Models\GovernmentInsight;
use App\Models\User;
use App\Models\WelfareData;
use App\Notifications\DailyDigestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DailyDigestTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_a_digest_with_an_unanswered_quiz_to_each_member(): void
    {
        Mail::fake();
        $member = User::factory()->create(['role' => 'member']);
        $guest = User::factory()->create([
            'email' => 'guest_'.Str::uuid().'@partai.id',
            'role' => 'guest',
        ]);
        $this->createItem('ANSWERED-QUIZ', ['question' => 'Sudah dijawab?']);
        $this->createItem('UNANSWERED-QUIZ', ['question' => 'Soal hari ini?']);
        $this->createItem('ESSAY', ['type' => 'essay', 'question' => 'Bukan kuis']);
        DB::table('reviews')->insert([
            'user_id' => $member->id,
            'item_id' => 'ANSWERED-QUIZ',
            'correct' => true,
            'quality' => 4,
            'request_id' => (string) Str::uuid(),
            'reviewed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        GovernmentInsight::query()->create([
            'category' => 'berita',
            'title' => 'Berita terbaru',
            'content' => 'Ringkasan berita terbaru.',
            'published_at' => now(),
        ]);
        WelfareData::query()->create([
            'region_code' => '31',
            'region_name' => 'Jakarta',
            'indicator' => 'Kemiskinan',
            'value' => 4.2,
            'unit' => 'persen',
            'year' => 2025,
            'source' => 'BPS',
        ]);

        $this->artisan('send:daily-digest')->assertSuccessful();

        $this->assertDatabaseCount('notifications', 1);
        $notification = $member->notifications()->firstOrFail();
        $this->assertSame('UNANSWERED-QUIZ', $notification->data['quiz']['id']);
        $this->assertSame('Berita terbaru', $notification->data['news']['title']);
        $this->assertSame('Jakarta', $notification->data['welfare']['region_name']);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $guest->id]);
    }

    public function test_command_falls_back_to_a_quiz_the_member_has_already_answered(): void
    {
        Mail::fake();
        $member = User::factory()->create(['role' => 'member']);
        $this->createItem('ANSWERED-QUIZ', ['question' => 'Soal pengulangan?']);
        DB::table('reviews')->insert([
            'user_id' => $member->id,
            'item_id' => 'ANSWERED-QUIZ',
            'correct' => false,
            'quality' => 1,
            'request_id' => (string) Str::uuid(),
            'reviewed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createDigestSources();

        $this->artisan('send:daily-digest')->assertSuccessful();

        $this->assertSame(
            'ANSWERED-QUIZ',
            $member->notifications()->firstOrFail()->data['quiz']['id'],
        );
    }

    public function test_notifications_api_lists_only_the_current_users_notifications_and_marks_one_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $digest = $this->digestData();
        $user->notify(new DailyDigestNotification($digest));
        $otherUser->notify(new DailyDigestNotification($digest));
        Sanctum::actingAs($user);

        $this->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('notifications.0.data.type', 'daily_digest');
        $notificationId = $user->notifications()->firstOrFail()->id;

        $this->postJson("/api/notifications/{$notificationId}/read", [
            'request_id' => (string) Str::uuid(),
        ])
            ->assertOk()
            ->assertJsonPath('status', 'read');

        $this->assertNotNull($user->notifications()->firstOrFail()->read_at);
        $this->assertNull($otherUser->notifications()->firstOrFail()->read_at);
        $this->getJson('/api/notifications')->assertJsonPath('unread_count', 0);
    }

    public function test_notifications_api_marks_all_current_user_notifications_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $digest = $this->digestData();
        $user->notify(new DailyDigestNotification($digest));
        $user->notify(new DailyDigestNotification($digest));
        $otherUser->notify(new DailyDigestNotification($digest));
        Sanctum::actingAs($user);

        $this->postJson('/api/notifications/read', [
            'request_id' => (string) Str::uuid(),
        ])
            ->assertOk()
            ->assertJsonPath('updated_count', 2)
            ->assertJsonPath('unread_count', 0);

        $this->assertSame(0, $user->notifications()->whereNull('read_at')->count());
        $this->assertSame(1, $otherUser->notifications()->whereNull('read_at')->count());
    }

    public function test_notifications_api_requires_authentication_and_cannot_mark_another_users_notification_read(): void
    {
        $this->getJson('/api/notifications')->assertUnauthorized();
        $this->postJson('/api/notifications/not-a-user-notification/read')->assertUnauthorized();

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $owner->notify(new DailyDigestNotification($this->digestData()));
        $notificationId = $owner->notifications()->firstOrFail()->id;
        Sanctum::actingAs($otherUser);

        $this->postJson("/api/notifications/{$notificationId}/read", [
            'request_id' => (string) Str::uuid(),
        ])->assertNotFound();
        $this->assertNull($owner->notifications()->firstOrFail()->read_at);
    }

    public function test_notification_resource_is_read_only_and_limited_to_super_admins(): void
    {
        $notification = new DatabaseNotification;
        $this->assertFalse(NotificationResource::canViewAny());

        $pengurus = User::factory()->create();
        $pengurus->assignRole(Role::create(['name' => 'pengurus', 'guard_name' => 'web']));
        $this->actingAs($pengurus);
        $this->assertFalse(NotificationResource::canViewAny());

        $admin = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));
        $this->actingAs($admin);

        $this->assertTrue(NotificationResource::canViewAny());
        $this->assertFalse(NotificationResource::canCreate());
        $this->assertFalse(NotificationResource::canEdit($notification));
        $this->assertFalse(NotificationResource::canDelete($notification));
        $this->assertFalse(NotificationResource::canDeleteAny());
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function createItem(string $id, array $payload): void
    {
        DB::table('items')->insert([
            'id' => $id,
            'domain' => 'hukum',
            'level' => 'pahami',
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createDigestSources(): void
    {
        GovernmentInsight::query()->create([
            'category' => 'ANTARA',
            'title' => 'Berita',
            'content' => 'Konten berita.',
        ]);
        WelfareData::query()->create([
            'region_code' => '31',
            'region_name' => 'Jakarta',
            'indicator' => 'Kemiskinan',
            'value' => 4.2,
            'unit' => 'persen',
            'year' => 2025,
            'source' => 'BPS',
        ]);
    }

    /**
     * @return array{
     *     quiz: array{id: string, question: string, domain: string, level: string},
     *     news: array{title: string, content: string, source_url: ?string, published_at: ?string},
     *     welfare: array{region_name: string, indicator: string, value: float, unit: string, year: int, source: string}
     * }
     */
    private function digestData(): array
    {
        return [
            'quiz' => ['id' => 'QUIZ', 'question' => 'Soal?', 'domain' => 'hukum', 'level' => 'pahami'],
            'news' => ['title' => 'Berita', 'content' => 'Konten.', 'source_url' => null, 'published_at' => null],
            'welfare' => [
                'region_name' => 'Jakarta',
                'indicator' => 'Kemiskinan',
                'value' => 4.2,
                'unit' => 'persen',
                'year' => 2025,
                'source' => 'BPS',
            ],
        ];
    }
}
