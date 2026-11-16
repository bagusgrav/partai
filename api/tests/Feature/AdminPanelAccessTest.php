<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    public function test_user_with_partai_domain_email_can_access_admin_panel(): void
    {
        $user = User::factory()->make([
            'email' => 'admin@partai.id',
        ]);

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_user_with_similar_but_unapproved_email_domain_cannot_access_admin_panel(): void
    {
        $user = User::factory()->make([
            'email' => 'admin@evilpartai.id',
        ]);

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_guest_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->make([
            'email' => 'guest_c95aa24c-a7e5-4aae-b865-2cb2b5951fd5@partai.id',
        ]);

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }
}
