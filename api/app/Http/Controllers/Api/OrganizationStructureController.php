<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganizationSeat;
use App\Services\OrganizationStructureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationStructureController extends Controller
{
    public function __construct(private readonly OrganizationStructureService $service) {}

    public function tree(Request $request): JsonResponse
    {
        return response()->json($this->service->getTree($request->only([
            'layer',
            'status',
            'territory_level',
            'search',
        ])));
    }

    public function eligibility(Request $request): JsonResponse
    {
        $user = $request->user();
        $seats = OrganizationSeat::query()
            ->orderBy('sort_order')
            ->get()
            ->map(function (OrganizationSeat $seat) use ($user): array {
                $result = $this->service->evaluateEligibility($user, $seat);

                return array_merge([
                    'id' => $seat->id,
                    'seat_code' => $seat->seat_code,
                    'title' => $seat->title,
                    'status' => $seat->status,
                    'required_mastery_level' => $seat->required_mastery_level,
                    'required_domain' => $seat->required_domain,
                    'required_min_participation' => $seat->required_min_participation,
                ], $result);
            });

        return response()->json([
            'seats' => $seats,
        ]);
    }

    public function apply(Request $request, OrganizationSeat $seat): JsonResponse
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
            'statement' => ['required', 'string', 'min:20', 'max:5000'],
        ]);
        $result = $this->service->applyForSeat($request->user(), $seat, $validated);

        return response()->json([
            'status' => $result['created'] ? 'created' : 'already_processed',
            'application' => $result['application'],
        ], $result['created'] ? 201 : 200);
    }
}
