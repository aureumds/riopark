<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\ParkingSession;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BootstrapController extends Controller
{
    public function show(Request $request, LicenseService $licenses): JsonResponse
    {
        $user = $request->user()->load(['company', 'parkingLot']);
        $company = $user->company;

        if (! $company || ! $licenses->companyCanOperate($company)) {
            return response()->json([
                'message' => 'Mensalidade em atraso. Solicite a liberação no painel.',
            ], 403);
        }

        $tariff = $company->activeTariff();
        $deviceUid = $request->query('device_uid');
        $licensePayload = null;
        $devicePayload = null;

        if ($deviceUid) {
            $device = Device::where('device_uid', $deviceUid)
                ->where('company_id', $company->id)
                ->first();

            if ($device) {
                $device->update(['last_seen_at' => now()]);
                $current = $device->currentLicense();
                if ($current && $current->expires_at->gte(now())) {
                    $licensePayload = $licenses->toClientPayload($current);
                }
                $devicePayload = [
                    'id' => $device->id,
                    'device_uid' => $device->device_uid,
                    'label' => $device->label,
                ];
            }
        }

        return response()->json([
            'user' => $user,
            'company' => $company,
            'parking_lot' => $user->parkingLot,
            'tariff' => $tariff,
            'settings' => [
                'print_ticket_on_entry' => $company->print_ticket_on_entry ?? false,
                'print_ticket_on_exit' => $company->print_ticket_on_exit ?? false,
                'primary_color' => $company->primary_color,
                'accent_color' => $company->accent_color,
            ],
            'license' => $licensePayload,
            'device' => $devicePayload,
            'active_sessions_count' => ParkingSession::where('parking_lot_id', $user->parking_lot_id)
                ->where('status', ParkingSession::STATUS_ACTIVE)
                ->count(),
        ]);
    }
}
