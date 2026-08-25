<?php

namespace App\Http\Controllers\Operator\Lite\Concerns;

use App\Models\Device;
use App\Models\License;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

trait ActivatesOperatorDevice
{
    protected function activateDevice(User $user, string $deviceUid, ?string $label, bool $createDevice, LicenseService $licenses): array
    {
        if (! $user->parking_lot_id || ! $user->company_id) {
            throw ValidationException::withMessages([
                'email' => ['Operador sem estacionamento vinculado.'],
            ]);
        }

        $company = $user->company;

        if (! $licenses->companyCanIssue($company)) {
            throw ValidationException::withMessages([
                'email' => ['Mensalidade em atraso. Solicite a liberação no painel Rio Park.'],
            ]);
        }

        $device = Device::withoutCompanyScope()
            ->where('device_uid', $deviceUid)
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
                'device_uid' => ['Máquina ainda não ativada. Faça login novamente.'],
            ]);
        } else {
            $device = Device::create([
                'company_id' => $user->company_id,
                'parking_lot_id' => $user->parking_lot_id,
                'device_uid' => $deviceUid,
                'label' => $label ?: ($user->parkingLot?->name.' · POS'),
                'active' => true,
                'last_seen_at' => now(),
            ]);
        }

        $device->update([
            'parking_lot_id' => $user->parking_lot_id,
            'last_seen_at' => now(),
        ]);

        try {
            $license = $licenses->issueForDevice($device, $user);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'email' => [$e->getMessage()],
            ]);
        }

        return ['device' => $device, 'license' => $license];
    }

    protected function storeOperatorSession(User $user, License $license, LicenseService $licenses): void
    {
        $user->load(['company', 'parkingLot']);
        $company = $user->company;
        $tariff = $company?->activeTariff();

        session([
            'operator_license_token' => $license->token,
            'operator_license_expires_at' => $license->expires_at->toIso8601String(),
            'operator_license_jti' => $license->jti,
            'operator_device_uid' => $license->device->device_uid,
            'operator_tariff' => $tariff?->toArray(),
            'operator_company' => $company ? [
                'id' => $company->id,
                'name' => $company->name,
                'print_ticket_on_entry' => $company->print_ticket_on_entry,
                'print_ticket_on_exit' => $company->print_ticket_on_exit,
                'primary_color' => $company->primary_color,
                'accent_color' => $company->accent_color,
            ] : null,
            'operator_parking_lot' => $user->parkingLot ? [
                'id' => $user->parkingLot->id,
                'name' => $user->parkingLot->name,
            ] : null,
        ]);
    }

    protected function bootstrapPayload(User $user): array
    {
        $user->load(['company', 'parkingLot']);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'company' => session('operator_company'),
            'parking_lot' => session('operator_parking_lot'),
            'tariff' => session('operator_tariff'),
            'license' => [
                'token' => session('operator_license_token'),
                'expires_at' => session('operator_license_expires_at'),
                'jti' => session('operator_license_jti'),
                'device_uid' => session('operator_device_uid'),
                'grace_days' => (int) config('license.grace_days', 3),
            ],
            'device_uid' => session('operator_device_uid'),
        ];
    }
}
