<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function __construct(private SyncService $syncService) {}

    public function push(Request $request): JsonResponse
    {
        $request->validate([
            'events' => ['required', 'array'],
        ]);

        $results = $this->syncService->push($request->user(), $request->input('events'));

        return response()->json(['results' => $results]);
    }

    public function pull(Request $request): JsonResponse
    {
        $data = $this->syncService->pull($request->user(), $request->query('since'));

        return response()->json($data);
    }
}
