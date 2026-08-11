<?php

namespace App\Livewire\Admin;

use App\Models\DailyClosing;
use App\Models\ParkingSession;
use Livewire\Component;

class Closing extends Component
{
    public function render()
    {
        $companyId = auth()->user()->company_id;
        $todayClosed = DailyClosing::where('company_id', $companyId)
            ->where('date', today())
            ->exists();

        $todayStats = ParkingSession::where('status', ParkingSession::STATUS_COMPLETED)
            ->whereDate('exit_at', today())
            ->selectRaw('COUNT(*) as total_sessions, COALESCE(SUM(amount), 0) as total_amount')
            ->first();

        return view('livewire.admin.closing', [
            'closings' => DailyClosing::with('closedByUser')->latest('date')->limit(30)->get(),
            'todayClosed' => $todayClosed,
            'todayStats' => $todayStats,
        ])->layout('components.layouts.admin', ['title' => 'Fechamento', 'subtitle' => 'Histórico de fechamentos diários']);
    }
}
