<?php

namespace Tests\Feature;

use App\Filament\Resources\FactionContractResource;
use App\Models\FactionContract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FactionContractResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_contract_managers_can_access_the_filament_resource(): void
    {
        $contract = new FactionContract;

        $this->assertFalse(FactionContractResource::canViewAny());

        $panelis = User::factory()->create();
        $panelis->assignRole(Role::create(['name' => 'panelis', 'guard_name' => 'web']));
        $this->actingAs($panelis);

        $this->assertFalse(FactionContractResource::canViewAny());
        $this->assertFalse(FactionContractResource::canCreate());
        $this->assertFalse(FactionContractResource::canEdit($contract));

        $pengurus = User::factory()->create();
        $pengurus->assignRole(Role::create(['name' => 'pengurus', 'guard_name' => 'web']));
        $this->actingAs($pengurus);

        $this->assertTrue(FactionContractResource::canViewAny());
        $this->assertTrue(FactionContractResource::canCreate());
        $this->assertTrue(FactionContractResource::canEdit($contract));
    }
}
