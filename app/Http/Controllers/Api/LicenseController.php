<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class LicenseController extends Controller
{
    public function __construct(private LicenseService $licenses) {}

    public function activate(Request $request): JsonResponse
    {
        return $this->issue($request, createDevice: true);
    }

    public function renew(Request $request): JsonResponse
    {
        return $this->issue($request, createDevice: false);
    }

    private function issue(Request $request, bool $createDevice): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_uid' => ['required', 'string', 'max:64'],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        if (! $user->active || ! $user->hasRole('operator')) {
            throw ValidationException::withMessages([
                'email' => ['Acesso permitido apenas para operadores ativos.'],
            ]);
        }

        if (! $user->parking_lot_id || ! $user->company_id) {
            throw ValidationException::withMessages([
                'email' => ['Operador sem estacionamento vinculado.'],
            ]);
        }

        $company = $user->company;

        if (! $this->licenses->companyCanIssue($company)) {
            throw ValidationException::withMessages([
                'email' => ['Mensalidade em atraso. Solicite a liberação no painel Rio Park.'],
            ]);
        }

        $device = Device::withoutCompanyScope()
            ->where('device_uid', $request->device_uid)
            ->first();

        if ($device) {
            if ($device->company_id !== $user->company_id) {
                throw ValidationException::withMessages([
                    'device_uid' => ['Esta máquina já está vinculada a outra empresa.'],
                ]);
            }

            if (! $device->active) {
                throw ValidationException::withMessages([
                    'device_uid' => ['Máquina desativada.'],
                ]);
            }
        } elseif (! $createDevice) {
            throw ValidationException::withMessages([
                'device_uid' => ['Máquina ainda não ativada. Use a ativação inicial.'],
            ]);
        } else {
            $device = Device::create([
                'company_id' => $user->company_id,
                'parking_lot_id' => $user->parking_lot_id,
                'device_uid' => $request->device_uid,
                'label' => $request->label ?: ($user->parkingLot?->name.' · POS'),
                'active' => true,
                'last_seen_at' => now(),
            ]);
        }

        $device->update([
            'parking_lot_id' => $user->parking_lot_id,
            'last_seen_at' => now(),
        ]);

        try {
            $license = $this->licenses->issueForDevice($device, $user);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'email' => [$e->getMessage()],
            ]);
        }

        $user->tokens()->delete();
        $apiToken = $user->createToken('operator-pos', ['operator'])->plainTextToken;

        return response()->json([
            'token' => $apiToken,
            'user' => $user->load(['company', 'parkingLot']),
            'license' => $this->licenses->toClientPayload($license),
            'device' => [
                'id' => $device->id,
                'device_uid' => $device->device_uid,
                'label' => $device->label,
            ],
        ]);
    }
}
