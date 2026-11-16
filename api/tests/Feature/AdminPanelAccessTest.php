<?php

namespace Tests\Feature;

use App\Filament\Resources\ItemResource;
use App\Filament\Resources\UserResource;
use App\Models\Item;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_partai_domain_email_without_a_role_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@partai.id',
        ]);

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_user_with_an_allowed_role_can_access_admin_panel(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.test',
        ]);
        $user->assignRole(Role::create(['name' => 'panelis', 'guard_name' => 'web']));

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_guest_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create([
            'email' => 'guest_c95aa24c-a7e5-4aae-b865-2cb2b5951fd5@partai.id',
        ]);

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_pengurus_can_manage_items_but_cannot_manage_users_or_delete_items(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'pengurus', 'guard_name' => 'web']));
        $this->actingAs($user);
        $item = new Item;
        $userRecord = new User;

        $this->assertTrue(ItemResource::canViewAny());
        $this->assertTrue(ItemResource::canCreate());
        $this->assertTrue(ItemResource::canEdit($item));
        $this->assertFalse(ItemResource::canDelete($item));
        $this->assertFalse(ItemResource::canDeleteAny());
        $this->assertFalse(UserResource::canViewAny());
        $this->assertFalse(UserResource::canCreate());
        $this->assertFalse(UserResource::canEdit($userRecord));
        $this->assertFalse(UserResource::canDelete($userRecord));
    }

    public function test_super_admin_can_manage_users_and_delete_items(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));
        $this->actingAs($user);
        $item = new Item;
        $userRecord = new User;

        $this->assertTrue(UserResource::canViewAny());
        $this->assertTrue(UserResource::canCreate());
        $this->assertTrue(UserResource::canEdit($userRecord));
        $this->assertTrue(UserResource::canDelete($userRecord));
        $this->assertTrue(ItemResource::canDelete($item));
        $this->assertTrue(ItemResource::canDeleteAny());
    }
}
