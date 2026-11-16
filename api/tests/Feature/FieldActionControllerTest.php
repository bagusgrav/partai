<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FieldActionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_coordinator_can_create_an_event_idempotently_and_members_see_only_active_events(): void
    {
        $coordinator = User::factory()->create();
        $activeEvent = $this->eventData($coordinator);
        $endedEvent = $this->eventData($coordinator, [
            'ends_at' => now()->subMinute(),
        ]);
        DB::table('events')->insert([$activeEvent, $endedEvent]);
        Sanctum::actingAs($coordinator);

        $payload = [
            'title' => 'Aksi warga',
            'description' => 'Kegiatan lapangan',
            'location_lat' => -6.2,
            'location_lng' => 106.8,
            'radius_m' => 150,
            'starts_at' => now()->addHour()->toISOString(),
            'ends_at' => now()->addHours(2)->toISOString(),
            'request_id' => '2b45c244-719f-4dfb-9b6c-81ea39bd1301',
        ];

        $this->postJson('/api/events', $payload)
            ->assertCreated()
            ->assertJsonPath('status', 'processed');
        $this->postJson('/api/events', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed');

        $this->getJson('/api/events/active')
            ->assertOk()
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.title', 'Aksi');
        $this->assertDatabaseCount('events', 3);
    }

    public function test_only_the_event_coordinator_can_read_its_rotating_qr_token(): void
    {
        $coordinator = User::factory()->create();
        $member = User::factory()->create();
        $eventId = $this->createEvent($coordinator);
        Sanctum::actingAs($member);

        $this->getJson("/api/events/{$eventId}/qr")->assertForbidden();

        Sanctum::actingAs($coordinator);
        $response = $this->getJson("/api/events/{$eventId}/qr")->assertOk();
        $this->assertSame(json_encode($this->eventToken($eventId)), $response->getContent());
    }

    public function test_claims_validate_token_geofence_and_request_idempotency(): void
    {
        $coordinator = User::factory()->create();
        $member = User::factory()->create();
        $eventId = $this->createEvent($coordinator);
        Sanctum::actingAs($member);
        $payload = [
            'event_id' => $eventId,
            'token' => $this->eventToken($eventId),
            'lat' => 0,
            'lng' => 0,
            'request_id' => 'a27fdc51-8b86-49b0-a185-42cab168a876',
        ];

        $this->postJson('/api/attendance/claim', [...$payload, 'token' => 'invalid'])
            ->assertUnprocessable();
        $rejectedPayload = [
            ...$payload,
            'lat' => 1,
            'request_id' => 'b82feb2e-6b98-49a7-b607-70d59cb9f248',
        ];
        $this->postJson('/api/attendance/claim', $rejectedPayload)
            ->assertCreated()
            ->assertJsonPath('status', 'rejected');
        $this->postJson('/api/attendance/claim', $rejectedPayload)
            ->assertOk()
            ->assertJsonPath('status', 'rejected');
        $this->assertDatabaseHas('attendance_claims', [
            'user_id' => $member->id,
            'status' => 'rejected',
        ]);

        $this->postJson('/api/attendance/claim', $payload)
            ->assertCreated()
            ->assertJsonPath('status', 'processed');
        $this->postJson('/api/attendance/claim', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed');
        $this->assertDatabaseCount('attendance_claims', 2);
        $this->assertDatabaseHas('attendance_claims', [
            'event_id' => $eventId,
            'user_id' => $member->id,
            'distance_m' => 0,
            'status' => 'pending',
        ]);
    }

    public function test_attestations_require_distinct_clusters_and_two_validators_to_validate_a_claim(): void
    {
        $coordinator = User::factory()->create();
        $claimant = User::factory()->create();
        $firstValidator = User::factory()->create();
        $secondValidator = User::factory()->create();
        $sameClusterValidator = User::factory()->create();
        $this->setCluster($claimant, 'cluster-a');
        $this->setCluster($firstValidator, 'cluster-b');
        $this->setCluster($secondValidator, 'cluster-c');
        $this->setCluster($sameClusterValidator, 'cluster-b');
        $eventId = $this->createEvent($coordinator);
        $claimId = $this->createClaim($eventId, $claimant);

        Sanctum::actingAs($firstValidator);
        $firstAttestation = [
            'claim_id' => $claimId,
            'request_id' => '60400c05-0c55-43a8-88c3-f5fead768e57',
        ];
        $this->postJson('/api/attendance/attest', $firstAttestation)
            ->assertCreated()
            ->assertJsonPath('claim_status', 'pending');

        Sanctum::actingAs($sameClusterValidator);
        $this->postJson('/api/attendance/attest', [
            'claim_id' => $claimId,
            'request_id' => 'ec03a535-332a-4a46-8c96-602dd93bfc42',
        ])->assertForbidden();

        Sanctum::actingAs($secondValidator);
        $this->postJson('/api/attendance/attest', [
            'claim_id' => $claimId,
            'request_id' => '723721b4-9c2c-4c62-8353-5ffdb3a4719c',
        ])
            ->assertCreated()
            ->assertJsonPath('claim_status', 'valid');

        $this->assertDatabaseCount('attestations', 2);
        $this->assertDatabaseHas('attestations', [
            'claim_id' => $claimId,
            'attester_id' => $firstValidator->id,
            'cluster_id' => 'cluster-b',
        ]);
        $this->assertDatabaseHas('attendance_claims', [
            'id' => $claimId,
            'status' => 'valid',
        ]);
    }

    public function test_attendance_routes_require_authentication(): void
    {
        $this->getJson('/api/events/active')->assertUnauthorized();
        $this->postJson('/api/events')->assertUnauthorized();
        $this->postJson('/api/attendance/claim')->assertUnauthorized();
        $this->getJson('/api/attendance/pending?event_id='.Str::uuid())->assertUnauthorized();
        $this->postJson('/api/attendance/attest')->assertUnauthorized();
    }

    /**
     * @return array<string, mixed>
     */
    private function eventData(User $coordinator, array $overrides = []): array
    {
        return [
            'id' => (string) Str::uuid(),
            'title' => 'Aksi',
            'description' => 'Kegiatan lapangan',
            'location_lat' => 0,
            'location_lng' => 0,
            'radius_m' => 100,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'coordinator_id' => $coordinator->id,
            'status' => 'active',
            'request_id' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
            ...$overrides,
        ];
    }

    private function createEvent(User $coordinator): string
    {
        $event = $this->eventData($coordinator);
        DB::table('events')->insert($event);

        return $event['id'];
    }

    private function createClaim(string $eventId, User $claimant): string
    {
        $claimId = (string) Str::uuid();
        DB::table('attendance_claims')->insert([
            'id' => $claimId,
            'event_id' => $eventId,
            'user_id' => $claimant->id,
            'lat' => 0,
            'lng' => 0,
            'distance_m' => 0,
            'status' => 'pending',
            'request_id' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $claimId;
    }

    private function eventToken(string $eventId): string
    {
        $period = intdiv(now()->timestamp, 90);

        return hash_hmac('sha256', "{$eventId}:{$period}", config('app.key'));
    }

    private function setCluster(User $user, string $clusterId): void
    {
        DB::table('users')->where('id', $user->id)->update(['cluster_id' => $clusterId]);
    }
}
