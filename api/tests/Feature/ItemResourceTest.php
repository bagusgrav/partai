<?php

namespace Tests\Feature;

use App\Filament\Resources\ItemResource\Pages\CreateItem;
use App\Filament\Resources\ItemResource\Pages\EditItem;
use App\Models\Item;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ItemResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $user = User::factory()->create([
            'email' => 'admin@partai.id',
        ]);
        $user->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));
        $this->actingAs($user);
    }

    public function test_item_creation_stores_payload_from_the_structured_form(): void
    {
        Livewire::test(CreateItem::class)
            ->fillForm([
                'id' => 'ITEM-VALID-JSON',
                'domain' => 'hukum',
                'level' => 'kenali',
                'payload' => [
                    'type' => 'multiple-choice',
                    'question' => 'Question',
                    'explanation' => 'Explanation',
                    'options' => [
                        ['option' => 'First option'],
                        ['option' => 'Second option'],
                    ],
                    'answer' => '0',
                    'sources' => [
                        ['citation' => 'UU Nomor 1 Tahun 2024'],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(
            [
                'type' => 'multiple-choice',
                'question' => 'Question',
                'explanation' => 'Explanation',
                'instructions' => null,
                'options' => ['First option', 'Second option'],
                'answer' => '0',
                'sources' => [
                    ['citation' => 'UU Nomor 1 Tahun 2024'],
                ],
            ],
            Item::query()->findOrFail('ITEM-VALID-JSON')->payload,
        );
    }

    public function test_item_id_cannot_be_changed_during_edit(): void
    {
        $item = Item::query()->create([
            'id' => 'ITEM-ORIGINAL',
            'domain' => 'hukum',
            'level' => 'kenali',
            'payload' => ['prompt' => 'Original'],
        ]);

        Livewire::test(EditItem::class, ['record' => $item->getRouteKey()])
            ->fillForm([
                'id' => 'ITEM-CHANGED',
                'domain' => 'hukum',
                'level' => 'kenali',
                'payload' => [
                    'type' => 'multiple-choice',
                    'question' => 'Updated',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('ITEM-ORIGINAL', $item->refresh()->id);
        $this->assertDatabaseMissing('items', ['id' => 'ITEM-CHANGED']);
    }

    public function test_essay_explanation_is_saved_from_the_rich_text_field(): void
    {
        Livewire::test(CreateItem::class)
            ->fillForm([
                'id' => 'ESSAY-RICH-TEXT',
                'domain' => 'hukum',
                'level' => 'kritisi',
                'payload' => [
                    'type' => 'essay',
                    'question' => 'Question',
                    'explanation' => '<p>Reference answer</p>',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(
            '<p>Reference answer</p>',
            Item::query()->findOrFail('ESSAY-RICH-TEXT')->payload['explanation'],
        );
    }
}
