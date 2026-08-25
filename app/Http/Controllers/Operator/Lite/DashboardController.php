<?php

namespace App\Http\Controllers\Operator\Lite;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Operator\Lite\Concerns\ActivatesOperatorDevice;
use App\Models\ParkingSession;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ActivatesOperatorDevice;

    public function index(Request $request): View
    {
        $user = $request->user();
        $user->load(['company', 'parkingLot']);

        $shift = Shift::where('user_id', $user->id)->whereNull('closed_at')->first();
        $activeCount = ParkingSession::where('parking_lot_id', $user->parking_lot_id)
            ->where('status', ParkingSession::STATUS_ACTIVE)
            ->count();

        return view('operator-lite.home', [
            'user' => $user,
            'shift' => $shift,
            'activeCount' => $activeCount,
            'bootstrap' => $this->bootstrapPayload($user),
        ]);
    }
}
