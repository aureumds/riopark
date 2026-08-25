<?php

namespace App\Livewire\Admin;

use App\Models\Device;
use Livewire\Component;

class Licenses extends Component
{
    public function render()
    {
        $company = auth()->user()->company;
        $devices = Device::with(['parkingLot', 'licenses' => fn ($q) => $q->latest('issued_at')])
            ->orderBy('label')
            ->get();

        return view('livewire.admin.licenses', [
            'company' => $company,
            'devices' => $devices,
        ])->layout('components.layouts.admin', ['title' => 'Licença', 'subtitle' => 'Validade das máquinas']);
    }
}
