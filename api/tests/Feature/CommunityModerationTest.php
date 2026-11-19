<?php

namespace Tests\Feature;

use App\Filament\Resources\PostReportResource;
use App\Filament\Resources\PostReportResource\Pages\ListPostReports;
use App\Filament\Resources\PostResource;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\User;
use App\Policies\PostPolicy;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommunityModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_community_resources_are_limited_to_moderators(): void
    {
        $post = new Post;
        $report = new PostReport;

        $this->assertFalse(PostResource::canViewAny());
        $this->assertFalse(PostReportResource::canViewAny());

        $panelist = User::factory()->create();
        $panelist->assignRole(Role::create(['name' => 'panelis', 'guard_name' => 'web']));
        $this->actingAs($panelist);
        $this->assertFalse(PostResource::canViewAny());
        $this->assertFalse(PostReportResource::canViewAny());

        foreach (['super_admin', 'pengurus'] as $roleName) {
            $moderator = User::factory()->create();
            $moderator->assignRole(Role::create(['name' => $roleName, 'guard_name' => 'web']));
            $this->actingAs($moderator);

            $this->assertTrue(PostResource::canViewAny());
            $this->assertTrue(PostResource::canView($post));
            $this->assertTrue(PostResource::canDelete($post));
            $this->assertFalse(PostResource::canCreate());
            $this->assertFalse(PostResource::canEdit($post));
            $this->assertTrue(PostReportResource::canViewAny());
            $this->assertTrue(PostReportResource::canView($report));
            $this->assertFalse(PostReportResource::canEdit($report));
            $this->assertFalse(PostReportResource::canDelete($report));
        }
    }

    public function test_post_policy_limits_updates_and_moderation_to_the_intended_users(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $moderator = $this->moderator();
        $post = $this->createCommunityPost($owner);
        $policy = new PostPolicy;

        $this->assertTrue($policy->update($owner, $post));
        $this->assertFalse($policy->update($other, $post));
        $this->assertTrue($policy->delete($owner, $post));
        $this->assertFalse($policy->hide($owner, $post));
        $this->assertTrue($policy->delete($moderator, $post));
        $this->assertTrue($policy->hide($moderator, $post));
    }

    public function test_hiding_and_soft_deleting_posts_write_moderation_logs(): void
    {
        $moderator = $this->moderator();
        $this->actingAs($moderator);

        $postToHide = $this->createCommunityPost();
        Livewire::test(ListPosts::class)
            ->assertTableActionExists('hide')
            ->callTableAction('hide', $postToHide)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('posts', [
            'id' => $postToHide->id,
            'status' => 'hidden',
        ]);
        $this->assertDatabaseHas('post_moderation_logs', [
            'post_id' => $postToHide->id,
            'actor_id' => $moderator->id,
            'action' => 'hidden',
        ]);

        $postToDelete = $this->createCommunityPost();
        Livewire::test(ListPosts::class)
            ->assertTableActionExists('softDelete')
            ->callTableAction('softDelete', $postToDelete)
            ->assertHasNoTableActionErrors();

        $this->assertSoftDeleted('posts', ['id' => $postToDelete->id]);
        $this->assertDatabaseHas('post_moderation_logs', [
            'post_id' => $postToDelete->id,
            'actor_id' => $moderator->id,
            'action' => 'deleted',
        ]);
    }

    public function test_reviewing_reports_writes_a_moderation_log(): void
    {
        $moderator = $this->moderator();
        $this->actingAs($moderator);
        $post = $this->createCommunityPost();
        $reporter = User::factory()->create();
        $report = PostReport::query()->create([
            'post_id' => $post->id,
            'reporter_id' => $reporter->id,
            'reason' => 'Perlu ditinjau',
            'status' => 'open',
        ]);

        Livewire::test(ListPostReports::class)
            ->assertTableActionExists('review')
            ->callTableAction('review', $report)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('post_reports', [
            'id' => $report->id,
            'status' => 'reviewed',
        ]);
        $this->assertDatabaseHas('post_moderation_logs', [
            'post_id' => $post->id,
            'actor_id' => $moderator->id,
            'action' => 'report_reviewed',
        ]);
    }

    private function moderator(): User
    {
        $moderator = User::factory()->create();
        $moderator->assignRole(Role::create(['name' => 'pengurus', 'guard_name' => 'web']));

        return $moderator;
    }

    private function createCommunityPost(?User $author = null): Post
    {
        return Post::query()->create([
            'user_id' => ($author ?? User::factory()->create())->id,
            'body' => 'Teks post moderasi',
            'visibility' => 'public',
            'status' => 'published',
            'request_id' => (string) Str::uuid(),
        ]);
    }
}
