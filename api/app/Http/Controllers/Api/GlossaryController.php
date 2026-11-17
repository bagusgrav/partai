<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GlossaryTerm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlossaryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');

        $terms = GlossaryTerm::query()
            ->when($search !== '', function ($query) use ($search) {
                $pattern = '%'.mb_strtolower($search).'%';
                $query->where(function ($query) use ($pattern) {
                    $query->whereRaw('LOWER(term) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(definition) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(category) LIKE ?', [$pattern]);
                });
            })
            ->orderBy('term')
            ->get(['id', 'term', 'definition', 'category']);

        return response()->json($terms);
    }
}
