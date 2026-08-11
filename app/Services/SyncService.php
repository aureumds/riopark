<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ParkingLot;
use App\Models\ParkingSession;
use App\Models\Payment;
use App\Models\Shift;
use App\Models\TariffRule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncService
{
    public function __construct(
        private PlateNormalizer $plateNormalizer,
        private TariffCalculator $tariffCalculator,
    ) {}

    public function push(User $user, array $events): array
    {
        $results = [];

        foreach ($events as $event) {
            $type = $event['type'] ?? null;
            $localUuid = $event['local_uuid'] ?? null;

            if (! $type || ! $localUuid) {
                $results[] = ['local_uuid' => $localUuid, 'status' => 'error', 'message' => 'Invalid event'];

                continue;
            }

            try {
                $result = match ($type) {
                    'shift_open' => $this->syncShiftOpen($user, $event),
                    'shift_close' => $this->syncShiftClose($user, $event),
                    'session_entry' => $this->syncSessionEntry($user, $event),
                    'session_exit' => $this->syncSessionExit($user, $event),
                    default => ['local_uuid' => $localUuid, 'status' => 'error', 'message' => 'Unknown type'],
                };

                $results[] = $result;
            } catch (\Throwable $e) {
                $results[] = [
                    'local_uuid' => $localUuid,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public function pull(User $user, ?string $since): array
    {
        $sinceDate = $since ? Carbon::parse($since) : Carbon::now()->subDay();

        $parkingLotId = $user->parking_lot_id;

        $sessions = ParkingSession::query()
            ->where('parking_lot_id', $parkingLotId)
            ->where('updated_at', '>=', $sinceDate)
            ->with('payment')
            ->get();

        $shifts = Shift::query()
            ->where('parking_lot_id', $parkingLotId)
            ->where('updated_at', '>=', $sinceDate)
            ->get();

        return [
            'sessions' => $sessions,
            'shifts' => $shifts,
            'synced_at' => now()->toIso8601String(),
        ];
    }

    private function syncShiftOpen(User $user, array $event): array
    {
        $localUuid = $event['local_uuid'];

        $existing = Shift::withoutGlobalScope(\App\Models\Scopes\CompanyScope::class)
            ->where('local_uuid', $localUuid)
            ->first();

        if ($existing) {
            return ['local_uuid' => $localUuid, 'status' => 'synced', 'id' => $existing->id];
        }

        $shift = Shift::create([
            'local_uuid' => $localUuid,
            'company_id' => $user->company_id,
            'parking_lot_id' => $event['parking_lot_id'] ?? $user->parking_lot_id,
            'user_id' => $user->id,
            'opened_at' => Carbon::parse($event['opened_at'] ?? now()),
            'opening_balance' => $event['opening_balance'] ?? 0,
            'sync_status' => 'synced',
        ]);

        return ['local_uuid' => $localUuid, 'status' => 'synced', 'id' => $shift->id];
    }

    private function syncShiftClose(User $user, array $event): array
    {
        $localUuid = $event['local_uuid'];

        $shift = Shift::withoutGlobalScope(\App\Models\Scopes\CompanyScope::class)
            ->where('local_uuid', $localUuid)
            ->first();

        if (! $shift) {
            return ['local_uuid' => $localUuid, 'status' => 'error', 'message' => 'Shift not found'];
        }

        $shift->update([
            'closed_at' => Carbon::parse($event['closed_at'] ?? now()),
            'closing_balance' => $event['closing_balance'] ?? 0,
            'sync_status' => 'synced',
        ]);

        return ['local_uuid' => $localUuid, 'status' => 'synced', 'id' => $shift->id];
    }

    private function syncSessionEntry(User $user, array $event): array
    {
        $localUuid = $event['local_uuid'];

        $existing = ParkingSession::withoutGlobalScope(\App\Models\Scopes\CompanyScope::class)
            ->where('local_uuid', $localUuid)
            ->first();

        if ($existing) {
            return ['local_uuid' => $localUuid, 'status' => 'synced', 'id' => $existing->id];
        }

        $plate = $event['plate'] ?? '';
        $normalized = $this->plateNormalizer->normalize($plate);

        $activeExists = ParkingSession::withoutGlobalScope(\App\Models\Scopes\CompanyScope::class)
            ->where('parking_lot_id', $event['parking_lot_id'] ?? $user->parking_lot_id)
            ->where('plate_normalized', $normalized)
            ->where('status', ParkingSession::STATUS_ACTIVE)
            ->exists();

        if ($activeExists) {
            return ['local_uuid' => $localUuid, 'status' => 'error', 'message' => 'Placa já está no pátio'];
        }

        $session = ParkingSession::create([
            'local_uuid' => $localUuid,
            'company_id' => $user->company_id,
            'parking_lot_id' => $event['parking_lot_id'] ?? $user->parking_lot_id,
            'shift_id' => $this->resolveShiftId($event, $user),
            'plate' => $this->plateNormalizer->format($plate),
            'plate_normalized' => $normalized,
            'entry_at' => Carbon::parse($event['entry_at'] ?? now()),
            'status' => ParkingSession::STATUS_ACTIVE,
            'sync_status' => 'synced',
        ]);

        return ['local_uuid' => $localUuid, 'status' => 'synced', 'id' => $session->id];
    }

    private function syncSessionExit(User $user, array $event): array
    {
        $localUuid = $event['local_uuid'];

        $session = ParkingSession::withoutGlobalScope(\App\Models\Scopes\CompanyScope::class)
            ->where('local_uuid', $localUuid)
            ->first();

        if (! $session) {
            return ['local_uuid' => $localUuid, 'status' => 'error', 'message' => 'Session not found'];
        }

        if ($session->status === ParkingSession::STATUS_COMPLETED) {
            return ['local_uuid' => $localUuid, 'status' => 'synced', 'id' => $session->id];
        }

        $exitAt = Carbon::parse($event['exit_at'] ?? now());
        $company = Company::find($user->company_id);
        $tariff = $company?->activeTariff();

        $amount = $event['amount'] ?? ($tariff ? $this->tariffCalculator->calculate($tariff, $session->entry_at, $exitAt) : 0);

        DB::transaction(function () use ($session, $exitAt, $amount, $event) {
            $session->update([
                'exit_at' => $exitAt,
                'amount' => $amount,
                'status' => ParkingSession::STATUS_COMPLETED,
                'sync_status' => 'synced',
            ]);

            if (! $session->payment) {
                Payment::create([
                    'parking_session_id' => $session->id,
                    'amount' => $amount,
                    'method' => $event['payment_method'] ?? 'cash',
                ]);
            }
        });

        return ['local_uuid' => $localUuid, 'status' => 'synced', 'id' => $session->id, 'amount' => $amount];
    }

    private function resolveShiftId(array $event, User $user): int
    {
        if (isset($event['shift_id']) && $event['shift_id']) {
            return (int) $event['shift_id'];
        }

        if (isset($event['shift_local_uuid'])) {
            $shift = Shift::withoutGlobalScope(\App\Models\Scopes\CompanyScope::class)
                ->where('local_uuid', $event['shift_local_uuid'])
                ->first();

            if ($shift) {
                return $shift->id;
            }
        }

        $openShift = Shift::withoutGlobalScope(\App\Models\Scopes\CompanyScope::class)
            ->where('user_id', $user->id)
            ->whereNull('closed_at')
            ->latest()
            ->first();

        if ($openShift) {
            return $openShift->id;
        }

        $shift = Shift::create([
            'local_uuid' => (string) Str::uuid(),
            'company_id' => $user->company_id,
            'parking_lot_id' => $event['parking_lot_id'] ?? $user->parking_lot_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_balance' => 0,
            'sync_status' => 'synced',
        ]);

        return $shift->id;
    }
}
