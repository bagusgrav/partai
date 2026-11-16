<?php

namespace Tests\Feature;

use App\Filament\Resources\WelfareDataResource\Pages\EditWelfareData;
use App\Filament\Resources\WelfareDataResource\Pages\ListWelfareData;
use App\Models\User;
use App\Models\WelfareData;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WelfareDataResourceTest extends TestCase
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

    public function test_admin_can_view_and_edit_welfare_data(): void
    {
        $welfareData = WelfareData::factory()->create();

        Livewire::test(ListWelfareData::class)
            ->assertCanSeeTableRecords([$welfareData]);

        Livewire::test(EditWelfareData::class, ['record' => $welfareData->getRouteKey()])
            ->fillForm([
                'region_code' => '35',
                'region_name' => 'Jawa Timur',
                'indicator' => 'kemiskinan',
                'value' => 10.5,
                'unit' => 'persen',
                'year' => 2023,
                'source' => 'BPS',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('welfare_data', [
            'id' => $welfareData->id,
            'region_code' => '35',
            'region_name' => 'Jawa Timur',
            'value' => 10.5,
        ]);
    }
}
