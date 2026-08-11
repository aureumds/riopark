<?php

namespace App\Livewire\Super;

use App\Models\Company;
use App\Models\ParkingSession;
use App\Models\Plan;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $companiesCount = Company::count();

        return view('livewire.super.dashboard', [
            'companiesCount' => $companiesCount,
            'activeCompanies' => Company::where('active', true)->count(),
            'usersCount' => User::count(),
            'sessionsToday' => ParkingSession::withoutGlobalScope(\App\Models\Scopes\CompanyScope::class)
                ->whereDate('entry_at', today())
                ->count(),
            'recentCompanies' => Company::latest()->take(5)->get(),
            'planStats' => Plan::withCount('companies')->get(),
        ])->layout('layouts.super', ['title' => 'Dashboard', 'subtitle' => 'Visão geral da plataforma']);
    }
}
