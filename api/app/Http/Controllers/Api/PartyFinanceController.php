<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PartyFinanceEntry;
use App\Services\PartyFinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartyFinanceController extends Controller
{
    public function __construct(private readonly PartyFinanceService $finance) {}

    public function summary(): JsonResponse
    {
        return response()->json(['summary' => $this->finance->getPublicSummary()]);
    }

    public function ledger(Request $request): JsonResponse
    {
        $query = PartyFinanceEntry::query()->orderByDesc('sequence');

        if ($request->filled('entry_type')) {
            $query->where('entry_type', $request->string('entry_type'));
        }
        if ($request->filled('channel')) {
            $query->where('channel', $request->string('channel'));
        }
        if ($request->filled('donor_type')) {
            $query->where('donor_type', $request->string('donor_type'));
        }
        if ($request->filled('status')) {
            $query->where('compliance_status', $request->string('status'));
        }
        if ($request->filled('year')) {
            $query->where('fiscal_year', $request->integer('year'));
        }
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('contributor_display_name', 'like', "%{$search}%")
                    ->orWhere('province_name', 'like', "%{$search}%")
                    ->orWhere('purpose_or_program', 'like', "%{$search}%");
            });
        }

        $ledger = $query->paginate(min($request->integer('per_page', 20), 100));

        $ledger->getCollection()->transform(fn (PartyFinanceEntry $entry) => [
            'id' => $entry->id,
            'sequence' => $entry->sequence,
            'entry_type' => $entry->entry_type,
            'channel' => $entry->channel,
            'fiscal_year' => $entry->fiscal_year,
            'transaction_date' => $entry->transaction_date?->toDateString(),
            'amount_idr' => $entry->amount_idr,
            'donor_type' => $entry->donor_type,
            'contributor_display_name' => $entry->contributor_display_name,
            'masked_identity' => $entry->masked_identity,
            'purpose_or_program' => $entry->purpose_or_program,
            'region_code' => $entry->region_code,
            'province_name' => $entry->province_name,
            'evidence_reference' => $entry->evidence_reference,
            'compliance_status' => $entry->compliance_status,
            'audited_at' => $entry->audited_at?->toISOString(),
            'audit_note' => $entry->compliance_status === 'verified' ? $entry->audit_note : null,
            'reversal_of_id' => $entry->reversal_of_id,
            'previous_hash' => $entry->previous_hash,
            'hash' => $entry->hash,
            'created_at' => $entry->created_at?->toISOString(),
        ]);

        return response()->json(['ledger' => $ledger]);
    }

    public function submitDonation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
            'donor_type' => ['required', 'in:individual,corporate'],
            'contributor_name' => ['required', 'string', 'max:255'],
            'identity_number' => ['required', 'string'],
            'amount_idr' => ['required', 'integer', 'min:10000'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'purpose_or_program' => ['nullable', 'string', 'max:220'],
            'evidence_reference' => ['required', 'string', 'max:255'],
            'legal_declaration_accepted' => ['required', 'accepted'],
        ]);

        $normalized = preg_replace('/\D/', '', $validated['identity_number']) ?? '';
        if (($validated['donor_type'] === 'individual' && ! preg_match('/^\d{16}$/', $normalized))
            || ($validated['donor_type'] === 'corporate' && ! preg_match('/^\d{15,16}$/', $normalized))) {
            return response()->json([
                'message' => $validated['donor_type'] === 'individual'
                    ? 'NIK harus terdiri dari 16 digit.'
                    : 'NPWP/NIB harus terdiri dari 15 atau 16 digit.',
            ], 422);
        }

        $identityHash = hash('sha256', $normalized);
        $maskedIdentity = $validated['donor_type'] === 'individual'
            ? 'NIK ••••••••'.substr($normalized, -4)
            : 'NPWP '.substr($normalized, 0, 2).'.•••.•••.'.substr($normalized, -1).'-000.000';

        $date = $validated['transaction_date'];
        $result = $this->finance->recordTransaction([
            'request_id' => $validated['request_id'],
            'entry_type' => 'inflow',
            'channel' => $validated['donor_type'] === 'individual'
                ? 'individual_donation'
                : 'corporate_donation',
            'fiscal_year' => (int) substr($date, 0, 4),
            'transaction_date' => $date,
            'amount_idr' => (int) $validated['amount_idr'],
            'donor_type' => $validated['donor_type'],
            'user_id' => null,
            'contributor_display_name' => $validated['contributor_name'],
            'identity_hash' => $identityHash,
            'masked_identity' => $maskedIdentity,
            'legal_declaration_accepted' => true,
            'purpose_or_program' => $validated['purpose_or_program'] ?? 'Dukungan Operasional & Pendidikan Politik',
            'region_code' => null,
            'province_name' => null,
            'evidence_reference' => $validated['evidence_reference'],
            'compliance_status' => 'pending_audit',
            'recorded_by' => $request->user()?->id,
            'reversal_of_id' => null,
        ]);

        $entry = $result['entry'];

        return response()->json([
            'created' => $result['created'],
            'entry' => [
                'id' => $entry->id,
                'sequence' => $entry->sequence,
                'compliance_status' => $entry->compliance_status,
                'masked_identity' => $entry->masked_identity,
            ],
        ], $result['created'] ? 201 : 200);
    }

    public function getMyDues(Request $request): JsonResponse
    {
        $user = $request->user();
        $year = (int) date('Y');
        $entries = PartyFinanceEntry::query()
            ->where('donor_type', 'member')
            ->where('user_id', $user->id)
            ->where('fiscal_year', $year)
            ->orderByDesc('transaction_date')
            ->get()
            ->map(fn (PartyFinanceEntry $entry) => [
                'id' => $entry->id,
                'amount_idr' => $entry->amount_idr,
                'transaction_date' => $entry->transaction_date?->toDateString(),
                'evidence_reference' => $entry->evidence_reference,
                'compliance_status' => $entry->compliance_status,
                'purpose_or_program' => $entry->purpose_or_program,
                'hash' => $entry->hash,
            ]);

        $accumulated = $entries
            ->whereIn('compliance_status', ['pending_audit', 'verified'])
            ->sum('amount_idr');
        $limit = (int) config('finance.max_annual_member_dues_idr');

        return response()->json([
            'year' => $year,
            'annual_accumulated_idr' => (int) $accumulated,
            'annual_remaining_idr' => max(0, $limit - (int) $accumulated),
            'annual_limit_idr' => $limit,
            'entries' => $entries->values(),
        ]);
    }

    public function submitMyDues(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
            'amount_idr' => ['required', 'integer', 'min:10000'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'evidence_reference' => ['required', 'string', 'max:255'],
            'legal_declaration_accepted' => ['required', 'accepted'],
        ]);

        $user = $request->user();
        $date = $validated['transaction_date'];
        $province = $user->cluster_id ? WelfareRegionName::provinceForCluster((string) $user->cluster_id) : null;
        $result = $this->finance->recordTransaction([
            'request_id' => $validated['request_id'],
            'entry_type' => 'inflow',
            'channel' => 'member_dues',
            'fiscal_year' => (int) substr($date, 0, 4),
            'transaction_date' => $date,
            'amount_idr' => (int) $validated['amount_idr'],
            'donor_type' => 'member',
            'user_id' => $user->id,
            'contributor_display_name' => $province
                ? "Anggota Wilayah {$province}"
                : 'Anggota Wilayah',
            'identity_hash' => null,
            'masked_identity' => null,
            'legal_declaration_accepted' => true,
            'purpose_or_program' => 'Iuran rutin anggota',
            'region_code' => $user->cluster_id,
            'province_name' => $province,
            'evidence_reference' => $validated['evidence_reference'],
            'compliance_status' => 'pending_audit',
            'recorded_by' => $user->id,
            'reversal_of_id' => null,
        ]);

        return response()->json([
            'created' => $result['created'],
            'entry' => $result['entry']->only([
                'id',
                'amount_idr',
                'transaction_date',
                'compliance_status',
                'evidence_reference',
                'hash',
            ]),
        ], $result['created'] ? 201 : 200);
    }
}
