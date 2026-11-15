<?php

namespace Tests\Feature;

use App\Filament\Resources\ItemResource\Pages\CreateItem;
use App\Filament\Resources\ItemResource\Pages\EditItem;
use App\Models\Item;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ItemResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create([
            'email' => 'admin@partai.id',
        ]));
    }

    public function test_item_creation_rejects_invalid_json_payload(): void
    {
        Livewire::test(CreateItem::class)
            ->fillForm([
                'id' => 'ITEM-INVALID-JSON',
                'domain' => 'hukum',
                'level' => 'kenali',
                'payload' => '{"prompt":}',
            ])
            ->call('create')
            ->assertHasFormErrors(['payload' => 'json']);
    }

    public function test_item_creation_stores_the_json_payload_as_an_array(): void
    {
        Livewire::test(CreateItem::class)
            ->fillForm([
                'id' => 'ITEM-VALID-JSON',
                'domain' => 'hukum',
                'level' => 'kenali',
                'payload' => '{"prompt":"Question"}',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(
            ['prompt' => 'Question'],
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
                'payload' => '{"prompt":"Updated"}',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('ITEM-ORIGINAL', $item->refresh()->id);
        $this->assertDatabaseMissing('items', ['id' => 'ITEM-CHANGED']);
    }
}
