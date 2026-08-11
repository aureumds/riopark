<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShiftController extends Controller
{
    public function open(Request $request): JsonResponse
    {
        $request->validate([
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'local_uuid' => ['nullable', 'uuid'],
        ]);

        $user = $request->user();

        $existing = Shift::where('user_id', $user->id)->whereNull('closed_at')->first();

        if ($existing) {
            return response()->json(['shift' => $existing, 'message' => 'Turno já aberto']);
        }

        $shift = Shift::create([
            'local_uuid' => $request->input('local_uuid', (string) Str::uuid()),
            'company_id' => $user->company_id,
            'parking_lot_id' => $user->parking_lot_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_balance' => $request->input('opening_balance', 0),
            'sync_status' => 'synced',
        ]);

        return response()->json(['shift' => $shift], 201);
    }

    public function close(Request $request): JsonResponse
    {
        $request->validate([
            'closing_balance' => ['nullable', 'numeric', 'min:0'],
            'local_uuid' => ['nullable', 'uuid'],
        ]);

        $user = $request->user();

        $shift = Shift::where('user_id', $user->id)->whereNull('closed_at')->first();

        if (! $shift) {
            return response()->json(['message' => 'Nenhum turno aberto'], 404);
        }

        if ($request->filled('local_uuid') && $shift->local_uuid !== $request->local_uuid) {
            $shift = Shift::where('local_uuid', $request->local_uuid)->first() ?? $shift;
        }

        $shift->update([
            'closed_at' => now(),
            'closing_balance' => $request->input('closing_balance', 0),
            'sync_status' => 'synced',
        ]);

        return response()->json(['shift' => $shift->fresh()]);
    }

    public function current(Request $request): JsonResponse
    {
        $shift = Shift::where('user_id', $request->user()->id)
            ->whereNull('closed_at')
            ->withCount(['parkingSessions'])
            ->first();

        return response()->json(['shift' => $shift]);
    }
}
