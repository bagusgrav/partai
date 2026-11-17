<?php

namespace Tests\Feature\Api;

use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FaqInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_faq_endpoint_only_returns_published_faqs(): void
    {
        Faq::query()->create([
            'question' => 'FAQ publik?',
            'answer' => 'Jawaban publik.',
            'is_published' => true,
        ]);
        Faq::query()->create([
            'question' => 'FAQ privat?',
            'answer' => 'Jawaban privat.',
            'is_published' => false,
        ]);

        $this->getJson('/api/faqs')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'question' => 'FAQ publik?',
                'answer' => 'Jawaban publik.',
            ])
            ->assertJsonMissing(['question' => 'FAQ privat?']);
    }

    public function test_a_public_inquiry_is_created_and_duplicate_request_ids_are_idempotent(): void
    {
        $requestId = (string) Str::uuid();
        $payload = [
            'request_id' => $requestId,
            'name' => 'Warga',
            'email' => 'warga@example.com',
            'question' => 'Bagaimana cara berpartisipasi?',
        ];

        $this->postJson('/api/inquiries', $payload)
            ->assertCreated()
            ->assertJsonPath('status', 'created')
            ->assertJsonPath('message', 'Pertanyaan Anda telah dikirim. Pengurus akan segera meninjau.');

        $this->postJson('/api/inquiries', [
            ...$payload,
            'question' => 'Pertanyaan berbeda dengan ID yang sama.',
        ])
            ->assertOk()
            ->assertJsonPath('status', 'already_processed');

        $this->assertDatabaseCount('inquiries', 1);
        $this->assertDatabaseHas('inquiries', [
            'request_id' => $requestId,
            'question' => 'Bagaimana cara berpartisipasi?',
            'status' => 'pending',
        ]);
    }

    public function test_public_inquiries_allow_omitted_contact_details_but_validate_required_fields(): void
    {
        $this->postJson('/api/inquiries', [
            'request_id' => (string) Str::uuid(),
            'question' => 'Pertanyaan tanpa identitas.',
        ])->assertCreated();

        $this->assertDatabaseHas('inquiries', [
            'name' => null,
            'email' => null,
            'question' => 'Pertanyaan tanpa identitas.',
        ]);

        $this->postJson('/api/inquiries', [
            'request_id' => 'not-a-uuid',
            'email' => 'email-tidak-valid',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['request_id', 'question', 'email']);
    }

    public function test_public_inquiries_are_rate_limited_to_five_requests_per_minute(): void
    {
        for ($request = 0; $request < 5; $request++) {
            $this->postJson('/api/inquiries', [
                'request_id' => (string) Str::uuid(),
                'question' => "Pertanyaan ke-{$request}.",
            ])->assertCreated();
        }

        $this->postJson('/api/inquiries', [
            'request_id' => (string) Str::uuid(),
            'question' => 'Pertanyaan keenam.',
        ])->assertTooManyRequests();

        $this->assertDatabaseCount('inquiries', 5);
    }
}
