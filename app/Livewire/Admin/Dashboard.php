<?php

namespace App\Livewire\Admin;

use App\Models\DailyClosing;
use App\Models\ParkingSession;
use App\Models\Shift;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $companyId = auth()->user()->company_id;

        return view('livewire.admin.dashboard', [
            'activeInLot' => ParkingSession::where('status', ParkingSession::STATUS_ACTIVE)->count(),
            'revenueToday' => ParkingSession::where('status', ParkingSession::STATUS_COMPLETED)
                ->whereDate('exit_at', today())
                ->sum('amount'),
            'openShifts' => Shift::whereNull('closed_at')->count(),
            'lastClosing' => DailyClosing::latest('date')->first(),
            'activeSessions' => ParkingSession::where('status', ParkingSession::STATUS_ACTIVE)
                ->latest()
                ->take(5)
                ->get(),
            'activeShifts' => Shift::with('user')
                ->whereNull('closed_at')
                ->latest()
                ->get(),
        ])->layout('components.layouts.admin', ['title' => 'Dashboard', 'subtitle' => 'Visão geral do estacionamento']);
    }
}
