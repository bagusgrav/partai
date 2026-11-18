<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BureaucracyReport;
use App\Models\PublicAgencyScorecard;
use App\Services\BureaucracyWatchService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BureaucracyWatchController extends Controller
{
    public function scorecards(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['sometimes', 'string'],
            'tier' => ['sometimes', Rule::in(['sangat_baik', 'baik', 'perlu_perbaikan', 'kritis'])],
            'province' => ['sometimes', 'string', 'max:255'],
            'year' => ['sometimes', 'integer', 'between:1900,2100'],
            'search' => ['sometimes', 'string', 'max:100'],
            'sort' => ['sometimes', Rule::in(['score_desc', 'score_asc', 'complaints_desc'])],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $year = $validated['year'] ?? PublicAgencyScorecard::query()->max('period_year') ?? (int) now()->format('Y');
        $national = PublicAgencyScorecard::query()->where('period_year', $year);
        $distribution = ['sangat_baik' => 0, 'baik' => 0, 'perlu_perbaikan' => 0, 'kritis' => 0];
        foreach ((clone $national)->select('performance_tier')->selectRaw('COUNT(*) AS total')->groupBy('performance_tier')->get() as $row) {
            $distribution[$row->performance_tier] = (int) $row->total;
        }

        $query = PublicAgencyScorecard::query()->where('period_year', $year);
        $query->when(isset($validated['category']), fn ($builder) => $builder->where('agency_category', $validated['category']));
        $query->when(isset($validated['tier']), fn ($builder) => $builder->where('performance_tier', $validated['tier']));
        $query->when(isset($validated['province']), fn ($builder) => $builder->where('province_name', $validated['province']));
        $query->when(isset($validated['search']), function ($builder) use ($validated): void {
            $search = trim($validated['search']);
            $builder->where(function ($nested) use ($search): void {
                $nested->where('agency_name', 'like', "%{$search}%")
                    ->orWhere('agency_code', 'like', "%{$search}%");
            });
        });

        $sort = $validated['sort'] ?? 'score_desc';
        if ($sort === 'score_asc') {
            $query->orderBy('bureaucracy_integrity_score')->orderBy('agency_name');
        } elseif ($sort === 'complaints_desc') {
            $query->orderByRaw('(open_complaints_count + resolved_complaints_count) desc')->orderBy('agency_name');
        } else {
            $query->orderByDesc('bureaucracy_integrity_score')->orderBy('agency_name');
        }

        $verifiedStatuses = ['verified', 'forwarded_to_ombudsman', 'resolved'];
        $publicReports = BureaucracyReport::query()->whereIn('verification_status', $verifiedStatuses);

        return response()->json([
            'summary' => [
                'year' => (int) $year,
                'average_integrity_score' => $national->avg('bureaucracy_integrity_score') === null
                    ? null
                    : round((float) $national->avg('bureaucracy_integrity_score'), 2),
                'distribution' => $distribution,
                'verified_reports_count' => (clone $publicReports)->count(),
                'forwarded_to_ombudsman_count' => (clone $publicReports)->where('verification_status', 'forwarded_to_ombudsman')->count(),
                'resolved_reports_count' => (clone $publicReports)->where('verification_status', 'resolved')->count(),
            ],
            'scorecards' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function publicReports(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['sometimes', Rule::in(['maladministrasi', 'penyalahgunaan_fasilitas', 'pungli_layanan', 'penundaan_berlarut', 'diskriminasi_layanan'])],
            'province' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(['verified', 'forwarded_to_ombudsman', 'resolved'])],
        ]);

        $query = BureaucracyReport::query()
            ->whereIn('verification_status', ['verified', 'forwarded_to_ombudsman', 'resolved'])
            ->when(isset($validated['category']), fn ($builder) => $builder->where('category', $validated['category']))
            ->when(isset($validated['province']), fn ($builder) => $builder->where('province_name', $validated['province']))
            ->when(isset($validated['status']), fn ($builder) => $builder->where('verification_status', $validated['status']))
            ->orderByDesc('updated_at');

        $reports = $query->paginate(20)->withQueryString();
        $reports->getCollection()->transform(function (BureaucracyReport $report): array {
            return [
                'id' => $report->id,
                'ticket_code' => $report->ticket_code,
                'agency_name' => $report->agency_name,
                'province_name' => $report->province_name,
                'category' => $report->category,
                'title' => $report->title,
                'chronology' => $report->chronology,
                'incident_date' => $report->incident_date?->toDateString(),
                'asset_identifier' => $report->asset_identifier,
                'evidence_url' => $report->evidence_url,
                'verification_status' => $report->verification_status,
                'public_resolution_note' => $report->public_resolution_note,
                'verified_at' => $report->verified_at,
                'created_at' => $report->created_at,
            ];
        });

        return response()->json(['reports' => $reports]);
    }

    public function store(Request $request, BureaucracyWatchService $service): JsonResponse
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
            'agency_id' => ['nullable', 'uuid', 'exists:public_agency_scorecards,id'],
            'agency_name' => ['required', 'string', 'max:255'],
            'province_name' => ['nullable', 'string', 'max:255'],
            'region_code' => ['nullable', 'string', 'max:255'],
            'category' => ['required', Rule::in(['maladministrasi', 'penyalahgunaan_fasilitas', 'pungli_layanan', 'penundaan_berlarut', 'diskriminasi_layanan'])],
            'title' => ['required', 'string', 'max:180'],
            'chronology' => ['required', 'string', 'min:30'],
            'incident_date' => ['required', 'date', 'before_or_equal:today'],
            'asset_identifier' => ['nullable', 'string', 'max:255'],
            'evidence_url' => ['nullable', 'url', 'max:255'],
        ]);

        $payload = collect($validated)->except('request_id')->all();
        ksort($payload);
        $payloadHash = hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $existing = BureaucracyReport::query()->where('request_id', $validated['request_id'])->first();
        if ($existing !== null) {
            if (! hash_equals($existing->payload_hash, $payloadHash)) {
                return response()->json(['message' => 'request_id sudah digunakan untuk laporan yang berbeda.'], 409);
            }

            return response()->json([
                'ticket_code' => $existing->ticket_code,
                'verification_status' => $existing->verification_status,
                'created_at' => $existing->created_at,
                'status' => 'already_processed',
            ]);
        }

        try {
            $report = DB::transaction(function () use ($validated, $payload, $payloadHash, $service): BureaucracyReport {
                $payload['request_id'] = $validated['request_id'];
                $payload['payload_hash'] = $payloadHash;

                return $service->createReport($payload);
            });
        } catch (QueryException $exception) {
            $existing = BureaucracyReport::query()->where('request_id', $validated['request_id'])->first();
            if ($existing === null) {
                throw $exception;
            }
            if (! hash_equals($existing->payload_hash, $payloadHash)) {
                return response()->json(['message' => 'request_id sudah digunakan untuk laporan yang berbeda.'], 409);
            }

            return response()->json([
                'ticket_code' => $existing->ticket_code,
                'verification_status' => $existing->verification_status,
                'created_at' => $existing->created_at,
                'status' => 'already_processed',
            ]);
        }

        $secret = $report->getAttribute('reporter_secret');

        return response()->json([
            'ticket_code' => $report->ticket_code,
            'reporter_secret' => $secret,
            'verification_status' => $report->verification_status,
            'created_at' => $report->created_at,
        ], 201);
    }

    public function track(string $ticketCode, Request $request): JsonResponse
    {
        $report = BureaucracyReport::query()->where('ticket_code', $ticketCode)->first();
        if ($report === null) {
            return response()->json(['message' => 'Tiket tidak ditemukan.'], 404)->header('Cache-Control', 'no-store');
        }

        $secret = $request->query('secret');
        $isReporter = is_string($secret) && hash_equals($report->reporter_token_hash, hash('sha256', $secret));
        $verified = in_array($report->verification_status, ['verified', 'forwarded_to_ombudsman', 'resolved'], true);

        $response = [
            'ticket_code' => $report->ticket_code,
            'verification_status' => $report->verification_status,
            'category' => $report->category,
            'agency_name' => $report->agency_name,
            'province_name' => $report->province_name,
            'public_resolution_note' => $report->public_resolution_note,
            'verified_at' => $report->verified_at,
        ];
        if ($verified || $isReporter) {
            $response['chronology'] = $report->chronology;
        }

        return response()->json($response)->header('Cache-Control', 'no-store');
    }
}
