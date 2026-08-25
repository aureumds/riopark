<?php

namespace App\Http\Controllers\Operator\Lite;

use App\Http\Controllers\Controller;
use App\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function push(Request $request, SyncService $sync): JsonResponse
    {
        $request->validate([
            'events' => ['required', 'array'],
        ]);

        $results = $sync->push($request->user(), $request->input('events', []));

        return response()->json(['results' => $results]);
    }
}
