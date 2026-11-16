<?php

namespace Tests\Feature;

use App\Filament\Resources\ReputationLogResource;
use App\Models\ReputationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReputationLogResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reputation_logs_are_read_only_and_visible_only_to_super_admins(): void
    {
        $log = new ReputationLog;
        $this->assertFalse(ReputationLogResource::canViewAny());

        $member = User::factory()->create();
        $member->assignRole(Role::create(['name' => 'pengurus', 'guard_name' => 'web']));
        $this->actingAs($member);

        $this->assertFalse(ReputationLogResource::canViewAny());

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));
        $this->actingAs($superAdmin);

        $this->assertTrue(ReputationLogResource::canViewAny());
        $this->assertFalse(ReputationLogResource::canCreate());
        $this->assertFalse(ReputationLogResource::canEdit($log));
        $this->assertFalse(ReputationLogResource::canDelete($log));
    }
}
