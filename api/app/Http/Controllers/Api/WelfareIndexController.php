<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\FactionContract;
use App\Models\RegionWelfareIndex;
use App\Services\WelfareIndexService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WelfareIndexController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province' => ['sometimes', 'string', 'max:255'],
            'tier' => ['sometimes', Rule::in(['rendah', 'sedang', 'tinggi', 'kritis'])],
            'year' => ['sometimes', 'integer', 'between:1900,2100'],
            'search' => ['sometimes', 'string', 'max:100'],
            'sort' => ['sometimes', Rule::in(['ikk_desc', 'ikk_asc', 'poverty_desc', 'disaster_desc'])],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $year = $validated['year'] ?? RegionWelfareIndex::query()->max('period_year') ?? (int) now()->format('Y');
        $year = (int) $year;
        $national = RegionWelfareIndex::query()->where('period_year', $year);
        $distribution = ['kritis' => 0, 'tinggi' => 0, 'sedang' => 0, 'rendah' => 0];

        foreach ((clone $national)
            ->select('vulnerability_tier')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('vulnerability_tier')
            ->get() as $row) {
            $distribution[$row->vulnerability_tier] = (int) $row->total;
        }
        $average = $national->avg('ikk_score');

        $query = RegionWelfareIndex::query()->where('period_year', $year);
        $query->when(isset($validated['province']), fn ($builder) => $builder->where('province_name', $validated['province']));
        $query->when(isset($validated['tier']), fn ($builder) => $builder->where('vulnerability_tier', $validated['tier']));
        $query->when(isset($validated['search']), function ($builder) use ($validated): void {
            $search = trim($validated['search']);
            $builder->where(function ($nested) use ($search): void {
                $nested->where('region_name', 'like', "%{$search}%")
                    ->orWhere('region_code', 'like', "%{$search}%");
            });
        });

        $sort = $validated['sort'] ?? 'ikk_desc';
        $sortColumn = match ($sort) {
            'ikk_asc' => 'ikk_score',
            'poverty_desc' => 'poverty_rate',
            'disaster_desc' => 'disaster_risk_score',
            default => 'ikk_score',
        };
        $query->orderBy($sortColumn, $sort === 'ikk_asc' ? 'asc' : 'desc')
            ->orderBy('region_name');

        return response()->json([
            'summary' => [
                'year' => $year,
                'average_ikk' => $average === null
                    ? null
                    : round((float) $average, 2),
                'distribution' => $distribution,
                'methodology' => WelfareIndexService::METHODOLOGY,
            ],
            'provinces' => RegionWelfareIndex::query()
                ->where('period_year', $year)
                ->distinct()
                ->orderBy('province_name')
                ->pluck('province_name'),
            'regions' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function show(string $regionCode, WelfareIndexService $service): JsonResponse
    {
        $region = RegionWelfareIndex::query()
            ->where('region_code', $regionCode)
            ->orderByDesc('period_year')
            ->first();

        if ($region === null) {
            return response()->json(['message' => 'Data wilayah tidak ditemukan.'], 404);
        }

        $calculation = $service->calculate($region->only([
            'poverty_rate',
            'stunting_rate',
            'gini_ratio',
            'open_unemployment_rate',
            'disaster_risk_score',
        ]));
        $matchingWork = static fn ($query) => $query->where(function ($scope) use ($region): void {
            $scope->where('region_code', $region->region_code)
                ->orWhere('province_name', $region->province_name);
        });

        return response()->json([
            'region' => $region,
            'sub_scores' => $calculation['sub_scores'],
            'sources' => [
                'bps' => $region->bps_source_citation,
                'bnpb' => $region->bnpb_source_citation,
            ],
            'internal_work' => [
                'faction_contracts' => $matchingWork(FactionContract::query())
                    ->whereIn('status', ['BELUM_DIMULAI', 'SEDANG_BERJALAN'])
                    ->orderBy('title')
                    ->get(['id', 'politician_name', 'title', 'status', 'region_code', 'province_name']),
                'events' => $matchingWork(Event::query())
                    ->where('status', 'active')
                    ->orderBy('starts_at')
                    ->get(['id', 'title', 'description', 'starts_at', 'ends_at', 'region_code', 'province_name']),
            ],
        ]);
    }

    public function ingest(Request $request, WelfareIndexService $service): JsonResponse
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
            'rows' => ['required', 'array', 'min:1', 'max:500'],
            'rows.*' => ['array:region_code,region_name,province_name,period_year,poverty_rate,gini_ratio,open_unemployment_rate,stunting_rate,disaster_events_count,affected_population,disaster_risk_score,bps_source_citation,bnpb_source_citation'],
            'rows.*.region_code' => ['required', 'string', 'regex:/^\d{4}$/'],
            'rows.*.region_name' => ['required', 'string', 'max:255'],
            'rows.*.province_name' => ['required', 'string', 'max:255'],
            'rows.*.period_year' => ['required', 'integer', 'between:1900,2100'],
            'rows.*.poverty_rate' => ['required', 'numeric', 'between:0,100'],
            'rows.*.gini_ratio' => ['required', 'numeric', 'between:0,1'],
            'rows.*.open_unemployment_rate' => ['required', 'numeric', 'between:0,100'],
            'rows.*.stunting_rate' => ['required', 'numeric', 'between:0,100'],
            'rows.*.disaster_events_count' => ['required', 'integer', 'min:0'],
            'rows.*.affected_population' => ['required', 'integer', 'min:0'],
            'rows.*.disaster_risk_score' => ['required', 'numeric', 'between:0,100'],
            'rows.*.bps_source_citation' => ['required', 'string', 'max:255'],
            'rows.*.bnpb_source_citation' => ['required', 'string', 'max:255'],
        ]);

        $requestId = $validated['request_id'];
        $rows = $validated['rows'];
        foreach ($rows as &$row) {
            ksort($row);
        }
        unset($row);
        usort($rows, static fn (array $a, array $b): int => [$a['region_code'], $a['period_year']] <=> [$b['region_code'], $b['period_year']]);
        $payloadHash = hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        foreach ($rows as &$row) {
            $row['last_synced_at'] = now()->toDateTimeString();
        }
        unset($row);
        $existing = DB::table('welfare_ikk_ingest_requests')->where('id', $requestId)->first();

        if ($existing !== null) {
            return $this->duplicateIngestResponse($existing, $payloadHash);
        }

        try {
            $result = DB::transaction(function () use ($requestId, $payloadHash, $rows, $service): array {
                DB::table('welfare_ikk_ingest_requests')->insert([
                    'id' => $requestId,
                    'payload_hash' => $payloadHash,
                    'response' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count = $service->upsert($rows);
                $response = ['status' => 'processed', 'records_saved' => $count];
                DB::table('welfare_ikk_ingest_requests')->where('id', $requestId)->update([
                    'response' => json_encode($response, JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);

                return $response;
            });
        } catch (QueryException $exception) {
            $existing = DB::table('welfare_ikk_ingest_requests')->where('id', $requestId)->first();

            if ($existing === null) {
                throw $exception;
            }

            return $this->duplicateIngestResponse($existing, $payloadHash);
        }

        return response()->json($result, 201);
    }

    private function duplicateIngestResponse(object $existing, string $payloadHash): JsonResponse
    {
        if (! hash_equals($existing->payload_hash, $payloadHash)) {
            return response()->json(['message' => 'request_id sudah digunakan untuk batch indikator yang berbeda.'], 409);
        }

        $response = json_decode((string) $existing->response, true, flags: JSON_THROW_ON_ERROR);
        $response['status'] = 'already_processed';

        return response()->json($response);
    }
}
