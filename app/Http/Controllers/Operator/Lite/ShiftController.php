<?php

namespace App\Http\Controllers\Operator\Lite;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Operator\Lite\Concerns\ActivatesOperatorDevice;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShiftController extends Controller
{
    use ActivatesOperatorDevice;

    public function show(Request $request): View
    {
        $user = $request->user();

        $shift = Shift::where('user_id', $user->id)
            ->whereNull('closed_at')
            ->withCount('parkingSessions')
            ->first();

        return view('operator-lite.shift', [
            'shift' => $shift,
            'bootstrap' => $this->bootstrapPayload($user),
        ]);
    }

    public function open(Request $request): RedirectResponse
    {
        $request->validate([
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'local_uuid' => ['nullable', 'uuid'],
        ]);

        $user = $request->user();

        $existing = Shift::where('user_id', $user->id)->whereNull('closed_at')->first();

        if ($existing) {
            return back()->with('warning', 'Turno já está aberto.');
        }

        $localUuid = $request->input('local_uuid', (string) Str::uuid());

        $shift = Shift::create([
            'local_uuid' => $localUuid,
            'company_id' => $user->company_id,
            'parking_lot_id' => $user->parking_lot_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_balance' => $request->input('opening_balance', 0),
            'sync_status' => 'synced',
        ]);

        return redirect()->route('operator-lite.shift')
            ->with('success', 'Turno aberto com sucesso.')
            ->with('lite_event', [
                'type' => 'shift_open',
                'shift' => $shift->toArray(),
            ])
            ->with('lite_bootstrap', $this->bootstrapPayload($user));
    }

    public function close(Request $request): RedirectResponse
    {
        $request->validate([
            'closing_balance' => ['nullable', 'numeric', 'min:0'],
            'local_uuid' => ['nullable', 'uuid'],
        ]);

        $user = $request->user();

        $shift = Shift::where('user_id', $user->id)->whereNull('closed_at')->first();

        if (! $shift) {
            return back()->withErrors(['shift' => 'Nenhum turno aberto.']);
        }

        $shift->update([
            'closed_at' => now(),
            'closing_balance' => $request->input('closing_balance', 0),
            'sync_status' => 'synced',
        ]);

        return redirect()->route('operator-lite.shift')
            ->with('success', 'Turno fechado com sucesso.')
            ->with('lite_event', [
                'type' => 'shift_close',
                'shift' => $shift->fresh()->toArray(),
            ])
            ->with('lite_bootstrap', $this->bootstrapPayload($user));
    }
}
