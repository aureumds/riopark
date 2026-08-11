<?php

namespace App\Livewire\Admin;

use App\Models\Shift;
use Livewire\Component;

class Shifts extends Component
{
    public function render()
    {
        return view('livewire.admin.shifts', [
            'shifts' => Shift::with(['user', 'parkingLot'])
                ->withCount('parkingSessions')
                ->latest('opened_at')
                ->limit(50)
                ->get(),
        ])->layout('components.layouts.admin', ['title' => 'Turnos', 'subtitle' => 'Acompanhe os turnos dos operadores']);
    }
}
