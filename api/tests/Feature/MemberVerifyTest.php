<?php

namespace Tests\Feature;

use App\Filament\Resources\PartySettingResource;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\PartySetting;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MemberVerifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_settings_returns_database_branding_and_logo_url(): void
    {
        PartySetting::query()->create([
            'party_name' => 'Partai Dinamis',
            'logo_path' => 'party-logos/logo.png',
            'primary_color' => '#123456',
            'secondary_color' => '#abcdef',
            'tagline' => 'Tagline uji',
        ]);

        $this->getJson('/api/settings')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('party_name', 'Partai Dinamis')
            ->assertJsonPath('logo_path', route('api.settings.logo'))
            ->assertJsonPath('primary_color', '#123456')
            ->assertJsonPath('secondary_color', '#abcdef')
            ->assertJsonPath('tagline', 'Tagline uji');
    }

    public function test_public_logo_endpoint_streams_the_current_uploaded_logo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('party-logos/logo.png', 'image content');
        PartySetting::query()->create([
            'party_name' => 'Partai Dinamis',
            'logo_path' => 'party-logos/logo.png',
            'primary_color' => '#123456',
            'secondary_color' => '#abcdef',
            'tagline' => 'Tagline uji',
        ]);

        $this->get('/api/settings/logo')->assertOk();
    }

    public function test_public_member_verification_returns_only_public_member_fields(): void
    {
        $region = Region::query()->create([
            'id' => '327300',
            'name' => 'Kota Bandung',
            'level' => 2,
        ]);
        $member = User::factory()->create([
            'name' => 'Anggota Uji',
            'email' => 'private@example.test',
            'nik' => '3201010101010001',
            'nik_verified_at' => now(),
            'member_number' => 'PRT-2026-0001',
            'region_id' => $region->id,
            'cluster_id' => null,
        ]);

        $this->getJson('/api/members/verify/'.$member->member_number)
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertExactJson([
                'name' => 'Anggota Uji',
                'member_number' => 'PRT-2026-0001',
                'region_name' => 'Kota Bandung',
                'status' => 'verified',
            ])
            ->assertJsonMissingPath('nik')
            ->assertJsonMissingPath('email');
    }

    public function test_public_member_verification_hides_unverified_and_unknown_members(): void
    {
        User::factory()->create([
            'nik' => '3201010101010002',
            'member_number' => 'PRT-2026-0002',
        ]);

        $this->getJson('/api/members/verify/PRT-2026-0002')->assertNotFound();
        $this->getJson('/api/members/verify/PRT-2026-9999')->assertNotFound();
        $this->getJson('/api/members/verify/invalid')->assertNotFound();
    }

    public function test_public_member_verification_is_rate_limited_after_sixty_requests(): void
    {
        for ($attempt = 1; $attempt <= 60; $attempt++) {
            $this->getJson('/api/members/verify/PRT-2026-9999')
                ->assertNotFound();
        }

        $this->getJson('/api/members/verify/PRT-2026-9999')
            ->assertStatus(429);
    }

    public function test_admin_nik_verification_generates_a_sequential_member_number(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));
        $this->actingAs($admin);

        $firstMember = User::factory()->create(['nik' => '3201010101010003']);
        Livewire::test(EditUser::class, ['record' => $firstMember->getRouteKey()])
            ->fillForm(['nik_verified' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $secondMember = User::factory()->create(['nik' => '3201010101010004']);
        Livewire::test(EditUser::class, ['record' => $secondMember->getRouteKey()])
            ->fillForm(['nik_verified' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('PRT-'.now()->format('Y').'-0001', $firstMember->fresh()->member_number);
        $this->assertSame('PRT-'.now()->format('Y').'-0002', $secondMember->fresh()->member_number);
        $this->assertNotNull($firstMember->fresh()->nik_verified_at);
        $this->assertNotNull($secondMember->fresh()->nik_verified_at);
    }

    public function test_authenticated_user_profile_includes_its_member_number(): void
    {
        $member = User::factory()->create([
            'member_number' => 'PRT-'.now()->format('Y').'-0001',
        ]);
        Sanctum::actingAs($member);

        $this->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('member_number', $member->member_number);
    }

    public function test_party_settings_resource_is_limited_to_super_admin(): void
    {
        $pengurus = User::factory()->create();
        $pengurus->assignRole(Role::create(['name' => 'pengurus', 'guard_name' => 'web']));
        $this->actingAs($pengurus);

        $this->assertFalse(PartySettingResource::canViewAny());

        $admin = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));
        $this->actingAs($admin);

        $this->assertTrue(PartySettingResource::canViewAny());
        $this->assertTrue(PartySettingResource::canCreate());
    }
}
