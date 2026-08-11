<?php

namespace App\Http\Controllers;

use App\Models\DailyClosing;
use App\Models\ParkingSession;
use App\Models\Shift;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DailyClosingController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $date = today();

        $exists = DailyClosing::where('company_id', $user->company_id)
            ->where('date', $date)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Fechamento do dia já realizado.');
        }

        $openShifts = Shift::where('company_id', $user->company_id)->whereNull('closed_at')->count();

        if ($openShifts > 0) {
            return back()->with('error', 'Existem turnos abertos. Feche todos antes do fechamento diário.');
        }

        $stats = ParkingSession::where('company_id', $user->company_id)
            ->where('status', ParkingSession::STATUS_COMPLETED)
            ->whereDate('exit_at', $date)
            ->selectRaw('COUNT(*) as total_sessions, COALESCE(SUM(amount), 0) as total_amount')
            ->first();

        DailyClosing::create([
            'company_id' => $user->company_id,
            'date' => $date,
            'total_amount' => $stats->total_amount ?? 0,
            'total_sessions' => $stats->total_sessions ?? 0,
            'closed_at' => now(),
            'closed_by' => $user->id,
        ]);

        return back()->with('success', 'Fechamento diário realizado com sucesso.');
    }

    public function pdf(Request $request, string $date): Response
    {
        $user = $request->user();
        $closingDate = \Carbon\Carbon::parse($date);

        $closing = DailyClosing::where('company_id', $user->company_id)
            ->where('date', $closingDate)
            ->firstOrFail();

        $shifts = Shift::where('company_id', $user->company_id)
            ->whereDate('opened_at', $closingDate)
            ->with(['user', 'parkingLot'])
            ->withCount('parkingSessions')
            ->get();

        $sessions = ParkingSession::where('company_id', $user->company_id)
            ->where('status', ParkingSession::STATUS_COMPLETED)
            ->whereDate('exit_at', $closingDate)
            ->with('payment')
            ->orderBy('exit_at')
            ->get();

        $pdf = Pdf::loadView('pdf.daily-closing', [
            'closing' => $closing,
            'shifts' => $shifts,
            'sessions' => $sessions,
            'company' => $user->company,
        ]);

        return $pdf->download('fechamento-'.$closingDate->format('Y-m-d').'.pdf');
    }

    public function ticket(Request $request, int $sessionId): Response
    {
        $session = ParkingSession::with(['payment', 'parkingLot', 'company'])
            ->where('company_id', $request->user()->company_id)
            ->findOrFail($sessionId);

        $type = $request->query('type', 'exit');

        $pdf = Pdf::loadView('pdf.ticket', [
            'session' => $session,
            'company' => $session->company,
            'type' => $type,
        ])->setPaper([0, 0, 226.77, 600], 'portrait');

        return $pdf->stream('ticket-'.$session->plate.'.pdf');
    }
}
