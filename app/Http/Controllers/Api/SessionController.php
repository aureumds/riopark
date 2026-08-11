<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingSession;
use App\Models\Payment;
use App\Models\Shift;
use App\Services\PlateNormalizer;
use App\Services\TariffCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    public function __construct(
        private PlateNormalizer $plateNormalizer,
        private TariffCalculator $tariffCalculator,
    ) {}

    public function entry(Request $request): JsonResponse
    {
        $request->validate([
            'plate' => ['required', 'string', 'min:4', 'max:10'],
            'local_uuid' => ['nullable', 'uuid'],
        ]);

        $user = $request->user();
        $normalized = $this->plateNormalizer->normalize($request->plate);

        $activeExists = ParkingSession::where('parking_lot_id', $user->parking_lot_id)
            ->where('plate_normalized', $normalized)
            ->where('status', ParkingSession::STATUS_ACTIVE)
            ->exists();

        if ($activeExists) {
            return response()->json(['message' => 'Placa já está no pátio'], 422);
        }

        $shift = $this->getOpenShift($user);

        $session = ParkingSession::create([
            'local_uuid' => $request->input('local_uuid', (string) Str::uuid()),
            'company_id' => $user->company_id,
            'parking_lot_id' => $user->parking_lot_id,
            'shift_id' => $shift->id,
            'plate' => $this->plateNormalizer->format($request->plate),
            'plate_normalized' => $normalized,
            'entry_at' => now(),
            'status' => ParkingSession::STATUS_ACTIVE,
            'sync_status' => 'synced',
        ]);

        return response()->json(['session' => $session], 201);
    }

    public function exit(Request $request): JsonResponse
    {
        $request->validate([
            'plate' => ['required', 'string'],
            'local_uuid' => ['nullable', 'uuid'],
        ]);

        $user = $request->user();
        $normalized = $this->plateNormalizer->normalize($request->plate);

        $session = ParkingSession::where('parking_lot_id', $user->parking_lot_id)
            ->where('plate_normalized', $normalized)
            ->where('status', ParkingSession::STATUS_ACTIVE)
            ->first();

        if (! $session) {
            return response()->json(['message' => 'Veículo não encontrado no pátio'], 404);
        }

        $tariff = $user->company?->activeTariff();
        $exitAt = now();
        $amount = $tariff ? $this->tariffCalculator->calculate($tariff, $session->entry_at, $exitAt) : 0;

        DB::transaction(function () use ($session, $exitAt, $amount) {
            $session->update([
                'exit_at' => $exitAt,
                'amount' => $amount,
                'status' => ParkingSession::STATUS_COMPLETED,
                'sync_status' => 'synced',
            ]);

            Payment::create([
                'parking_session_id' => $session->id,
                'amount' => $amount,
                'method' => 'cash',
            ]);
        });

        return response()->json([
            'session' => $session->fresh()->load('payment'),
            'amount' => $amount,
        ]);
    }

    public function active(Request $request): JsonResponse
    {
        $sessions = ParkingSession::where('parking_lot_id', $request->user()->parking_lot_id)
            ->where('status', ParkingSession::STATUS_ACTIVE)
            ->orderByDesc('entry_at')
            ->get();

        return response()->json(['sessions' => $sessions]);
    }

    public function preview(Request $request): JsonResponse
    {
        $request->validate(['plate' => ['required', 'string']]);

        $user = $request->user();
        $normalized = $this->plateNormalizer->normalize($request->plate);

        $session = ParkingSession::where('parking_lot_id', $user->parking_lot_id)
            ->where('plate_normalized', $normalized)
            ->where('status', ParkingSession::STATUS_ACTIVE)
            ->first();

        if (! $session) {
            return response()->json(['message' => 'Veículo não encontrado'], 404);
        }

        $tariff = $user->company?->activeTariff();
        $amount = $tariff ? $this->tariffCalculator->calculate($tariff, $session->entry_at, now()) : 0;

        return response()->json([
            'session' => $session,
            'amount' => $amount,
            'duration_minutes' => $session->entry_at->diffInMinutes(now()),
        ]);
    }

    private function getOpenShift($user): Shift
    {
        $shift = Shift::where('user_id', $user->id)->whereNull('closed_at')->first();

        if ($shift) {
            return $shift;
        }

        return Shift::create([
            'local_uuid' => (string) Str::uuid(),
            'company_id' => $user->company_id,
            'parking_lot_id' => $user->parking_lot_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_balance' => 0,
            'sync_status' => 'synced',
        ]);
    }
}
