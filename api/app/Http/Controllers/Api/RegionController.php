<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'string',
                Rule::exists('regions', 'id')->where('level', 1),
            ],
        ]);

        $parentId = $validated['parent_id'] ?? null;
        $regions = Region::query()
            ->where('level', $parentId === null ? 1 : 2)
            ->when($parentId !== null, fn ($query) => $query->where('parent_id', $parentId))
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'level']);

        return response()->json(['regions' => $regions]);
    }
}
