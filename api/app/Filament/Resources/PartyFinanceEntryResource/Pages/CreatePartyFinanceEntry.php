<?php

namespace App\Filament\Resources\PartyFinanceEntryResource\Pages;

use App\Filament\Resources\PartyFinanceEntryResource;
use App\Models\PartyFinanceEntry;
use App\Services\PartyFinanceService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePartyFinanceEntry extends CreateRecord
{
    protected static string $resource = PartyFinanceEntryResource::class;

    protected function handleRecordCreation(array $data): PartyFinanceEntry
    {
        $date = $data['transaction_date'] instanceof \DateTimeInterface
            ? $data['transaction_date']->format('Y-m-d')
            : (string) $data['transaction_date'];

        $result = app(PartyFinanceService::class)->recordTransaction([
            'request_id' => (string) Str::uuid(),
            'entry_type' => $data['entry_type'],
            'channel' => $data['channel'],
            'fiscal_year' => (int) substr($date, 0, 4),
            'transaction_date' => $date,
            'amount_idr' => (int) $data['amount_idr'],
            'donor_type' => $data['donor_type'],
            'user_id' => null,
            'contributor_display_name' => $data['contributor_display_name'],
            'identity_hash' => null,
            'masked_identity' => null,
            'legal_declaration_accepted' => (bool) ($data['legal_declaration_accepted'] ?? false),
            'purpose_or_program' => $data['purpose_or_program'],
            'region_code' => null,
            'province_name' => $data['province_name'] ?? null,
            'evidence_reference' => $data['evidence_reference'] ?? null,
            'compliance_status' => 'pending_audit',
            'recorded_by' => auth()->id(),
            'reversal_of_id' => $data['reversal_of_id'] ?? null,
        ]);

        return $result['entry'];
    }
}
