<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BootstrapController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['company', 'parkingLot']);
        $company = $user->company;
        $tariff = $company?->activeTariff();

        return response()->json([
            'user' => $user,
            'company' => $company,
            'parking_lot' => $user->parkingLot,
            'tariff' => $tariff,
            'settings' => [
                'print_ticket_on_entry' => $company?->print_ticket_on_entry ?? false,
                'print_ticket_on_exit' => $company?->print_ticket_on_exit ?? false,
                'primary_color' => $company?->primary_color,
                'accent_color' => $company?->accent_color,
            ],
            'active_sessions_count' => ParkingSession::where('parking_lot_id', $user->parking_lot_id)
                ->where('status', ParkingSession::STATUS_ACTIVE)
                ->count(),
        ]);
    }
}
