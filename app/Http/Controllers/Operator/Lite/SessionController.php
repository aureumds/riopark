<?php

namespace App\Http\Controllers\Operator\Lite;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Operator\Lite\Concerns\ActivatesOperatorDevice;
use App\Models\ParkingSession;
use App\Models\Payment;
use App\Models\Shift;
use App\Services\PlateNormalizer;
use App\Services\TariffCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SessionController extends Controller
{
    use ActivatesOperatorDevice;

    public function __construct(
        private PlateNormalizer $plateNormalizer,
        private TariffCalculator $tariffCalculator,
    ) {}

    public function showEntry(Request $request): View
    {
        return view('operator-lite.entry', [
            'bootstrap' => $this->bootstrapPayload($request->user()),
        ]);
    }

    public function entry(Request $request): RedirectResponse
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
            return back()->withErrors(['plate' => 'Placa já está no pátio']);
        }

        $shift = $this->getOpenShift($user);
        $localUuid = $request->input('local_uuid', (string) Str::uuid());

        $session = ParkingSession::create([
            'local_uuid' => $localUuid,
            'company_id' => $user->company_id,
            'parking_lot_id' => $user->parking_lot_id,
            'shift_id' => $shift->id,
            'plate' => $this->plateNormalizer->format($request->plate),
            'plate_normalized' => $normalized,
            'entry_at' => now(),
            'status' => ParkingSession::STATUS_ACTIVE,
            'sync_status' => 'synced',
        ]);

        $company = $user->company;
        $printTicket = $company?->print_ticket_on_entry ?? false;

        return redirect()->route('operator-lite.entry')
            ->with('success', 'Entrada registrada: '.$session->plate)
            ->with('lite_event', [
                'type' => 'session_entry',
                'session' => $session->toArray(),
                'print_ticket' => $printTicket,
            ])
            ->with('lite_bootstrap', $this->bootstrapPayload($user));
    }

    public function showExit(Request $request): View
    {
        return view('operator-lite.exit', [
            'bootstrap' => $this->bootstrapPayload($request->user()),
        ]);
    }

    public function preview(Request $request)
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

    public function exit(Request $request): RedirectResponse
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
            return back()->withErrors(['plate' => 'Veículo não encontrado no pátio']);
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

        $session->refresh()->load('payment');
        $company = $user->company;
        $printTicket = $company?->print_ticket_on_exit ?? false;

        return redirect()->route('operator-lite.exit')
            ->with('success', 'Saída registrada — R$ '.number_format($amount, 2, ',', '.'))
            ->with('lite_event', [
                'type' => 'session_exit',
                'session' => $session->toArray(),
                'amount' => $amount,
                'print_ticket' => $printTicket,
            ])
            ->with('lite_bootstrap', $this->bootstrapPayload($user));
    }

    public function yard(Request $request): View
    {
        $user = $request->user();

        $sessions = ParkingSession::where('parking_lot_id', $user->parking_lot_id)
            ->where('status', ParkingSession::STATUS_ACTIVE)
            ->orderByDesc('entry_at')
            ->get();

        return view('operator-lite.yard', [
            'sessions' => $sessions,
            'bootstrap' => $this->bootstrapPayload($user),
        ]);
    }

    public function vehicleDetail(Request $request, string $plate): View|RedirectResponse
    {
        $user = $request->user();
        $normalized = $this->plateNormalizer->normalize($plate);

        $session = ParkingSession::where('parking_lot_id', $user->parking_lot_id)
            ->where('plate_normalized', $normalized)
            ->where('status', ParkingSession::STATUS_ACTIVE)
            ->first();

        if (! $session) {
            return redirect()->route('operator-lite.yard')
                ->withErrors(['plate' => 'Veículo não encontrado no pátio.']);
        }

        $tariff = $user->company?->activeTariff();
        $amount = $tariff
            ? $this->tariffCalculator->calculate($tariff, $session->entry_at, now())
            : 0;

        $durationMinutes = $session->entry_at->diffInMinutes(now());

        return view('operator-lite.yard-detail', [
            'session' => $session,
            'amount' => $amount,
            'durationMinutes' => $durationMinutes,
            'bootstrap' => $this->bootstrapPayload($user),
        ]);
    }

    public function closing(Request $request): View
    {
        return view('operator-lite.closing', [
            'bootstrap' => $this->bootstrapPayload($request->user()),
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
