<?php

namespace Tests\Feature\Api;

use App\Models\GlossaryTerm;
use Database\Seeders\GlossaryTermSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlossaryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_glossary_is_public_and_searches_terms_definitions_and_categories(): void
    {
        $term = GlossaryTerm::query()->create([
            'term' => 'IKK',
            'definition' => 'Indeks kerentanan wilayah.',
            'category' => 'Data publik',
        ]);
        GlossaryTerm::query()->create([
            'term' => 'Inflasi',
            'definition' => 'Perubahan harga barang dan jasa.',
            'category' => 'Ekonomi',
        ]);

        $this->getJson('/api/glossary')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => $term->id, 'term' => 'IKK']);

        $this->getJson('/api/glossary?search=INDEKS')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.term', 'IKK');

        $this->getJson('/api/glossary?search=ekonomi')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.term', 'Inflasi');
    }

    public function test_glossary_search_has_clear_input_validation(): void
    {
        $this->getJson('/api/glossary?search[]=invalid')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['search']);

        $this->getJson('/api/glossary?search='.str_repeat('a', 101))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['search']);
    }

    public function test_glossary_seeder_is_repeatable_and_contains_the_required_terms(): void
    {
        $this->seed(GlossaryTermSeeder::class);
        $this->seed(GlossaryTermSeeder::class);

        $this->assertDatabaseCount('glossary_terms', 13);
        $this->assertDatabaseHas('glossary_terms', ['term' => 'Participation Score']);
    }
}
